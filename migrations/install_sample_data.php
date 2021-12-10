<?php
/**
 *
 * Secret Santa Generator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Andreas Kourtidis
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace andreask\secretsanta\migrations;

class install_sample_data extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return $this->config->offsetExists('andreask_secretsanta_sample_int');
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	/**
	 * Add, update or delete data stored in the database during extension installation.
	 *
	 * https://area51.phpbb.com/docs/dev/3.2.x/migrations/data_changes.html
	 *  config.add: Add config data.
	 *  config.update: Update config data.
	 *  config.remove: Remove config.
	 *  config_text.add: Add config_text data.
	 *  config_text.update: Update config_text data.
	 *  config_text.remove: Remove config_text.
	 *  module.add: Add a new CP module.
	 *  module.remove: Remove a CP module.
	 *  permission.add: Add a new permission.
	 *  permission.remove: Remove a permission.
	 *  permission.role_add: Add a new permission role.
	 *  permission.role_update: Update a permission role.
	 *  permission.role_remove: Remove a permission role.
	 *  permission.permission_set: Set a permission to Yes or Never.
	 *  permission.permission_unset: Set a permission to No.
	 *  custom: Run a callable function to perform more complex operations.
	 *
	 * @return array Array of data update instructions
	 */
	public function update_data()
	{
		return [
			// Add new config table settings
			['config.add', ['andreask_secretsanta_is_active', 0]],
			['config.add', ['andreask_secretsanta_active_year', '']],
			['config.add', ['andreask_secretsanta_notification_id', 0]],

			// Add a new config_text table setting
			// ['config_text.add', ['andreask_secretsanta_sample', '']],

			// Add new permissions
			['permission.add', ['a_new_andreask_secretsanta']], // New admin permission
			['permission.add', ['m_new_andreask_secretsanta']], // New moderator permission
			['permission.add', ['u_new_andreask_secretsanta']], // New user permission

			// ['permission.add', ['a_copy', true, 'a_existing']], // New admin permission a_copy, copies permission settings from a_existing

			// Set our new permissions
			['permission.permission_set', ['ROLE_ADMIN_FULL', 'a_new_andreask_secretsanta']], // Give ROLE_ADMIN_FULL a_new_andreask_secretsanta permission
			['permission.permission_set', ['ROLE_MOD_FULL', 'm_new_andreask_secretsanta']], // Give ROLE_MOD_FULL m_new_andreask_secretsanta permission
			['permission.permission_set', ['ROLE_USER_FULL', 'u_new_andreask_secretsanta']], // Give ROLE_USER_FULL u_new_andreask_secretsanta permission
			['permission.permission_set', ['ROLE_USER_STANDARD', 'u_new_andreask_secretsanta']], // Give ROLE_USER_STANDARD u_new_andreask_secretsanta permission
			['permission.permission_set', ['REGISTERED', 'u_new_andreask_secretsanta', 'group']], // Give REGISTERED group u_new_andreask_secretsanta permission
			['permission.permission_set', ['REGISTERED_COPPA', 'u_new_andreask_secretsanta', 'group', false]], // Set u_new_andreask_secretsanta to never for REGISTERED_COPPA

			// Add new permission roles
			['permission.role_add', ['secretsanta admin role', 'a_', 'a new role for admins']], // New role "secretsanta admin role"
			['permission.role_add', ['secretsanta moderator role', 'm_', 'a new role for moderators']], // New role "secretsanta moderator role"
			['permission.role_add', ['secretsanta user role', 'u_', 'a new role for users']], // New role "secretsanta user role"

			// Call a custom callable function to perform more complex operations.
			// ['custom', [[$this, 'sample_callable_install']]],
		];
	}

	/**
	 * Add, update or delete data stored in the database during extension un-installation (purge step).
	 *
	 * IMPORTANT: Under normal circumstances, the changes performed in update_data will
	 * automatically be reverted during un-installation. This revert_data method is optional
	 * and only needs to be used to perform custom un-installation changes, such as to revert
	 * changes made by custom functions called in update_data.
	 *
	 * https://area51.phpbb.com/docs/dev/3.2.x/migrations/data_changes.html
	 *  config.add: Add config data.
	 *  config.update: Update config data.
	 *  config.remove: Remove config.
	 *  config_text.add: Add config_text data.
	 *  config_text.update: Update config_text data.
	 *  config_text.remove: Remove config_text.
	 *  module.add: Add a new CP module.
	 *  module.remove: Remove a CP module.
	 *  permission.add: Add a new permission.
	 *  permission.remove: Remove a permission.
	 *  permission.role_add: Add a new permission role.
	 *  permission.role_update: Update a permission role.
	 *  permission.role_remove: Remove a permission role.
	 *  permission.permission_set: Set a permission to Yes or Never.
	 *  permission.permission_unset: Set a permission to No.
	 *  custom: Run a callable function to perform more complex operations.
	 *
	 * @return array Array of data update instructions
	 */
	// public function revert_data()
	// {
	// 	return [
	// 		['custom', [[$this, 'sample_callable_uninstall']]],
	// 	];
	// }

	/**
	 * A custom function for making more complex database changes
	 * during extension installation. Must be declared as public.
	 */
	// public function sample_callable_install()
	// {
	// 	// Run some SQL queries on the database
	// }

	/**
	 * A custom function for making more complex database changes
	 * during extension un-installation. Must be declared as public.
	 */
	// public function sample_callable_uninstall()
	// {
	// 	// Run some SQL queries on the database
	// }
}
