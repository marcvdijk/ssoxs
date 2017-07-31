# SSOXS
Single Sign On for eXternal Services (SSOXS), a Drupal module

<img style="float: right;" src="ssoxs.png">

**Drupal version: 7.x**

This project was developed as part of the WeNMR project (European FP7 e-Infrastructure grant,
contract no. 261572, www.wenmr.eu) and first deposited on the Drupal git repository.

Links:

* [WeNMR project][1]
* [Marc van Dijk on Drupal][2]
* [SSOXS Drupal project page][3]
* [SSOXS Drupal git repository][4]

Managing authentication and authorization for virtual communities with a distributed service infrastructure.

“Unite and conquer”, professionals are organizing themselves more often in virtual communities. 
These communities link likeminded individuals together enabling a common knowledgebase, professional interaction
and exposure to services valuable to the community. These services are often web-based tools organized through a
common gateway or offered by the community’s users on their own infrastructure using their own implementation.
The “Single Sign On for eXternal Services” module, or SSOXS for short, extends Drupal to serve as a single-sign-on 
authentication, central user management and accounting portal for external web servers or services. 
It provides a complete solutions for all SSO and accounting needs for these communities.

The module offers a “My Services” page on the Drupal users profile page allowing them to easily ‘subscribe’ to 
external services.
The services offered are registered with the module by administrators of the service though the modules 
administration interface. A rich feature set allows the service administrator to fine-tune who may subscribe to
the service, if there are additional subscription requirements to fulfill, the use of Token based access and more.
Registered services are allowed access to the modules API using the flexible XML-RPC communication standard. 
The extendable API enables secure communication between the external service and the SSOXS module enabling not 
only user authentication and authorization but also user management and accounting.
The service administration pages allow the service administrators to manage subscribed users, query the accounting
information provided by the service through the API and get an overview of service usage and performance by 
beautiful reports with interactive graphs.

Finally, the module is designed to be a good “Drupal citizen” extending the core functionality with new features 
like the many other excellent open source modules do. It can therefore directly benefit from the many modules that 
extend Drupal with additional functionality such as authentication and authorization mechanisms like Social media 
credentials, Shibboleth, OAuth and OpenID.
The module even hosts it’s own federate login functionality via the versatile simpleSAMLphp library allowing 
Drupal users to login using federate identity out of the box.

It is the combination between the online SSOXS administration interface and the flexible API that provides a 
closed infrastructure for everything related to authentication, authorization and accounting for a service. 
There is no longer a need to install independent modules or access different online services and “glue” them 
together to achieve the same functionality. The combination of these features makes this module unique. 
The service administrator is in control!

Documentation for the module is included in the modules README.txt file
This module was developed as part of the WeNMR project, and academic e-Infrastructure project funded by the 
European FP7 grant, contract no. 261572, (www.wenmr.eu)


##SSOXS features, a detailed view:
###One time authentication
Allow users to authorize themselves to the external services using their Drupal account credentials. 
The SSOXS module integrates with Drupal’s core user management system and directly benefits from 
the many excellent modules that extend Drupal with additional authentication and authorization 
mechanisms like Social media credentials, SAML, Shibboleth, OAuth and OpenID.

###Federate login enabled
SSOXS offers it’s own federated login facility using the powerful simpleSAMLphp library. 
Identity provides configured in simpleSAMLphp are directly available in the module and can be offered 
on Drupal’s login page. Federated login account management is fully configurable with support for 
account persistence, privacy settings and mapping of attributes provided by the identity providers 
with support for the Profile module.

###Subscription based access
Access to external services is managed using a subscription mechanism. Drupal users can subscribe 
to services through a “My Services” page on their account profile.

###Tailored subscription procedures
No single service is the same. The ssoxs module respects this, offering service administrators with
a rich toolset to fine-tune the subscription procedure to their service. Access to a service can be 
restricted by Drupal role and the subscription procedure supports requirements such as; 
a valid certificate, agreement to license terms, manual subscription approval and the availability of
user profile information.

###Token-based access
The SSOXS module offers token-based access to control the frequency of service use by a subscriber. 
Tokens enable a user to first try a service or allows for implementation of a payment model among others.

###Up on security
The SSOXS module handles potentially sensitive data and does so with care by: offering detailed 
permission settings, support for external SQL databases and BlowFish encrypted API communications. 
The module even comes with a build in database backup functionality.

###Easy service management
Enabling single sign-on for an external service is as easy as filling out one form by an authorized 
Drupal user. The form defines the basic service description, the Drupal users that serve as administrators,
the roles that can subscribe to the service, any signup requirements and the API key to enable secure 
XML-RPC communication between the service and the Drupal site.
After service registration the service administration interfaces created by the ssoxs module allows 
administrators to easily administrate subscribed users, access accounting logs and view service statistics 
as beautiful formatted reports with interactive graphs.

###Easy user management
The service administration pages offer a convenient interface for user management. One interface to edit 
a users subscription, subscribe new users, query the user base with powerful filter options and contact 
your users through an email messaging interface.

###XML-RPC API
Services communicate with the SSOXS module for authentication, authorization and accounting using a 
dedicated API using the XML-RPC protocol. XML-RPC implementations are available for every major programming
language and the module offers ready made program classes in Python and PHP.

###Track your service usage
The XML-RPC API offers functions for service accounting. The accounting information is stored in the 
SSOXS database and made available through the accounting and statistics interfaces. Track access to your
service by user, job status or start/finish time. The accounting interface offers a rich logging facility 
for live logs.
Overall statistics for a service for any give time period are available on the ‘Service statistics’ interface.
Service access statistics including access count, user access and user demographics are displayed as 
interactive graphs.

###Service specific attributes
Services often define specific attributes for a user such as a result URL or an internal id. 
The ssoxs module allows administrators to define variable data fields of the type ‘integer’, ‘selections’ 
or ‘text’ that are stored as part of the user subscription. These attributes can be managed via the user 
management interface and are available via the XML-RPC API.

[1]: http://www.wenmr.eu
[2]: https://www.drupal.org/u/mvdijk
[3]: https://drupal.org/sandbox/mvdijk/2123977
[4]: http://git.drupal.org/sandbox/mvdijk/2123977.git