<?php
namespace Craft;

class FormBuilderVariable
{

  protected $inputJsClass;


	function entries()
	{
		return craft()->elements->getCriteria('FormBuilder_Entry');
	}

  function getFormByHandle($formHandle)
  {
    return craft()->formBuilder_forms->getFormByHandle($formHandle);
  }

  function getFormById($formId)
  {
    return craft()->formBuilder_forms->getFormById($formId);
  }


  /**
   * Returns the field's input HTML.
   *
   * @param string $field
   *
   * @return string
   */
  function getInputHtml($field) 
  {
    
    $theField = craft()->fields->getFieldById($field->fieldId); // Get the field
    $fieldType = $theField->getFieldType(); // Get fieldtype
    $requiredField = $field->required; // Get Required Field
    $theField->required = $requiredField; // Add Required Value to Field if true

    //
    // UPDATE TEMPLATE PATHS
    //
    craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'formBuilder/templates'); // Change template path to FormBuilder
    $getPluginInputHtml = $fieldType->getInputHtml($theField, null); // Load our input html 
    craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath()); // Reset tempalte path to Craft

    return $getPluginInputHtml;
  }
}