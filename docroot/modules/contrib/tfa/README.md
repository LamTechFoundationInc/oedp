![travis](https://travis-ci.org/d8-contrib-modules/tfa.svg?branch=master "Travis CI")
## Two-factor Authentication (TFA) module for Drupal

TFA is a base module for providing two-factor authentication for your Drupal
site. As a base module, TFA handles all of the Drupal integration work,
providing flexible and well tested interfaces to enable seamless, and
configurable, choice of various two-factor authentication solutions like
Time-based One Time Passwords, SMS-delivered codes, fallback codes, or
integrations with third-party suppliers like Authy, Duo and others.

Read more about the features and use of TFA at its Drupal.org project page at
https://drupal.org/project/tfa

### Installation and use

TFA module can be installed like other Drupal modules by placing this directory
in the Drupal file system (for example, under modules/) and enabling on
the Drupal modules page.

TFA module does not come with any plugins of its own so refer to the project
page for contributed plugins or read the section on Plugin development.

### Configuration

TFA can be configured on your Drupal site at Administration - Configuration -
People - Two-factor Authentication. Available plugins will be listed along with
their type and configured use, if set.

Additionally, a permission is exposed to Drupal roles allowing them to skip the
TFA process -- regardless of plugins and the "require TFA" setting.

#### Default validation plugin

The plugin that will be used by default during user authentication. The plugin
must be ready for use by the authenticating account. If "Require TFA" is marked
then an account that has not setup TFA with the validation plugin will be unable
to log in.

#### Fallback plugins

With multiple validation plugins installed, TFA can be setup to handle fallback
options for a user going through the TFA process. For example, let's say a user
has setup SMS code delivery and TOTP via Google Authenticator app on their
phone. In the situation that the user has deleted the Authenticator app they
could fallback to SMS code delivery and still authenticate to the site.

### Plugin development

TFA plugins provide the form and validation handling for 2nd factor
authentication of a user. The TFA module will interrupt a successful username
and password authentication and begin the TFA process (see Configuration for
exceptions to this statement), passing off the form control and validation to
the active plugin.

#### Getting started
TODO
