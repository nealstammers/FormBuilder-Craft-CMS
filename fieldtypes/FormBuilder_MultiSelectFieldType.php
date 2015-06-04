<?php
namespace Craft;

/**
 * Class MultiSelectFieldType
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2014, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @see       http://buildwithcraft.com
 * @package   craft.app.fieldtypes
 * @since     1.0
 */
class FormBuilder_MultiSelectFieldType extends BaseOptionsFieldType
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
    return Craft::t('| FormBuilder | Multi-select');
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

    // Namespace our field id
    $id = craft()->templates->namespaceInputId($fieldId, 'field'); 

    // If this is a new entry, look for any default options
    if ($this->isFresh()) {
      $values = $this->getDefaultValue();
    }

    craft()->path->setTemplatesPath(craft()->path->getPluginsPath().'formBuilder/templates');
    $html = craft()->templates->render('_includes/forms/multiselect', array(
      'id'            => $id,
      'name'          => $name,
      'instructions'  => $instructions,
      'required'      => $required,
      'values'        => $values,
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
    return Craft::t('Multi-select Options');
  }
}
