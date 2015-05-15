<?php
namespace Craft;

class FormBuilderVariable
{
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





  // function pluginPath($name, $criteria)
  // {
  //   craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'formBuilder/templates');

    

  //   $variables = $this->getInputTemplateVariables($name, $criteria);
  //   return craft()->templates->render($this->inputTemplate, $variables);




  //   $template = craft()->templates->render('_fields/fields/text', ['text']);


  //   craft()->path->setTemplatesPath(craft()->path->getSiteTemplatesPath());

  //   return $template;
  // }






  function getInputHtml($field) {

    // Field
    $theField = craft()->fields->getFieldById($field->fieldId);

    // Get Required Field
    $requiredField = $field->required;

    // Add Required Value to Field if true
    $theField->required = $requiredField;

    // Namespace our field id
    $namespacedId = craft()->templates->namespaceInputId($field->fieldId, 'field');

    // Populate FieldModel
    // $fieldType = craft()->fields->populateFieldType($theField, null);
    $fieldType = craft()->fields->populateFieldType($theField, null);
    $input = $fieldType->getInputHtml($theField, null);


    // {% set input = fieldtype.getInputHtml(field.handle, value) %}



    // Render and return the input template
    // return craft()->templates->render('formBuilder/_fields/field', array(
    //   'field'         => $name,
    //   'id'           => $id,
    //   'namespacedId' => $namespacedId,
    //   'value'        => $value
    // ));

    return $fieldType;
  }

  function inputHtml($fieldHandle, $value = null) {
    return $fieldHandle;
  }




}
