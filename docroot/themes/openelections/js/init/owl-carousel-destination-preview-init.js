(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtowlCarouselDestinationPreview = {
    attach: function (context, settings) {
      $(context).find('.mt-carousel-destination-preview').once('mtowlCarouselDestinationPreviewInit').each(function() {
        $(this).owlCarousel({
          items: 1,
          responsive:{
            0:{
              items:1,
            },
            480:{
              items:1,
            },
            768:{
              items:1,
            },
            992:{
              items:1,
            },
            1200:{
              items:1,
            },
            1680:{
              items:1,
            }
          },
          autoplay: false,
          nav: true,
          dots: true,
          dotData: true,
          loop: true,
          navText: false
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
