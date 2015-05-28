(function($){

  $('#contactForm').submit(function(e) {
    e.preventDefault();

    var url = '/admin/actions/' + $(this).children("[name=action]").attr('value');

    console.log(url);
    // Validate Parsley
    if ($(this).parsley().isValid()) {

      // Get the post data
      var data = $(this).serialize();


      // Send it to the server
      $.post(url, data, function(response) {
        if (response.success) {
          console.log(response);
          // $('#contactForm-notification').html('Thanks, form submitted.');
        } else {
          console.log(response);
          // $('#contactForm-notification').html('An error occurred. Please try again.');
        }
      });
    
    } // End parlsey

  });

})(jQuery);