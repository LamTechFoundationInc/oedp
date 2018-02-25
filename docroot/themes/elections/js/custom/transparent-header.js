(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtTransparentHeader = {
    attach: function (context, settings) {

      if ($('.banner .slideshow-fullscreen', context)) {
        $(".header-container").addClass("js-transparent-header");
      } else {
        $(".header-container").removeClass("js-transparent-header");
      }

      if ($('header.header', context)) {
        var header_color = $(".header-container.js-transparent-header header.header").css("background-color").replace(")", "," + drupalSettings.elections.transparentHeader.transparentHeaderOpacity + ")").replace("rgb", "rgba");
        $(".header-container.js-transparent-header header.header").css("background-color", header_color);
      }

      if ($('.header-top', context)) {
        var header_top_color = $(".header-container.js-transparent-header .header-top").css("background-color").replace(")", "," + drupalSettings.elections.transparentHeader.transparentHeaderOpacity + ")").replace("rgb", "rgba");
        $(".header-container.js-transparent-header .header-top").css("background-color", header_top_color);
      }
    }
  };
})(jQuery, Drupal, drupalSettings);