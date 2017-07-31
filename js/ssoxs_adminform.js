/**
 * @file
 * Attaches the behaviors service administration page.
 */

(function ($) {

Drupal.behaviors.ssoxsAdminForm = {
  attach: function (context, settings) {

    // jQuery function to fold/unfold individual form fieldsets after clicking a
    // checkbox. Fieldsets are uniquely defined by an id that is passed to the
    // expandSection function by the onclick(expandSection(<unique id>)).
    // Synchronize checking/unchecking when clicking the 'subscribe' button.

    expandSection = function(id) { $('#' + id).slideToggle(); };

    // On window load loop over all 'service-block' classes and check if
    // service checkbox is checked. If so show the license terms if they
    // are available.
    $(window).load(function() {
      $(".service-block").each(function() {
        var block = $(this);
        if ($(this).find(':checkbox').is(':checked')) {
           $license = $(block).find(".license");
           if ($license[0]) {
             $license.show();
           }
         }
      });
    });
  }
};
})(jQuery);
