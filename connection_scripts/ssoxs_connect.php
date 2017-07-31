<?php

/**
* @file
* ssoxs.php
*
* Created by Marc van Dijk on 2014-03-01.
* Copyright (c) 2013 StickyBits. All rights reserved (www.stickybits.nl).
*/

class Blowfish_crypt_decrypt {

  /**
   * Blowfish encryption decryption class.
   *
   * - Supports php arrays by serializing/unserializing.
   * - Data being encrypted should have length divisible by 16,
   *   pad by spaces if needed.
   * - Encrypt with mode ECB.
   * - Use 56 bits secret key.
   */

  const CYPHER = MCRYPT_BLOWFISH;
  const MODE   = MCRYPT_MODE_CBC;
  const KEY    = '< your secret key >';

  public function __construct() {

    // Test if xmlrpc extension is loaded, exit with error if not.
    extension_loaded('mcrypt') or exit("PHP mcrypt extension not loaded");
  }

  protected function urlsafeB64encode($string) {

    return strtr(base64_encode($string), '+/=', '-_~');
  }

  protected function urlsafeB64decode($string) {

    return base64_decode(strtr($string, '-_~', '+/='));
  }

  public function encrypt($data) {

    $data = '########' . $data;
    $data = str_pad($data, ceil(strlen($data) / 16 * 16));

    $cypher = mcrypt_module_open(self::CYPHER, '', self::MODE, '');
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($cypher), MCRYPT_RAND);
    mcrypt_generic_init($cypher, self::KEY, $iv);
    $encrypted_data = mcrypt_generic($cypher, $data);
    mcrypt_generic_deinit($cypher);
    mcrypt_module_close($cypher);

    return self::urlsafeB64encode($encrypted_data);
  }

  public function decrypt($crypttext) {

    $crypttext = self::urlsafeB64decode($crypttext);

    $cypher = mcrypt_module_open(self::CYPHER, '', self::MODE, '');
    $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($cypher), MCRYPT_RAND);
    mcrypt_generic_init($cypher, self::KEY, $iv);
    $data = mdecrypt_generic($cypher, $crypttext);
    mcrypt_generic_deinit($cypher);
    mcrypt_module_close($cypher);

    $data = substr($data, 8);
    return trim($data);
  }
}

class ssoxs_connect
{

  // Single Sign-On for eXternal Services XML-RPC communication class.
  protected $server = "< server url >";
  public $service = NULL;
  public $user = array();

  public function __construct($service) {

    // Dedicated class constructor. Requires service machine name as argument.
    // Test if xmlrpc extension is loaded, exit with error if not.
    extension_loaded('xmlrpc') or exit("PHP xmlrpc extension not loaded");
    $this->service = $service;
  }

  protected function create_xmlrpc_context($request) {

    // Returns the header for the XMLRPC request message.
    $context = stream_context_create(array(
      'http' => array(
      'method' => "POST",
      'header' => "Content-Type: text/xml\r\nUser-Agent: PHPRPC/1.0\r\n",
      'content' => $request,
    )));

    return $context;
  }

  /**
   * Authentication by public key of the GRID certificate (DN).
   *
   * - DN is send without encryption by default, $encrypt=FALSE.
   * - Returned user object or emtpy array decrypted.
   * - Return TRUE or FALSE for authentication, register user object in the
   *   class 'user' attribute.
   */
  public function autologin($dn, $encrypt=FALSE) {

    if ($encrypt) {
      $crypt = new Blowfish_crypt_decrypt;
      $dn = $crypt->encrypt($dn);
    }

    $request = xmlrpc_encode_request("ssoxs.autologin", array($this->service, $dn));
    $file = file_get_contents($this->server, FALSE, $this->create_xmlrpc_context($request));
    $response = xmlrpc_decode($file);

    if ($encrypt) {
      foreach ($response as $key => $data) {
        $response[$key] = $crypt->decrypt($data);
      }
    }

    $this->user = $response;

    if (empty($response) or !(array_key_exists('name', $response))) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Authenticate the user by name and password.
   *
   * - Password always communicated as MD5 hash
   * - Username and password converted to ',' separated string and encrypted
   *   using Blowfish.
   * - Encrypted string send to server using XML-RPC authenticate method
   * - Returned user object or emtpy array decrypted.
   * - Return TRUE or FALSE for aythentication, register user object in the
   *   class 'user' attribute.
   */
  public function authenticate($username, $password, $encrypt=FALSE) {

    $userstring = sprintf("%s,%s", $username, $password);

    $crypt = new Blowfish_crypt_decrypt;
    $encrypt_string = $crypt->encrypt($userstring);

    $request = xmlrpc_encode_request("ssoxs.authenticate", array($this->service, $encrypt_string));
    $file = file_get_contents($this->server, FALSE, $this->create_xmlrpc_context($request));
    $response = xmlrpc_decode($file);

    if ($encrypt) {
      foreach ($response as $key => $data) {
        $response[$key] = $crypt->decrypt($data);
      }
    }

    $this->user = $response;

    if (empty($response) or !(array_key_exists('name', $response))) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Communicate job accounting with the server. Possible attributes:
   *
   * - uid: The users uid as registered in the users table of the website the
   *   SSOXS module is installed. The default uid of 0 equals a anonymous user.
   * - ip: The IP address the users request came from.
   * - jid: A unique numerical job identifier. If none supplied the SSOXS
   *   module will create one for internal bookkeeping and return it.
   * - status: The status of the job as number; 0 = submitted, 1 = waiting,
   *   2 = ready, 3 = scheduled, 4 = running, 5 = done, 6 = cleared, 7 = failed.
   *   Default status set to 0.
   * - message: A log message for the job. The SSOXS module will handle
   *   formatting the message and appending to the log.
   * - url: Optional URL of the job results page.
   */
  public function accounting($uid=0, $ip=NULL, $jid=NULL, $status=0, $message='', $url=NULL) {

    $response = array();
    $request = xmlrpc_encode_request("ssoxs.accounting", array('machine_name' => $this->service,
                                                               'uid' => $uid,
                                                               'ip' => $ip,
                                                               'jid' => $jid,
                                                               'status' => $status,
                                                               'message' => $message,
                                                               'url' => $url));
    $file = file_get_contents($this->server, false, $this->create_xmlrpc_context($request));
    $response = xmlrpc_decode($file);

    return $response;
  }

  /**
   * Query the service user table for information specified by a key/value pair.
   *
   * - (table column/row). If the data is found, the server will try to return
   *   the associated user object.
   * - Note: if multiple users are found, self.user will turn into a list.
   * - Function return True if found False if not.
   */
  public function query($key, $value=NULL, $encrypt=FALSE) {

    if (!$value) {
      $request = xmlrpc_encode_request("ssoxs.query", array('machine_name' => $this->service, 'key' => $key));
    }
    else {
      $request = xmlrpc_encode_request("ssoxs.query", array('machine_name' => $this->service, 'key' => $key, 'value' => $value));
    }
    $file = file_get_contents($this->server, FALSE, $this->create_xmlrpc_context($request));
    $response = xmlrpc_decode($file);

    if ($encrypt) {
      $crypt = new Blowfish_crypt_decrypt;
      foreach ($response as $key => $data) {
        $response[$key] = $crypt->decrypt($data);
      }
    }

    $this->user = $response;

    if (empty($response) or !(array_key_exists('name', $response))) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Update user information.
   *
   * - Edit information in the self.user dictionary, the run 'update' function.
   * - Note, if user info derived from query run that yielded more than 1 user,
   *   only first user is used in update function.
   */
  public function update($encrypt=FALSE) {

    if ($this->user) {
      $request = xmlrpc_encode_request("ssoxs.update", array('machine_name' => $this->service, 'user' => $this->user));
      $file = file_get_contents($this->server, FALSE, $this->create_xmlrpc_context($request));
      $response = xmlrpc_decode($file);
    }

    if ($encrypt) {
      $crypt = new Blowfish_crypt_decrypt;
      foreach ($response as $key => $data) {
        $response[$key] = $crypt->decrypt($data);
      }
    }

    if ($response) {
      $this->user = $response;
    }

    return $response;
  }
}
