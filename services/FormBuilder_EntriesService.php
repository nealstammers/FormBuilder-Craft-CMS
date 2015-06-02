<?php
namespace Craft;

class FormBuilder_EntriesService extends BaseApplicationComponent
{

	/**
	 * 
	 * Gell all entries
	 * 
	 */
	public function getAllEntries()
	{
		$entries = FormBuilder_EntryRecord::model()->findAll();
		return $entries;
	}

	/**
	 * 
	 * Gell all forms
	 * 
	 */
	public function getAllForms()
	{
		$forms = FormBuilder_FormRecord::model()->findAll();
		return $forms;
	}

	/**
	 * 
	 * Get forms by handle name
	 * 
	 */
	public function getFormByHandle($handle)
	{
		$formRecord = FormBuilder_FormRecord::model()->findByAttributes(array(
			'handle' => $handle,
		));

		if (!$formRecord) {	return false; }
		return FormBuilder_FormModel::populateModel($formRecord);
	}

	/**
	 * 
	 * Get entry by id
	 * 
	 */
	public function getFormEntryById($id)
	{
		return craft()->elements->getElementById($id, 'FormBuilder');
	}

	/**
	 * 
	 * Save Form Entry
	 * 
	 */
	public function saveFormEntry(FormBuilder_EntryModel $entry)
	{
		$entryRecord = new FormBuilder_EntryRecord();

		// Set attributes
		$entryRecord->formId = $entry->formId;
		$entryRecord->title = $entry->title;
		$entryRecord->data   = $entry->data;

		$entryRecord->validate();
		$entry->addErrors($entryRecord->getErrors());

		if (!$entry->hasErrors()) {
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try {
				if (craft()->elements->saveElement($entry))	{
					$entryRecord->id = $entry->id;
					$entryRecord->save(false);

					if ($transaction !== null) { $transaction->commit(); }
					return $entryRecord->id;
				} else { return false; }
			} catch (\Exception $e) {
				if ($transaction !== null) { $transaction->rollback(); }
				throw $e;
			}
			return true;
		}	else { return false; }
	}

	//======================================================================
  // Send Email Notification to Admin
  //======================================================================
	public function sendEmailNotification($form, $message, $html = true, $email = null)
	{	
		$errors = false;

		$email = new EmailModel();

		$email->toEmail		= $form->toEmail;
		$email->replyTo   = $form->toEmail;
		$email->fromName  = $form->name;
		$email->subject   = $form->subject;
		$email->htmlBody  = $message;

		$emailTo = explode(',', $form->toEmail);
		foreach ($emailTo as $emailAddress) {
			$email->toEmail = trim($emailAddress);
			if (!craft()->email->sendEmail($email)) {
				$errors = true;
			}
		}
		return $errors ? false : true;
	}

	//======================================================================
  // Send Email Notification to Submitter
  //======================================================================
	public function sendRegistrantEmailNotification($form, $message, $html = true, $email = null)
	{
		$errors = false;
		// TODO: CONVERT ALL FORM POST DATA "NAME" TO fields handle name!!!!!!!
		$emailTo = explode(',', $form->toEmail);
		var_dump($form);
		die();
		$email = new EmailModel();
		$email->toEmail   = $form->toEmail;
		$email->fromEmail = $emailTo[0];
		$email->replyTo   = $emailTo[0];
		$email->fromName  = 'Submission Notification';
		$email->subject   = $form->subject;
		$email->htmlBody  = $message;


		if (!craft()->email->sendEmail($email)) {
			$errors = true;
		}
		return $errors ? false : true;
	}

}