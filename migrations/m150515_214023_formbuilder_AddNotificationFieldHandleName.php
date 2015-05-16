<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150515_214023_formbuilder_AddNotificationFieldHandleName extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		craft()->db->createCommand()
      ->addColumn('formbuilder_forms', 'notificationFieldHandleName', 'VARCHAR(255) null');
    return true;
	}
}
