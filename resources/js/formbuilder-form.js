var ajaxForm;

ajaxForm = function() {
  var file, notificationContainer, theForm;
  notificationContainer = $('.formbuilder-notification');
  theForm = $('.formbuilder-form');
  theForm.parsley();
  file = void 0;
  $('input[type=file]').on('change', function(event) {
    return file = event.target.files;
  });
  return theForm.submit(function(e) {
    var data, formData, redirect, redirectUrl, url;
    notificationContainer.html('');
    e.stopPropagation();
    e.preventDefault();
    url = '/admin/actions/' + $(this).children('[name=action]').attr('value');
    redirect = $(this).children('[name=formredirect]').attr('data-redirect');
    redirectUrl = $(this).children('[name=formredirect]').attr('value');
    formData = $(this)[0];
    data = new FormData;
    data.append('message', 'test');
    console.log(data);
    if ($(this).parsley().isValid()) {
      data = data.serialize();
      return $.post(url, data, function(response) {
        if (response.success) {
          if (redirect === '1') {
            return window.location.href = redirectUrl;
          } else {
            notificationContainer.html('<p class="success-message">' + response.message + '</p>');
            return theForm[0].reset();
          }
        } else {
          return notificationContainer.html('<p class="error-message">' + response.message + '</p>');
        }
      });
    }
  });
};

$(ajaxForm);
