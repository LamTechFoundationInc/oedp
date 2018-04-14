/**
* Add Javascript - Node features JS
*/
jQuery(document).ready(function($) {
  if ($("#affix").length>0) {
    $(window).load(function() {
      var affixBottom = $(".content-bottom").outerHeight(true)
      + $(".featured-top").outerHeight(true)
      + $(".featured").outerHeight(true)
      + $(".featured-bottom").outerHeight(true)
      + $(".sub-featured").outerHeight(true)
      + $(".footer-top").outerHeight(true)
      + $("footer.footer").outerHeight(true)
      + $(".footer-bottom").outerHeight(true)
      + $(".subfooter").outerHeight(true)
      + $(".main-content").outerHeight(true)
      - $(".block-system-main-block").outerHeight(true),
      affixTop = $("#affix").offset().top;

      //The admin overlay menu height
      if ($(".sticky-wrapper").length>0) {
        var navigationHeight = $(".sticky-wrapper").outerHeight(true);
      } else {
        var navigationHeight = 0;
      }
      var adminHeight = parseInt($('body').css('paddingTop'));
      var topValue = adminHeight + navigationHeight + 15;

      //We select the highest of the 2 adminHeight OR fixedHeaderHeight to use
      if (navigationHeight > adminHeight) {
        fixedAffixTop = navigationHeight;
      } else {
        fixedAffixTop = adminHeight;
      }

      function initializeAffix(topAffix) {
        affixBottom = affixBottom + navigationHeight - fixedAffixTop - adminHeight + 15;
        initAffixTop = topAffix - adminHeight - navigationHeight - 15; //The fixedAffixTop is added as padding on the page so we need to remove it from affixTop
        $("#affix").affix({
          offset: {
            top: initAffixTop,
            bottom: affixBottom
          }
        });
      }

      //The internal banner element is rendered after it is ready so initially it does not have height that can calculated
      //Therefore we manually add the height when we know it or we wait a few seconds to when its height is not known
      if ($(".view-sle-internal-banner").length>0) {
        var pageWidth = $("#page>.container").outerWidth();
        if (pageWidth == 1170) {
          affixTop = affixTop + 610;
          initializeAffix(affixTop);
        } else if (pageWidth == 970) {
          affixTop = affixTop + 506;
          initializeAffix(affixTop);
        } else if (pageWidth == 750) {
          affixTop = affixTop + 491;
          initializeAffix(affixTop);
        } else {
          setTimeout(function() {
            affixTop = $("#affix").offset().top;
            initializeAffix(affixTop);
          }, 2000);
        }
      } else {
        initializeAffix(affixTop);
      }

      $("#affix").on("affixed.bs.affix", function () {
        //We set through JS the inline style top position
        if ($(".fixed-header-enabled").length>0) {
          $("#affix").css("top", (navigationHeight+adminHeight)+"px");
        } else {
          $("#affix").css("top", (adminHeight)+"px");
        }
      });

    });
  }
});
