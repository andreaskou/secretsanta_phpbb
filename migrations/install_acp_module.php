<?php
/**
 *
 * Secret X Generator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Andreas Kourtidis
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace andreask\secretx\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['andreask_secretx_goodbye']);
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_data()
	{
		return [
			['config.add', ['andreask_secretx_goodbye', 0]],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_SECRETX_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_SECRETX_TITLE',
				[
					'module_basename'	=> '\andreask\secretx\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
