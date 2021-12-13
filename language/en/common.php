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

	'SECRETX_WELCOME'				=> 'Welcome %s',	
	'YOU_ARE_NOT_A_SECRET_SANTA_YET' 	=> 'Hey, you are not a secret santa yet.<br>But you could be, if you fill your info down bellow!',

	'SECRETX_EVENT'					=> ' :: Secretsanta Event :: ',

	'ACP_SECRETX_SET_ONOFF'			=> 'Is it already time for Secret Santa?',
	'ACP_SECRETX_SETTING_SAVED'		=> 'Settings have been saved successfully!',

	'ANDREASK_SECRETX_NOTIFICATION'	=>	'Secret Santa Generator notification',
	'SECRETX_YOUR_GIFT_WAS_SENT'	=>	'Hi, we would like to inform you that your Secretsanta has sent your gift.',
	'SECRETX_YOU_HAVE_A_WINNER'		=>	'Hi, we would like to inform you that we have paired you with your lucky user',

	'SECRETX_PAGE'					=>	'Secret Santa',
	'VIEWING_ANDREASK_SECRETX'		=>	'Viewing Secret Santa Generator page',
	'SECRETX_INFORMATION'			=>	'Be a good Santa!',
	'SECRETX_ADDRESS'				=>	'This is your address Santa!',
	'SECRETX_ADDRESS_INFO'			=>	'Please add your information',
	'SECRETX_ADDRESS_INFO_EXPLAIN'	=>	'Fill your information the way you would write then on an envelope. Full name, address, postal code, country, telephone etc. i.e.<i></br>My Fullname</br>Thisismystreetaddress 9</br>Thisismylocation 1234AB</br>Thisismycountry</br>123456789</i>',
	'SECRETX_NOT_PAIRED_YET'		=>	'Sorry Santa!</br>But so far there are no kids on the list yes!</br>Pleae be patient and soon there will be someone lucky that you`ll make happy!</br>Don`t worry, we will notify you as soon there is someone paired to you!</br>Of course you are always welcome to check on your own!',
	'SECRETX_RECEPIENT'				=>	'Recepient`s Address',
	'SECRETSANT_MANAGMENT'				=>	'Manage SecretSanta Participants',
	'PARTICIPATING_USERS'				=>	'Amount of SecretSantas',
	'PARTICIPANTS_PAIRED'				=>	[
												'0'	=>	'There are no pairs yet.',
												'1'	=>	'Participants are paired',
											],
	'PAIR_PARTICIPANTS'					=>	'Pair Participants',
	'PARTICIPANTS_PAIR'					=>	'Click on the button to pair the SecretSantas',
	'SECRETX_PARTICIPATION_CHECK'	=>	'By clickiing on this button you verify that you want to be a secret Santa.',
	'SECRETX_PARTICIPATION_VALIDATION'	=> 'Yes, I do!',
	'SECRETX_VALIDATED_PARTICIPATION'	=>	'You have validated your participation!',
	'SECRETX_USERS_RESET'			=>	'Reset process was complete.',
	'NO_PARTICIPANTS_TO_RESET'			=>	'There are no participants to reset.',
	'SECRETX_SENT_GIFT'				=>	'Don`t forget to click on this button once you`ve sent the gift!',
	'SECRETX_GIFT_VALIDATION'		=>	'Ho ho ho! I`ve sent the Gift!',
	'SECRETX_GIFT_VALIDATION_MSG'	=>	'Thank you! We will inform your pair about it!',

	'SECRETX_ADDRESS_EMPTY'			=>	'Address field can`t be empty!<br>Please fill up your info first.',
	'SECRETX_PAIR_CONFIRM'			=>	'Please verify that there is no errors in the pairs and click on "Yes" otherwise click on "No" and try again.%s',
	'THIS_SECRETX'					=>	'SecretSanta %s',
	'SENDS_TO'							=>	'sends to %s',
]);
