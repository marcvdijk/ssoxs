#!/usr/bin/env python
# encoding: utf-8

"""
ssoxs.py

Created by Marc van Dijk on 2014-03-01.
Copyright (c) 2013 StickyBits. All rights reserved (www.stickybits.nl).
"""

import sys, os, hashlib, xmlrpclib, random, string
from Crypto.Cipher import Blowfish
from base64 import urlsafe_b64decode, urlsafe_b64encode
from optparse import *

#SERVICE SPECIFIC VARIABLES
USAGE = ""
CRYPT_KEY = '< your secret key >'
SERVER_URL = '< server url >'

class Blowfish_crypt_decrypt(object):

  # Blowfish encryption decryption class
  # - Data being encrypted should have length divisible by 16, pad by spaces if needed.
  # - Encrypt with mode CBC with random IV of 8 bits
  # - Add 8 characters at the beginning of the string because these 8 are not properly encoded
  #   decoded by Blowfish in mode CBC. As it are always the first 8 bits we can remove them when decoding
  # - Use 56 bits secret key

  def __init__(self, encrypt_method=Blowfish.MODE_CBC):

    self.iv = ''.join(random.choice(string.ascii_lowercase + string.digits) for x in range(8))
    self.cipher = Blowfish.new(CRYPT_KEY, encrypt_method, self.iv)

  def encrypt(self, data):

    data = '########' + data
    while (len(data) % 16 != 0):
      data = data + " "

    return urlsafe_b64encode(self.cipher.encrypt(data))

  def decrypt(self, data):

    decipher = self.cipher.decrypt(urlsafe_b64decode(data))
    decipher = (decipher.strip())[8:]
    return decipher

class ssoxs_connect(object):

	# Class for managing user authentication, automatic login and accounting for managed services
	# Successful authentication will populate the user dictionary with user specific info:
	# - dn: users certificate dn
	# - expd: experation date of the certificate
	# - name: user name
	# - mail: user email
	# - subscription: service subscription status as 'inactive', 'pending', 'active'
	# - ip: if the user is logged into the Drupal website this field will list the users IP
	# - uid: users unique uid as used in de Drupal site
	# - rdate: UNIX time stamp of the subscription start date
	# - any service specific information can be added to the dictionary.

  def __init__(self, service):

    self.server = xmlrpclib.Server(SERVER_URL, allow_none=True)
    self.user = {}
    self.service = service

  def autologin(self, dn, encrypt=False):

    # Authentication by public key of the GRID certificate (DN)
    # - DN is send without encryption by default, encrypt=False.
    # - Returned user object or emtpy array decrypted.
    # - Return True or False for aythentication, register user object in the class 'user' attribute

    if encrypt:
      crypt = Blowfish_crypt_decrypt()
      dn = crypt.encrypt(dn)

    try:
      self.user = self.server.ssoxs.autologin(self.service, dn)
    except xmlrpclib.Error, v:
      sys.stderr.write("ERROR: %s" % v)

    if encrypt:
      for data in self.user:
        self.user[data] = crypt.decrypt(self.user[data]);

    if not len(self.user) or not self.user.has_key('name'):
      return False

    return True

  def authenticate(self, username, password, encrypt=False):

    # Authenticate the user by name and password.
		# - Password needs to be plain text but all info is encrypted using Blowfish. Plain text
    #   password hashed by Drupal on the server side and compaired with stored user password has.
		# - Username and password converted to ',' separated string and encrypted using Blowfish
		# - Encrypted string send to server using XML-RPC authenticate method
		# - Returned user object or emtpy array decrypted.
		# - Return TRUE or FALSE for aythentication, register user object in the class 'user' attribute

    userstring = "%s,%s" % (username, password)
    crypt = Blowfish_crypt_decrypt()
    encrypt_string = crypt.encrypt(userstring)

    try:
      self.user = self.server.ssoxs.authenticate(self.service, encrypt_string)
    except xmlrpclib.Error, v:
      sys.stderr.write("ERROR: %s" % v)

    if encrypt:
      for data in self.user:
        self.user[data] = crypt.decrypt(self.user[data]);

    if not len(self.user) or not self.user.has_key('name'):
      return False

    return True

  def accounting(self, uid=0, ip=None, jid=None, status=0, message='', url=None):

		# Communicate job accounting with the server. Possible attributes:
		# - uid: The users uid as registered in the users table of the website the SSOXS module is installed
		#        The default uid of 0 equals a anonymous user.
		# - ip: The IP address the users request came from.
		# - jid: A unique numerical job identifier. If none supplied the SSOXS module will create one for
		#        internal bookkeeping and return it.
		# - status: The status of the job as number; 0 = submitted, 1 = waiting, 2 = ready, 3 = scheduled,
		#            4 = running, 5 = done, 6 = cleared, 7 = failed. Default status set to 0.
		# - message: A log message for the job. The SSOXS module will handle formatting the message and
		#						 appending to the log.
		# - Return the statistics object.

    try:
      stats = self.server.ssoxs.accounting({'machine_name': self.service,'uid': uid,'ip': ip,'jid': jid,'status': status,'message': message,'url': url})
    except xmlrpclib.Error, v:
      sys.stderr.write("ERROR: %s" % v)

    return stats

  def query(self, key, value=None, encrypt=False):

    # Query the user table of the service for specific information specified by a key/value pair
    # (table column/row). If the data is found, the server will try to return the associated user object.
    # Note: if multiple users are found, self.user will turn into a list
    # Function return True if found False if not.

    found = False
    try:
      if not value:
        self.user = self.server.ssoxs.query({'machine_name': self.service, 'key': "%s" % key})
      else:
        self.user = self.server.ssoxs.query({'machine_name': self.service, 'key': "%s" % key, 'value': "%s" % value})
      if self.user: found = True
    except xmlrpclib.Error, v:
      sys.stderr.write("ERROR: %s" % v)

    if encrypt:
      for data in self.user:
        self.user[data] = crypt.decrypt(self.user[data]);

    if len(self.user) == 1:
      self.user = self.user[0]

    return found

  def update(self, encrypt=False):

    # Update user information. Edit information in the self.user dictionary, the run 'update' function.
    # Note, if user info derived from query run that yielded more than 1 user, only first user is used
    # in update function.

    updated_user = None
    try:
      if type(self.user) == type([]):
        updated_user = self.server.ssoxs.update({'machine_name': self.service, 'user': self.user[0]})
      else:
        updated_user = self.server.ssoxs.update({'machine_name': self.service, 'user': self.user})
    except xmlrpclib.Error, v:
      sys.stderr.write("ERROR: %s" % v)

    if updated_user:
      self.user = updated_user

    return updated_user

class CommandlineOptionParser:

	"""Parses command line arguments using optparse"""

	def __init__(self):

		self.option_dict = {}
		self.option_dict = self.CommandlineOptionParser()

	def CommandlineOptionParser(self):

		"""Parsing command line arguments"""

		usage = "usage: %prog" + USAGE
		parser = OptionParser(usage)

		parser.add_option( "-u", "--uid", action="store", dest="uid", type="int", default=0, help="Unique user ID")
		parser.add_option( "-i", "--ip", action="store", dest="ip", type="string", help="IP where the request originated from")
		parser.add_option( "-j", "--jid", action="store", dest="jid", type="int", help="Unique job identifier")
		parser.add_option( "-s", "--status", action="store", dest="status", type="int", default=0, help="Status of the job")
		parser.add_option( "-m", "--message", action="store", dest="message", default=False, help="Optional, messages for the user")
		parser.add_option( "-r", "--url", action="store", dest="url", help="Optional, define URL to a results or project page")

		(options, args) = parser.parse_args()

		self.option_dict['uid'] = options.uid
		self.option_dict['ip'] = options.ip
		self.option_dict['jid'] = options.jid
		self.option_dict['status'] = options.status
		self.option_dict['message'] = options.message
		self.option_dict['url'] = options.url

		return self.option_dict

if __name__ == '__main__':

  commands = CommandlineOptionParser().option_dict