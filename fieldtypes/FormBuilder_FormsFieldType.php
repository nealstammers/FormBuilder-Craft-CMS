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
   * @inheritDoc IFieldType::defineContentAttribute()
   *
   * @return mixed
   */
  public function defineContentAttribute()
  {
    $maxLength = $this->getSettings()->maxLength;

    if (!$maxLength)
    {
      $columnType = ColumnType::Text;
    }
    // TODO: MySQL specific
    else if ($maxLength <= 255)
    {
      $columnType = ColumnType::Varchar;
    }
    else if ($maxLength <= 65535)
    {
      $columnType = ColumnType::Text;
    }
    else if ($maxLength <= 16777215)
    {
      $columnType = ColumnType::MediumText;
    }
    else
    {
      $columnType = ColumnType::LongText;
    }

    return array(AttributeType::String, 'column' => $columnType, 'maxLength' => $maxLength);
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
      'value' => 'yourEmail',
      'settings' => $this->getSettings()
    ));
		craft()->path->setTemplatesPath($oldPath);

		return $html;
	}


  /**
   * @inheritDoc ISavableComponentType::getSettingsHtml()
   *
   * @return string|null
   */
  public function getSettingsHtml()
  {
    return craft()->templates->render('_components/fieldtypes/PlainText/settings', array(
      'settings' => $this->getSettings()
    ));;
  }


  /**
   * @inheritDoc BaseSavableComponentType::defineSettings()
   *
   * @return array
   */
	protected function defineSettings()
  {
    return array(
      'placeholder'   => array(AttributeType::String),
      'multiline'     => array(AttributeType::Bool),
      'initialRows'   => array(AttributeType::Number, 'min' => 1, 'default' => 4),
      'maxLength'     => array(AttributeType::Number, 'min' => 0),
    );
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
