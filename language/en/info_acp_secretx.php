<?php
/**
 *
 * Secret X Generator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Andreas Kourtidis
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [
	'ACP_SECRETX_TITLE'	=> 'Secret Santa Generator Module',
	'ACP_SECRETX'			=> 'Secret Santa Generator Settings',

	'LOG_ACP_SECRETX_SETTINGS'		=>	'<strong>Secret Santa Generator settings updated</strong>',
	'ACP_SECRETX_SET_ORGANIZER'		=>	'Set a user as an organizer for the secret santa.',
	'SECRETX_SET_ORGANIZER'			=>	'Set Organizer',
	'SECRETX_RESET_ORGANIZER'		=>	'Reset Organizer',
	'SECRETX_ORGANIZER_LEGEND'		=>	'Secret Santa Organizer',
	'SECRETX_ORGANIZER'				=>	[	
												0	=>	'No organizer yet.',
												1	=>	'Current organizer : <strong>%s</strong>',
											],
	'SECRETX_ORGANIZER_WASRESET'	=>	'User <strong>%s</strong> is no more organizing Secretsanta.',
	'SECRETX_ORGANIZER_WASSET'		=>	'User <strong>%s</strong> was set to organize Secretsanta.',
]);
