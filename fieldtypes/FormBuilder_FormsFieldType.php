<?php
namespace Craft;
// http://buildwithcraft.com/docs/plugins/field-types

class FormBuilder_FormsFieldType extends BaseOptionsFieldType
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
		return Craft::t('Submitter Email Field');
	}

	/**
   * Get this fieldtype's column type.
   *
   * @return mixed
   */
  public function defineContentAttribute()
  {
    // "Mixed" represents a "text" column type, which can be used to store arrays etc.
    return AttributeType::Mixed;
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
		$oldPath = craft()->path->getTemplatesPath();
		$newPath = craft()->path->getPluginsPath().'formBuilder/templates';
		craft()->path->setTemplatesPath($newPath);
		$html = craft()->templates->render('/fields/notifieremail', array(
      'name'  => 'yourEmail',
      'value' => 'yourEmail'
    ));
		craft()->path->setTemplatesPath($oldPath);

		return $html;
	}

	protected function defineSettings()
  {
    return array(
      'test' => array(AttributeType::Mixed),
    );
  }

	/**
	 * @inheritDoc BaseElementFieldType::getSettingsHtml()
	 *
	 * @return string|null
	 */
	public function getSettingsHtml()
	{
		// return craft()->templates->render('/fields/settings', array(
  //     'settings' => $this->getSettings()
  //   ));
		return false;
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
		return Craft::t('Field Settings');
	}
}
