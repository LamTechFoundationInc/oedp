/**
 * @file
 * Initiates map(s) for the Styled Google Map module.
 *
 * A single or multiple Styled Google Maps will be initiated.
 * Drupal behaviors are used to make sure ajax called map(s) are correctly loaded.
 */

(function ($) {
  Drupal.behaviors.styled_google_maps = {
    attach: function (context) {
      //console.log(drupalSettings.styled_google_map);
      var maps = drupalSettings.styled_google_map;
      var markers = [];
      for (i in maps) {
        var current_map = drupalSettings.maps['id' + maps[i]];
        var map_id = current_map.id;
        if ($('#' + map_id).length) {
          var map_locations = current_map.locations;
          var map_settings = current_map.settings;
          var bounds = new google.maps.LatLngBounds();
          var map_types = {
            'ROADMAP': google.maps.MapTypeId.ROADMAP,
            'SATELLITE': google.maps.MapTypeId.SATELLITE,
            'HYBRID': google.maps.MapTypeId.HYBRID,
            'TERRAIN': google.maps.MapTypeId.TERRAIN
          }
          $('#' + map_id).css({'width':current_map.settings.width, 'height' : current_map.settings.height});
          var map_style = (map_settings.style.style != '' ? map_settings.style.style : '[]');

          map_settings.draggable = $(window).width() > 480 ? map_settings.draggable : map_settings.mobile_draggable;

          var init_map = {
            zoom: parseInt(map_settings.zoom.default),
            mapTypeId: map_types[map_settings.style.maptype],
            disableDefaultUI: !map_settings.ui,
            maxZoom: parseInt(map_settings.zoom.max),
            minZoom: parseInt(map_settings.zoom.min),
            styles: JSON.parse(map_style),
            mapTypeControl: map_settings.maptypecontrol,
            scaleControl: map_settings.scalecontrol,
            rotateControl: map_settings.rotatecontrol,
            streetViewControl: map_settings.streetviewcontrol,
            zoomControl: map_settings.zoomcontrol,
            scrollwheel: map_settings.scrollwheel,
            draggable: map_settings.draggable
          }
          var map = new google.maps.Map(document.getElementById(map_id), init_map);
          var infoBubble = new InfoBubble({
            shadowStyle: parseInt(map_settings.popup.shadow_style),
            padding: parseInt(map_settings.popup.padding),
            borderRadius: parseInt(map_settings.popup.border_radius),
            borderWidth: parseInt(map_settings.popup.border_width),
            borderColor: map_settings.popup.border_color,
            backgroundColor: map_settings.popup.background_color,
            minWidth: map_settings.popup.min_width,
            maxWidth: map_settings.popup.max_width,
            maxHeight: map_settings.popup.min_height,
            minHeight: map_settings.popup.max_height,
            arrowStyle: parseInt(map_settings.popup.arrow_style),
            arrowSize: parseInt(map_settings.popup.arrow_size),
            arrowPosition: parseInt(map_settings.popup.arrow_position),
            disableAutoPan: parseInt(map_settings.popup.disable_auto_pan),
            disableAnimation: parseInt(map_settings.popup.disable_animation),
            hideCloseButton: parseInt(map_settings.popup.hide_close_button),
            backgroundClassName: map_settings.popup.classes.background,
          });
          // Set extra custom classes for easy styling.
          infoBubble.bubble_.className = 'sgmpopup sgmpopup-' + this.category;
          // infoBubble.close_.src = map_settings.style.active_pin;
          infoBubble.contentContainer_.className = map_settings.popup.classes.container;
          infoBubble.arrow_.className = map_settings.popup.classes.arrow;
          infoBubble.arrowOuter_.className = map_settings.popup.classes.arrow_outer;
          infoBubble.arrowInner_.className = map_settings.popup.classes.arrow_inner;
          if (map_settings.style.cluster_pin) {
            var clusterStyles = [
              {
                textColor: 'white',
                url: map_settings.style.cluster_pin,
                height: 73,
                width: 73,
                textSize: 17
              },
              {
                textColor: 'white',
                url: map_settings.style.cluster_pin,
                height: 73,
                width: 73,
                textSize: 17
              },
              {
                textColor: 'white',
                url: map_settings.style.cluster_pin,
                height: 73,
                width: 73,
                textSize: 17
              }
            ];
            var mcOptions = {
              gridSize: 60,
              styles: clusterStyles,
              minimumClusterSize: 2,
            };
          }
          for (j in map_locations) {
            var marker = new google.maps.Marker({
              position: new google.maps.LatLng(map_locations[j].lat , map_locations[j].lon),
              map: map,
              html: map_locations[j].popup,
              icon: map_locations[j].pin,
              original_icon: map_locations[j].pin,
              active_icon: map_locations[j].pin,
              category: map_locations[j].category
            });
            markers.push(marker);
            if (map_locations[j].popup) {
              google.maps.event.addListener(marker, 'click', (function(map){
                  return function() {
                      infoBubble.setContent(this.html);
                      for (var i = 0; i < markers.length; i++) {
                         markers[i].setIcon(markers[i].original_icon);
                      }
                      this.setIcon(this.active_icon);
                      infoBubble.open(map, this);
                  };
              }(map)));
            }
            bounds.extend(marker.getPosition());
          }
          if (map_settings.style.cluster_pin) {
            var markerCluster = new MarkerClusterer(map, markers, mcOptions);
          }
        }
        if (map_settings.map_center && map_settings.map_center.center_coordinates) {
          var map_center = new google.maps.LatLng(map_settings.map_center.center_coordinates.lat , map_settings.map_center.center_coordinates.lon)
          bounds.extend(map_center);
          map.setCenter(map_center);
        } else {
          map.setCenter(bounds.getCenter());
        }
        // This is needed to set the zoom after fitbounds,
        google.maps.event.addListener(map, 'zoom_changed', function() {
          zoomChangeBoundsListener =
              google.maps.event.addListener(map, 'bounds_changed', function(event) {
              var current_zoom = this.getZoom();
              if (current_zoom > parseInt(map_settings.zoom.default) && map.initialZoom == true) {
                // Change max/min zoom here
                this.setZoom(parseInt(map_settings.zoom.default) - 1);
                map.initialZoom = false;
              }
              google.maps.event.removeListener(zoomChangeBoundsListener);
            });
        });
        map.initialZoom = true;
        map.fitBounds(bounds);
      }
      // Prevents piling up generated map ids.
      drupalSettings.styled_google_map = [];
    }
  }
  /*if (Drupal.ajax !== undefined) {
    // Fixes ajax view filters with refreshed settings
    Drupal.ajax.prototype.commands.settings = function (ajax, response, status) {
      if (ajax.element_settings.event == 'RefreshView') {
        ajax.settings = response.settings;
      }
    };
  }*/
})(jQuery);
