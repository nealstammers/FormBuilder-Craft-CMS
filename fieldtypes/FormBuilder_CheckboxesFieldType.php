<?php
namespace Craft;

/**
 * Class CheckboxesFieldType
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2014, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @see       http://buildwithcraft.com
 * @package   craft.app.fieldtypes
 * @since     1.0
 */
class FormBuilder_CheckboxesFieldType extends BaseOptionsFieldType
{
  // Properties
  // =========================================================================

  /**
   * @var bool
   */
  protected $multi = true;

  // Public Methods
  // =========================================================================

  /**
   * @inheritDoc IComponentType::getName()
   *
   * @return string
   */
  public function getName()
  {
    return Craft::t('| FormBuilder | Checkboxes');
  }

  /**
   * @inheritDoc IFieldType::getInputHtml()
   *
   * @param string $name
   * @param mixed  $values
   *
   * @return string
   */
  public function getInputHtml($name, $values)
  {
    // Variables
    $fieldId      = $name->id;
    $required     = $name->required;
    $options      = $this->getTranslatedOptions();
    $instructions = $name->instructions;
    $handle       = $name->handle;
    
    // Namespace our field id
    $id = craft()->templates->namespaceInputId($fieldId, 'field'); 

    // If this is a new entry, look for any default options
    if ($this->isFresh()) {
      $values = $this->getDefaultValue();
    }

    craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'formBuilder/templates');
    $html = craft()->templates->render('_includes/forms/checkboxGroup', array(
      'name'          => $name,
      'handle'        => $handle,
      'id'            => $id,
      'instructions'  => $instructions,
      'required'      => $required,
      'options'       => $options,
      'values'        => $values
    ));
    craft()->path->setTemplatesPath(craft()->path->getTemplatesPath());

    return $html;
  }

  // Protected Methods
  // =========================================================================

  /**
   * @inheritDoc BaseOptionsFieldType::getOptionsSettingsLabel()
   *
   * @return string
   */
  protected function getOptionsSettingsLabel()
  {
    return Craft::t('Checkbox Options');
  }
}
