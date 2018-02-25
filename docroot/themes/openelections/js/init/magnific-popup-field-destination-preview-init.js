(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtMagnificPopupFieldDestinationPreview = {
    attach: function (context, settings) {
      $(context).find('.mt-carousel-destination-preview').once('mtMagnificPopupFieldDestinationPreviewInit').each(function() {
        $(this).find('.owl-item:not(.cloned) .image-popup').magnificPopup({
          type:"image",
          removalDelay: 300,
          mainClass: "mfp-fade",
          gallery: {
            enabled: true, // set to true to enable gallery
          },
          image: {
            titleSrc: function(item) {
              return item.el.closest('.overlay-container').children()[1].title || '';
            }
          }
        });
      });
    }
  };
})(jQuery, Drupal, drupalSettings);