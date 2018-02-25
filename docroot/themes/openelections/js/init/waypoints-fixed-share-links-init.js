(function ($, Drupal) {
  Drupal.behaviors.mtWaypointsFixedShareLinks = {
    attach: function (context, settings) {
      var inview = new Waypoint.Inview({
        element: $('.node__main-content')[0],
        entered: function(direction) {
          $('body').removeClass('js-node-meta-fixed');
        },
        exit: function(direction) {
          if (direction = "down") {
            $('body').addClass('js-node-meta-fixed');
          }
        }
      });
    }
  };
})(jQuery, Drupal);
