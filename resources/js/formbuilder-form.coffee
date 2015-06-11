ajaxForm = ->
  # AJAX + Parsleyjs Validation 
  notificationContainer = $('.formbuilder-notification')
  theForm = $('.formbuilder-form')

  # Parsleyjs
  theForm.parsley()

  # Prepare File Uploads
  file = undefined
  $('input[type=file]').on 'change', (event) ->
    file = event.target.files

  # AJAX Form Submit
  theForm.submit (e) ->
    notificationContainer.html ''
    e.stopPropagation()
    e.preventDefault()

    # Variables
    url = '/admin/actions/' + $(@).children('[name=action]').attr('value')
    redirect = $(@).children('[name=formredirect]').attr('data-redirect')
    redirectUrl = $(@).children('[name=formredirect]').attr('value')

    # Get the post data
    formData = $(@)[0]

    data = new FormData
    data.append('message', 'test')


    
    console.log data

    # data = $(@).serialize()


    # Validate Parsley
    if $(@).parsley().isValid()


      # Get the post data
      data = data.serialize()
      
      # Send it to the server
      $.post url, data, (response) ->
        if response.success
          if redirect == '1' 
            window.location.href = redirectUrl
          else
            notificationContainer.html '<p class="success-message">' + response.message + '</p>'
            theForm[0].reset()
        else
          notificationContainer.html '<p class="error-message">' + response.message + '</p>'

$(ajaxForm)