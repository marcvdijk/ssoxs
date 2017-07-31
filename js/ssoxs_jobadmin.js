/**
 * @file
 * Attaches the behaviors job administration page.
 */

(function ($) {

Drupal.behaviors.ssoxsJobAdmin = {
  attach: function (context, settings) {

    //Show the job logs when clicking the message icon.
    $(".log").click(function() {
      $(this).parent().find('.log_display').show();
    });

    //Hide the job logs when clicking the close button.
    $(".close_btn").click(function() {
      $(this).parent().hide();
    });
  
  }
};
})(jQuery);
