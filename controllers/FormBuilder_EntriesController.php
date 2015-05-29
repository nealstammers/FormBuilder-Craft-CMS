<?php
namespace Craft;

class FormBuilder_EntriesController extends BaseController
{
	protected $allowAnonymous = true;
  protected $defaultEmailTemplate = 'formbuilder/email/default';
	protected $defaultRegistrantEmailTemplate = 'formbuilder/email/registrant';
	
	/**
	 * View All Entries
	 */
	public function actionEntriesIndex()
	{
		// Get the data
		$variables['entries'] = craft()->formBuilder_entries->getAllEntries();
		$variables['tabs'] = $this->_getTabs();

		// Render entries template
		$this->renderTemplate('formbuilder/entries/index', $variables);
	}

  /**
   * View Single Entry
   */
	public function actionViewEntry(array $variables = array())
	{
		$entry              = craft()->formBuilder_entries->getFormEntryById($variables['entryId']);
		$variables['entry'] = $entry;

		if (empty($entry)) { throw new HttpException(404); }

		$variables['form']        = craft()->formBuilder_forms->getFormById($entry->formId);
		$variables['tabs']        = $this->_getTabs();
		$variables['selectedTab'] = 'entries';
		$variables['data']        = json_decode($entry->data, true);

		$this->renderTemplate('formbuilder/entries/_view', $variables);
	}

	/**
	 * Save Form Entry
	 */
	public function actionSaveFormEntry()
	{
		// Require a post request
		$this->requirePostRequest();
    // $this->requireAjaxRequest();

		// Get form by handle name
		$formBuilderHandle = craft()->request->getPost('formHandle');
		if (!$formBuilderHandle) { throw new HttpException(404);}

		// Get the form model, need this to save the entry
		$form = craft()->formBuilder_entries->getFormByHandle($formBuilderHandle);
		if (!$form) { throw new HttpException(404); }

    // Collect form submission data
    $data = craft()->request->getPost();

    // Filter out unused fields (action, handle, etc)
    $postData = $this->_filterPostKeys($data);

    // New form entry model
    $formBuilderEntry = new FormBuilder_EntryModel();

    // Set entry attributes
    $formBuilderEntry->formId     = $form->id;
    $formBuilderEntry->title      = $form->name;
    $formBuilderEntry->data       = $postData;


    // ###########################################################
    // Notifications
    // ###########################################################
    $sendNotification = false;
    $sendRegistrarNotification = false;
    
    // Notify Registrar
    if ($form->notifyRegistrant and $form->notificationFieldHandleName != '') {
      $sendRegistrarNotification = true;
    }
    // Notify Form Admin
    if ($form->notifyFormAdmin and $form->toEmail != '') {
      $sendNotification = true;
    }


    // ###########################################################
    // Save Form Entry To Database
    // ###########################################################
    if (craft()->formBuilder_entries->saveFormEntry($formBuilderEntry)) {

      // Notify Registrar if true
      if ($sendRegistrarNotification) {
        $this->_sendRegistrantEmailNotification($formBuilderEntry, $form);
      }

      // Notify Form Owner if true
      if ($sendNotification) {
        $this->_sendEmailNotification($formBuilderEntry, $form);
      }

      // Get success message from form settings 
      if (!empty($form->successMessage)) {
        $successMessage = $form->successMessage;
      } else {
        $successMessage =  Craft::t('Thank you, we have received your submission and we\'ll be in touch shortly.');
      }

      // Return success message
      $this->returnJson(
        ['success' => true, 'message' => $successMessage]
      );

    } else {
      // Get error message from form settings 
      if (!empty($form->errorMessage)) {
        $errorMessage = $form->errorMessage;
      } else {
        $errorMessage =  Craft::t('We\'re sorry, but something has gone wrong.');
      }

      // Return error message
      $this->returnJson(
        ['error' => true, 'message' => $errorMessage]
      );
    }

	}

	/**
	 * Delete Entry
	 */
	public function actionDeleteEntry()
	{
		$this->requirePostRequest();

		$entryId = craft()->request->getRequiredPost('entryId');

		if (craft()->elements->deleteElementById($entryId)) {
			craft()->userSession->setNotice(Craft::t('Entry deleted.'));
			$this->redirectToPostedUrl();
			craft()->userSession->setError(Craft::t('Couldn’t delete entry.'));
		}

	}

	/**
   * TODO: FIXED THIS FUNCTION
	 * Send Email Notification
	 */
	protected function _sendEmailNotification($record, $form)
	{  

		// Put in work setting up data for the email template.
		$data = new \stdClass($data);
		$data->entryId   = $record->id;

		$postData = unserialize($record->data);
		$postData = $this->_filterPostKeys($postData);

		foreach ($postData as $key => $value) {
			$data->$key = $value;
		}

		// Email template
		if (craft()->templates->findTemplate($form->notificationTemplatePath)) {
			$template = $form->notificationTemplatePath;
		}

		if (!$template) {
			$template = $this->defaultEmailTemplate;
		}

		$variables = array(
			'data'  => $postData,
			'form'  => $form,
			'entry' => $record,
		);

		$message  = craft()->templates->render($template, $variables);

		// Send notification to form owner
		if (craft()->formBuilder_entries->sendEmailNotification($form, $message, true, null)) {
			return true;
		} else {
			return false;
		}

	}

	/**
   * TODO: FIXED THIS FUNCTION
	 * Send Email Notification
	 */
	protected function _sendRegistrantEmailNotification($record, $form)
	{
		// Put in work setting up data for the email template.
		$data = new \stdClass($data);
		$data->entryId   = $record->id;

		$postData = unserialize($record->data);
		$postData = $this->_filterPostKeys($postData);

		foreach ($postData as $key => $value) {
			$data->$key = $value;
		}

		// Email template
		if (craft()->templates->findTemplate($form->notificationTemplatePathRegistrant)) {
			$template = $form->notificationTemplatePathRegistrant;
		}

		if (!$template) {
			$template = $this->defaultRegistrantEmailTemplate;
		}

		$variables = array(
			'data'  => $postData,
			'form'  => $form,
			'entry' => $record,
		);

		$message  = craft()->templates->render($template, $variables);

    // Send notification to form owner
    if (craft()->formBuilder_entries->sendRegistrantEmailNotification($form, $message, true, null)) {
      return true;
    } else {
      return false;
    }

	}

  /**
   * Filter out unused post data
   */
	protected function _filterPostKeys($post)
	{
		$filterKeys = array(
			'action',
			'redirect',
			'formhandle'
		);
		if (isset($post['honeypot'])) {
			$honeypot = $post['honeypot'];
			array_push($filterKeys, $honeypot);
		}
		if (is_array($post)) {
			foreach ($post as $k => $v) {
				if (in_array(strtolower($k), $filterKeys)) {
					unset($post[$k]);
				}
			}
		}
		return $post;
	}

  /**
   * Get Page Tabs
   */
	protected function _getTabs()
	{
		return array(
			'forms' => array(
				'label' => "Forms", 
				'url'   => UrlHelper::getUrl('formbuilder/'),
			),
			'entries' => array(
				'label' => "Entries", 
				'url'   => UrlHelper::getUrl('formbuilder/entries'),
			),
		);
	}
}
