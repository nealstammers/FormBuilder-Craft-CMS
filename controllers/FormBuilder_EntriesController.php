<?php
namespace Craft;

class FormBuilder_EntriesController extends BaseController
{
	protected $allowAnonymous = true;
  protected $defaultEmailTemplate = 'formbuilder/email/default';
	protected $defaultRegistrantEmailTemplate = 'formbuilder/email/registrant';
	
	/**
	 * View Form Entry
	 */
	public function actionEntriesIndex()
	{
		// Get the data
		$variables['entries'] = craft()->formBuilder_entries->getAllEntries();
		$variables['tabs'] = $this->_getTabs();

		// Render the template!
		$this->renderTemplate('formbuilder/entries/index', $variables);
	}

	public function actionViewEntry(array $variables = array())
	{
		$entry              = craft()->formBuilder_entries->getFormEntryById($variables['entryId']);
		$variables['entry'] = $entry;

		if (empty($entry)) { throw new HttpException(404); }

		$variables['form']        = craft()->formBuilder_forms->getFormById($entry->formId);
		$variables['tabs']        = $this->_getTabs();
		$variables['selectedTab'] = 'entries';
		$variables['data']        = $this->_filterPostKeys(unserialize($entry->data));

		$this->renderTemplate('formbuilder/entries/_view', $variables);
	}


	/**
	 * Save Form Entry
	 */
	public function actionSaveFormEntry()
	{
		// Require a post request
		$this->requirePostRequest();
    $this->requireAjaxRequest();

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


	public function validateAddress($address, $patternselect = 'auto')
	{
    if (!$patternselect or $patternselect == 'auto') {
      //Check this constant first so it works when extension_loaded() is disabled by safe mode
      //Constant was added in PHP 5.2.4
      if (defined('PCRE_VERSION')) {
        //This pattern can get stuck in a recursive loop in PCRE <= 8.0.2
        if (version_compare(PCRE_VERSION, '8.0.3') >= 0) {
          $patternselect = 'pcre8';
        } else {
          $patternselect = 'pcre';
        }
      } elseif (function_exists('extension_loaded') and extension_loaded('pcre')) {
        //Fall back to older PCRE
        $patternselect = 'pcre';
      } else {
        //Filter_var appeared in PHP 5.2.0 and does not require the PCRE extension
        if (version_compare(PHP_VERSION, '5.2.0') >= 0) {
            $patternselect = 'php';
        } else {
          $patternselect = 'noregex';
        }
      }
    }
    switch ($patternselect) {
	    case 'pcre8':
        /**
         * Uses the same RFC5322 regex on which FILTER_VALIDATE_EMAIL is based, but allows dotless domains.
         * @link http://squiloople.com/2009/12/20/email-address-validation/
         * @copyright 2009-2010 Michael Rushton
         * Feel free to use and redistribute this code. But please keep this copyright notice.
         */
        return (boolean)preg_match(
          '/^(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){255,})(?!(?>(?1)"?(?>\\\[ -~]|[^"])"?(?1)){65,}@)' .
          '((?>(?>(?>((?>(?>(?>\x0D\x0A)?[\t ])+|(?>[\t ]*\x0D\x0A)?[\t ]+)?)(\((?>(?2)' .
          '(?>[\x01-\x08\x0B\x0C\x0E-\'*-\[\]-\x7F]|\\\[\x00-\x7F]|(?3)))*(?2)\)))+(?2))|(?2))?)' .
          '([!#-\'*+\/-9=?^-~-]+|"(?>(?2)(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\x7F]))*' .
          '(?2)")(?>(?1)\.(?1)(?4))*(?1)@(?!(?1)[a-z0-9-]{64,})(?1)(?>([a-z0-9](?>[a-z0-9-]*[a-z0-9])?)' .
          '(?>(?1)\.(?!(?1)[a-z0-9-]{64,})(?1)(?5)){0,126}|\[(?:(?>IPv6:(?>([a-f0-9]{1,4})(?>:(?6)){7}' .
          '|(?!(?:.*[a-f0-9][:\]]){8,})((?6)(?>:(?6)){0,6})?::(?7)?))|(?>(?>IPv6:(?>(?6)(?>:(?6)){5}:' .
          '|(?!(?:.*[a-f0-9]:){6,})(?8)?::(?>((?6)(?>:(?6)){0,4}):)?))?(25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
          '|[1-9]?[0-9])(?>\.(?9)){3}))\])(?1)$/isD',
          $address
        );
	    case 'pcre':
        //An older regex that doesn't need a recent PCRE
        return (boolean)preg_match(
          '/^(?!(?>"?(?>\\\[ -~]|[^"])"?){255,})(?!(?>"?(?>\\\[ -~]|[^"])"?){65,}@)(?>' .
          '[!#-\'*+\/-9=?^-~-]+|"(?>(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\xFF]))*")' .
          '(?>\.(?>[!#-\'*+\/-9=?^-~-]+|"(?>(?>[\x01-\x08\x0B\x0C\x0E-!#-\[\]-\x7F]|\\\[\x00-\xFF]))*"))*' .
          '@(?>(?![a-z0-9-]{64,})(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)(?>\.(?![a-z0-9-]{64,})' .
          '(?>[a-z0-9](?>[a-z0-9-]*[a-z0-9])?)){0,126}|\[(?:(?>IPv6:(?>(?>[a-f0-9]{1,4})(?>:' .
          '[a-f0-9]{1,4}){7}|(?!(?:.*[a-f0-9][:\]]){8,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?' .
          '::(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,6})?))|(?>(?>IPv6:(?>[a-f0-9]{1,4}(?>:' .
          '[a-f0-9]{1,4}){5}:|(?!(?:.*[a-f0-9]:){6,})(?>[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4})?' .
          '::(?>(?:[a-f0-9]{1,4}(?>:[a-f0-9]{1,4}){0,4}):)?))?(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}' .
          '|[1-9]?[0-9])(?>\.(?>25[0-5]|2[0-4][0-9]|1[0-9]{2}|[1-9]?[0-9])){3}))\])$/isD',
          $address
        );
	    case 'html5':
        /**
         * This is the pattern used in the HTML5 spec for validation of 'email' type form input elements.
         * @link http://www.whatwg.org/specs/web-apps/current-work/#e-mail-state-(type=email)
         */
        return (boolean)preg_match(
          '/^[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}' .
          '[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/sD',
          $address
        );
	    case 'noregex':
        //No PCRE! Do something _very_ approximate!
        //Check the address is 3 chars or longer and contains an @ that's not the first or last char
        return (strlen($address) >= 3
          and strpos($address, '@') >= 1
          and strpos($address, '@') != strlen($address) - 1);
	    case 'php':
	    default:
        return (boolean)filter_var($address, FILTER_VALIDATE_EMAIL);
    }
	}

	protected function _filterPostKeys($post)
	{
		$filterKeys = array(
			'action',
			'redirect',
			'formhandle',
			'honeypot'
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
