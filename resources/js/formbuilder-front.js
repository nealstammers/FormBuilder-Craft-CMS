(function($){

  $('#contactForm').submit(function(e) {
    e.preventDefault();


    var url = '/admin/actions/' + $(this).children("[name=action]").attr('value');

    // Validate Parsley
    if ($(this).parsley().isValid()) {

      // Get the post data
      var data = $(this).serialize();


      // Send it to the server
      $.post(url, data, function(response) {
        if (response.success) {
          console.log(response);
          $('#contactForm-notification').html('<p class="success-message">'+response.message+'</p>');
          document.getElementById("contactForm").reset();
        } else {
          console.log(response);
          $('#contactForm-notification').html('<p class="error-message">'+response.message+'</p>');
        }
      });

    
    } // End parlsey

  });

})(jQuery);