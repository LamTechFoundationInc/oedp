INTRODUCTION
------------

Geolocation Street View extends upon Geolocation Field to save and display
Street View POVs.

REQUIREMENTS
------------

This module requires the following modules:

 * Geolocation Field (https://www.drupal.org/project/geolocation)

INSTALLATION
------------

Install as you would normally install a contributed Drupal module. See
https://www.drupal.org/documentation/install/modules-themes/modules-8.

CONFIGURATION
-------------

 * Create a Geolocation field and configure both the widget and formatter to
   "Geolocation Street View".
 * Optionally, configure the formatter and disable displaying the address info
   and the Street View close button.
 * When creating field contents, go to the desired location and drop the Street
   View marker.
 * Configure the POV and save the contents.
 * Display the content. The field will show the configured Street View POV.

KNOWN BUGS
----------

 * You can configure a default location for a field, but not a default Street
   View POV. An error is displayed upon saving.

MAINTAINERS
-----------

Current maintainers:
 * Dietrich Moerman (dietr_ch) - https://www.drupal.org/u/dietr_ch

This project has been sponsored by:
 * EntityOne - https://entityone.be/
