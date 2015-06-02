var ajaxForm;

ajaxForm = function() {
  var notificationContainer, theForm;
  notificationContainer = $('.formbuilder-notification');
  theForm = $('.formbuilder-form');
  theForm.parsley();
  return theForm.submit(function(e) {
    var data, url;
    notificationContainer.html('');
    e.preventDefault();
    url = '/admin/actions/' + $(this).children('[name=action]').attr('value');
    if ($(this).parsley().isValid()) {
      data = $(this).serialize();
      return $.post(url, data, function(response) {
        if (response.success) {
          console.log(response);
          notificationContainer.html('<p class="success-message">' + response.message + '</p>');
          return theForm[0].reset();
        } else {
          console.log(response);
          return notificationContainer.html('<p class="error-message">' + response.message + '</p>');
        }
      });
    }
  });
};

$(ajaxForm);
