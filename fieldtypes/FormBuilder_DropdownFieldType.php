<?php
namespace Craft;

/**
 * Class DropdownFieldType
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2014, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @see       http://buildwithcraft.com
 * @package   craft.app.fieldtypes
 * @since     1.0
 */
class FormBuilder_DropdownFieldType extends BaseOptionsFieldType
{
  // Public Methods
  // =========================================================================

  /**
   * @inheritDoc IComponentType::getName()
   *
   * @return string
   */
  public function getName()
  {
    return Craft::t('| FormBuilder | Dropdown');
  }

  /**
   * @inheritDoc IFieldType::getInputHtml()
   *
   * @param string $name
   * @param mixed  $value
   *
   * @return string
   */
  public function getInputHtml($name, $value)
  {
    // Variables
    $fieldId      = $name->id;
    $required     = $name->required;
    $options      = $this->getTranslatedOptions();
    $instructions = $name->instructions;

    // Namespace our field id
    $id = craft()->templates->namespaceInputId($fieldId, 'field'); 

    // If this is a new entry, look for a default option
    if ($this->isFresh()) {
      $value = $this->getDefaultValue();
    }

    craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'formBuilder/templates');
    $html = craft()->templates->render('_includes/forms/select', array(
      'name'          => $name,
      'id'            => $id,
      'instructions'  => $instructions,
      'required'      => $required,
      'value'         => $value,
      'options'       => $options
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
    return Craft::t('Dropdown Options');
  }
}
