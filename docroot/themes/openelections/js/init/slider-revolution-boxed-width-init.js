(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.mtSliderRevolutionBoxedWidth = {
    attach: function (context, settings) {
      if (drupalSettings.openelections.sliderRevolutionBoxedWidthInit.slideshowBoxedWidthNavigationStyle == "bullets") {
        var bulletsEnable = true,
        tabsEnable = false;
      } else {
        var tabsEnable = true,
        bulletsEnable = false;
      }
      $(context).find('.slideshow-boxedwidth .rev_slider').once('mtSliderRevolutionBoxedWidthInit').show().revolution({
        sliderType:"standard",
        sliderLayout: "auto",
        gridwidth: [1140,970,750,450],
        gridheight: drupalSettings.openelections.sliderRevolutionBoxedWidthInit.slideshowBoxedWidthInitialHeight,
        autoHeight: "on",
        delay: drupalSettings.openelections.sliderRevolutionBoxedWidthInit.slideshowBoxedWidthEffectTime,
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
          bullets:{
            style:"",
            enable:bulletsEnable,
            direction:"horizontal",
            space: 5,
            h_align: drupalSettings.openelections.sliderRevolutionBoxedWidthInit.slideshowBoxedWidthBulletsPosition,
            v_align:"bottom",
            h_offset: 0,
            v_offset: 20,
            tmp:"",
          },
          tabs: {
            style:"",
            enable:tabsEnable,
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
          touch:{
            touchenabled: drupalSettings.openelections.sliderRevolutionBoxedWidthInit.slideshowBoxedWidthTouchSwipe,
            swipe_treshold:75,
            swipe_min_touches:1,
            drag_block_vertical:false,
            swipe_direction:"horizontal"
          }
        }
      });

      $(context).find('.transparent-background').once('mtSliderRevolutionBoxedWidthBG').css("backgroundColor", "rgba(0,0,0," + drupalSettings.openelections.slideshowBackgroundOpacity + ")");
      $(context).find('.tp-caption--transparent-background .tp-caption__title').once('mtSliderRevolutionBoxedWidthCaptionBG').css("backgroundColor", "rgba(0,0,0," + drupalSettings.openelections.slideshowCaptionOpacity + ")");
      $(context).find('.tp-caption--transparent-background .tp-caption__text').once('mtSliderRevolutionBoxedWidthCaptionBG').css("backgroundColor", "rgba(0,0,0," + drupalSettings.openelections.slideshowCaptionOpacity + ")");

    }
  };
})(jQuery, Drupal, drupalSettings);
