<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150415_172028_formbuilder_AddNotificationTemplate extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
    craft()->db->createCommand()
      ->addColumn('formbuilder_forms', 'notificationTemplatePathRegistrant', 'VARCHAR(255) null');
    return true;
	}
}
