/**
 * @file
 * Attaches the behaviors My Services page.
 */

(function ($) {

Drupal.behaviors.ssoxsJobAdmin = {
  attach: function (context, settings) {

    // Perform AJAX call to the certificate validation URL on behalf of the user
    // to check validity of certificate loaded in users browser.
    // The remote server must implement the CORS protocol.
    validateCertificate = function(service, http) {

      // Change Drupal AJAX throbber to spinning
      $("#cert-validate").addClass('active');

      setTimeout(function() {
        var $status = $("#cert-validate");

        var valid = false;
        $.ajax({
           async: false,
           type: 'GET',
           url: http,
           success: function(data) {
             valid = true;
           }
        });

        if (valid) {
          $status.replaceWith('<span id="cert-validate" class="success">Certificate validation successful</span>');
          $("#edit-service-" + service + "-certificate").attr('checked', 'checked');
        }
        else {
          $status.replaceWith('<span id="cert-validate" class="failed">Certificate validation failed</span>');
          $("#edit-service-" + service + "-certificate").removeAttr('checked');
        }
      }, 1000);
    };

    $(window).load(function() {

      // Bind click event to the service name table header cell (class status0).
      $('.name').click(function() {

        var $parent = $(this).closest('table');
        $parent.find('tbody').slideToggle();

      });

      // Bind click event to all table 'step' cells. Click on cell
      // expands re-wrapper div and folds all other ones.
      $('.step').click(function() {

        var target = $(this).parent().get(0);
        var $tbody = $(this).closest('tbody');

        $tbody.find('tr').each(function() {
          if (this === target) {
            $(this).find('.req-wrapper').slideToggle();
          }
          else {
            $checkvis = $(this).find('.req-wrapper');
            if ($checkvis.is(':visible')) {
              $checkvis.slideToggle();
            }
          }
        });

      });
    });
   } 
 };
})(jQuery);
