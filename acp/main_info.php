<?php
/**
 *
 * Secret Santa Generator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Andreas Kourtidis
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace andreask\secretsanta\acp;

/**
 * Secret Santa Generator ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\andreask\secretsanta\acp\main_module',
			'title'		=> 'ACP_SECRETSANTA_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_SECRETSANTA',
					'auth'	=> 'ext_andreask/secretsanta && acl_a_new_andreask_secretsanta',
					'cat'	=> ['ACP_SECRETSANTA_TITLE'],
				],
			],
		];
	}
}
