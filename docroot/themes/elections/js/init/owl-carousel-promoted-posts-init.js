(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtowlCarouselPromotedPosts = {
    attach: function (context, settings) {
      $(context).find('.sle-carousel-promoted-posts').once('mtowlCarouselPromotedPostsInit').each(function() {
        $(this).owlCarousel({
          items: 2,
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
              items:2,
            },
            1200:{
              items:4,
            },
            1680:{
              items:4,
            }
          },
          autoplay: drupalSettings.elections.owlCarouselPromotedPostsInit.owlPromotedPostsAutoPlay,
          autoplayTimeout: drupalSettings.elections.owlCarouselPromotedPostsInit.owlPromotedPostsEffectTime,
          nav: true,
          dots: false,
          loop: true,
          navText: [Drupal.t('prev', {}, {context: "Owl Carousel prev text"}),Drupal.t('next', {}, {context: "Owl Carousel next text"})]
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
