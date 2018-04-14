(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtFlexsliderInPage = {
    attach: function (context, settings) {

      // store the slider in a local variable
      var $window = $(window),
      theSlider;

      // tiny helper function to add breakpoints
      function getGridSize() {
        return (window.innerWidth < 768) ? 2 : 3;
      }

      $(context).find('.in-page-images-slider').once('mtFlexsliderInPage').fadeIn("slow");
      $(context).find('.in-page-images-carousel').once('mtFlexsliderInPage').fadeIn("slow");

      // The slider being synced must be initialized first
      $(context).find('.in-page-images-carousel').once('mtFlexsliderInPageCarousel').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: false,
        slideshow: false,
        itemWidth: 172.5,
        itemMargin: 20,
        prevText: "",
        nextText: "",
        asNavFor: ".in-page-images-slider",
        minItems: getGridSize(), // use function to pull in initial value
        maxItems: getGridSize(), // use function to pull in initial value
        start: function(slider){
          theSlider = slider;
          $window.resize(function() {
            var gridSize = getGridSize();
            theSlider.vars.minItems = gridSize;
            theSlider.vars.maxItems = gridSize;
          });
        }
      });
      $(context).find('.in-page-images-slider').once('mtFlexsliderInPageSlider').flexslider({
        useCSS: false,
        animation: drupalSettings.openelections.flexsliderInPageInit.inPageSliderEffect,
        controlNav: false,
        directionNav: true,
        prevText: "",
        nextText: "",
        animationLoop: false,
        slideshow: false,
        sync: ".in-page-images-carousel"
      });

    }
  };
})(jQuery, Drupal, drupalSettings);
