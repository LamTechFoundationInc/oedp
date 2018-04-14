(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtowlCarouselTeamMembers = {
    attach: function (context, settings) {
      $(context).find('.sle-carousel-team-members').once('mtowlCarouselTeamMembersInit').each(function() {
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
          autoplay: drupalSettings.elections.owlCarouselTeamMembersInit.owlTeamMembersAutoPlay,
          autoplayTimeout: drupalSettings.elections.owlCarouselTeamMembersInit.owlTeamMembersEffectTime,
          nav: true,
          dots: false,
          loop: true,
          navText: [Drupal.t('prev', {}, {context: "Owl Carousel prev text"}),Drupal.t('next', {}, {context: "Owl Carousel next text"})]
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);
