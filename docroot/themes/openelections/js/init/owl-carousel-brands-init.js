(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtOwlCarouselBrands = {
    attach: function (context, settings) {
      $(context).find('.mt-carousel-brands').once('mtowlCarouselBrandsInit').each(function() {
        $(this).owlCarousel({
          items: 2,
          responsive:{
            0:{
              items:2,
            },
            480:{
              items:2,
            },
            768:{
              items:3,
            },
            992:{
              items:4,
            },
            1200:{
              items:5,
            },
            1680:{
              items:5,
            }
          },
          autoplay: drupalSettings.openelections.owlCarouselBrandsInit.owlBrandsAutoPlay,
          autoplayTimeout: drupalSettings.openelections.owlCarouselBrandsInit.owlBrandsEffectTime,
          nav: true,
          dots: false,
          loop: true,
          navText: [Drupal.t('prev', {}, {context: "Owl Carousel prev text"}),Drupal.t('next', {}, {context: "Owl Carousel next text"})]
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
