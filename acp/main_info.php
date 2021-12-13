<?php
/**
 *
 * Secret X Generator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Andreas Kourtidis
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace andreask\secretx\acp;

/**
 * Secret Santa Generator ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\andreask\secretx\acp\main_module',
			'title'		=> 'ACP_SECRETX_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_SECRETX',
					'auth'	=> 'ext_andreask/secretx && acl_a_new_andreask_secretx',
					'cat'	=> ['ACP_SECRETX_TITLE'],
				],
			],
		];
	}
}
