(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtMainSlideshowBlock = {
    attach: function (context, settings) {

      $(context).find('.main-slideshow-block .rev_slider').once('mtMainSlideshowBlockInit').show().each(function() {
        $(this).revolution({
          sliderType:"standard",
          sliderLayout: $(this).closest(".main-slideshow-block").attr('data-attribute-op-layout'),
          gridwidth: [1170,970,750,450],
          gridheight: parseInt($(this).closest(".main-slideshow-block").attr('data-attribute-op-initial-height')),
          autoHeight: $(this).closest(".main-slideshow-block").attr('data-attribute-op-autoheight'),
          fullScreenOffsetContainer: $(this).closest(".main-slideshow-block").attr('data-attribute-op-ignore-header'),
          delay: $(this).closest(".main-slideshow-block").attr('data-attribute-op-effect-duration'),
          disableProgressBar:'off',
          responsiveLevels:[1199,991,767,480],
          navigation: {
            onHoverStop:"off",
            arrows:{
              enable:true,
              tmp: "<div class='tp-title-wrap'><span class='tp-arr-titleholder'>{{title}}</span></div>",
              left:{
                h_align:"left",
                v_align:"center",
                h_offset:0,
                v_offset:0
              },
              right:{
                h_align:"right",
                v_align:"center",
                h_offset:0,
                v_offset:0
              }
            },
            tabs: {
              style:"",
              enable: $(this).closest(".main-slideshow-block").attr('data-attribute-op-tabs-enable'),
              width:410,
              height:95,
              min_width:240,
              wrapper_padding: 0,
              wrapper_opacity:"1",
              tmp:'<div class="tp-tab-content"><span class="tp-tab-title">{{title}}</span></div>',
              visibleAmount: 6,
              hide_onmobile: false,
              hide_onleave: false,
              direction:"horizontal",
              span: true,
              position:"outer-bottom",
              space:0,
              h_align:"left",
              v_align:"bottom",
              h_offset:0,
              v_offset:0
            },
            bullets:{
              style:"",
              enable: $(this).closest(".main-slideshow-block").attr('data-attribute-op-bullets-enable'),
              direction:"horizontal",
              space: 5,
              h_align: $(this).closest(".main-slideshow-block").attr('data-attribute-op-bullets-position'),
              v_align:"bottom",
              h_offset: 0,
              v_offset: 20,
              tmp:"",
            },
            touch:{
              touchenabled: $(this).closest(".main-slideshow-block").attr('data-attribute-op-touch-swipe-nav'),
              swipe_treshold:75,
              swipe_min_touches:1,
              drag_block_vertical:false,
              swipe_direction:"horizontal"
            }
          }
        });

        $(this).find(".ms-transparent-background").each(function() {
          var captionOpacity = $(this).attr('data-attribute-op-caption-opacity'),
           captionBackgroundColor = $(this).find('.tp-caption__title').css("background-color").replace(")", "," + captionOpacity + ")").replace("rgb", "rgba");
           $(this).find('.tp-caption__title').css("background-color", captionBackgroundColor);
           $(this).find('.tp-caption__text').css("background-color", captionBackgroundColor);
        });

        $(this).find(".transparent-background").css("backgroundColor", "rgba(0,0,0," + $(this).closest(".main-slideshow-block").attr('data-attribute-op-background-opacity') + ")");
      });

    }
  };
})(jQuery, Drupal, drupalSettings);
