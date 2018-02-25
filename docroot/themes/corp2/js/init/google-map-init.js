(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtGoogleMaps = {
    attach: function (context, settings) {

      var mapSelectorClass = "google-map-canvas";
      var mapSelector = "." + mapSelectorClass;

      function initialize() {
        $(context).find(mapSelector).once('mtGoogleMapsInit').each(function(index, item) {
          var map_locations_string = $(this).attr('data-attribute-sle-locations');
          var locations = JSON.parse(map_locations_string);
          var zoom = parseInt($(this).attr('data-attribute-sle-map-zoom'));

          var mapOptions = {
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            scrollwheel: false
          };

          var map = new google.maps.Map(this,mapOptions);
          var bounds = new google.maps.LatLngBounds();
          var infowindow = new google.maps.InfoWindow();
          var marker, i;

          for (i = 0; i < locations.length; i++) {  
            marker = new google.maps.Marker({
              position: new google.maps.LatLng(locations[i][1], locations[i][2]),
              map: map,
              draggable:false
            });
            bounds.extend(marker.position);
            google.maps.event.addListener(marker, 'click', (function(marker, i) {
              return function() {
                infowindow.setContent(locations[i][0]);
                infowindow.open(map, marker);
              }
            })(marker, i));
          }
          map.fitBounds(bounds);

          google.maps.event.addDomListener(window, "resize", function() {
            map.fitBounds(bounds);
          });

          if (zoom > 0) {
            var listener = google.maps.event.addListener(map, "idle", function () {
              map.setZoom(zoom);
              google.maps.event.removeListener(listener);
            });
          }

          $(".field--sle-collapsible-block .google-map-canvas", context).closest('.collapse').on('shown.bs.collapse', function() {
              google.maps.event.trigger(map, 'resize');
          });

        });
      }
      google.maps.event.addDomListener(window, "load", initialize);

    }
  };
})(jQuery, Drupal, drupalSettings);
