(function ($) {

  "use strict";

  Drupal.behaviors.dropdogy_mobileNavMenu = {
    // perform jQuery as normal in here
    attach: function (context, settings) {
      // For devices smaller than $tab. Show the menu on click.
      if ($(window).width() < 768) {
        $('#block-header-mobilenavbuttondemo', context).click(function(event) {
          $('#block-rc-theme-main-menu > ul').slideToggle('fast');
        });
      }
    }
  };
})(jQuery);
