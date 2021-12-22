<?php
/**
 *
 * Secret Santa Generator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Andreas Kourtidis
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace andreask\secretx\controller;

/**
 * Secret Santa Generator main controller.
 */
class main_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\request */
	protected $request;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\notiification\manager */
	protected $notification_manager;
	/** @vat \phpbb\log\log  */
	protected $log;				/** Log class for logging informatin */


	protected $u_action;

	private $user_id;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config				$config		Config object
	 * @param \phpbb\db\driver\driver_interface	$db	database interface
	 * @param \phpbb\user						$user		user interface
	 * @param \phpbb\request 					$request	request interface
	 * @param \phpbb\controller\helper			$helper		Controller helper object
	 * @param \phpbb\template\template			$template	Template object
	 * @param \phpbb\language\language			$language	Language object
	 * @param \phpbb\notification\manager 		$notification_manager		Notification Manager
	 * @param \phpbb\log\log 					$log 	Phpbb loging system
	 */

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\request\request $request, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\notification\manager $notification_manager, \phpbb\log\log $log)
	{
		$this->config	= $config;
		$this->db		= $db;
		$this->user		= $user;
		$this->request	= $request;
		$this->helper	= $helper;
		$this->template	= $template;
		$this->language	= $language;
		$this->notification_manager = $notification_manager;
		$this->log		= $log;

		$this->user_id = $this->user->data['user_id'];
		$this->u_action	= append_sid(generate_board_url() . '/' . $this->user->page['page']);
	}

	/**
	 * Controller handler for route /demo/{name}
	 *
	 * @param string $name
	 *
	 * @return \Symfony\Component\HttpFoundation\Response A Symfony Response object
	 */
	public function handle()
	{

		// First we check if he is a member of the forum.
		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			trigger_error('NOT_LOGGEDIN', E_USER_WARNING);
		}

		$form_key = 'andreask_secretx';
		add_form_key($form_key);

		$this->template->assign_var('SECRETX_WELCOME_MESSAGE', $this->language->lang('SECRETX_WELCOME',$this->user->data['username']));
		$secret_santas_info = $this->fetch_all();
		$sends_to = $this->get_recepient_address($secret_santas_info['secretx_sends_to']);
		$text = $secret_santas_info['secretx_address'];

		// check if the user is the organizer to give some more info
		if ($secret_santas_info['secretx_organizer'] == 1)
		{
			// Get all the participants
			$secretxs = $this->get_participants();
			$this->template->assign_var('SECRETX_PARICIPATING_AMOUNT', $secretxs['count']);
			
			// Count the ones that filled their addresses
			$part_sends_to = array_column($secretxs['participants'], 'secretx_sends_to');
			$part_sends_to_count = count(array_filter(
				$part_sends_to, function($x)
					{
						return (!is_null($x));
					}
			));
			
			// How many have sent the gifts?
			$sent_gifts = array_column($secretxs['participants'], 'secretx_gift_sent');
			$sent_gifts_count = count($sent_gifts);
			$can_reset_pairs = ($part_sends_to_count == $secretxs['count']);

			$this->template->assign_vars(
				[
					'SECRETX_PARICIPATING_AMOUNT' 		=>	$secretxs['count'],
					'ORGANIZER'							=>	$secret_santas_info['secretx_organizer'],
					'PARTICIPANTS_PAIRED'				=>	$this->language->lang('PARTICIPANTS_PAIRED', (int) $sends_to),
					'PARTICIPANTS_RESET_PAIRED_BOOL'	=>	$can_reset_pairs,
					'PARTICIPANTS_PAIR_ACTION_BOOL'		=>	(bool) ($sends_to)
				]
			);
		}

		$uid = $bitfield = $flags = '';		
		// Saving address of Secretsanta simply always updating 
		if ($this->request->is_set_post('secretx_address_info'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . $this->usr_back_link(), E_USER_WARNING);
			}
			
			if ($this->request->variable('secretx_address_info', '') =='')
			{
				trigger_error($this->language->lang('SECRETX_ADDRESS_EMPTY') . $this->usr_back_link(), E_USER_WARNING);
			}

			$text = $this->request->variable('secretx_address_info', '');
			$save = generate_text_for_storage($text, $uid, $bitfield, $flags, false, false, false); //inout formated
			
			$this->save_secretx_address($text);
		}
		
		// User is validating his participation
		if ($this->request->is_set_post('secretx_part_validation', ''))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . $this->usr_back_link(), E_USER_ERROR);
			}
			if (empty($secret_santas_info))
			{
				trigger_error($this->language->lang('NO_VALID_ADDRESS') . $this->usr_back_link(), E_USER_ERROR);
			}
			$this->validate_action('secretx_participating');
			trigger_error($this->language->lang('SECRETX_VALIDATED_PARTICIPATION') . $this->usr_back_link(), E_USER_NOTICE);
		}
		
		// User is verifying that he sent his gift
		if ($this->request->is_set_post('secretx_gift_validation', ''))
		{
			if (!check_form_key($form_key))
			{
			trigger_error($this->language->lang('FORM_INVALID') . $this->usr_back_link(), E_USER_ERROR);
		}
		
		$this->validate_action('secretx_gift_sent');
		$this->notify('gift', $secret_santas_info['secretx_sends_to']);
		trigger_error($this->language->lang('SECRETX_GIFT_VALIDATION_MSG') . $this->usr_back_link(), E_USER_NOTICE);
		// $trigger_error($this->language->lang('SECRETX_GIFT_VALIDATION_MSG') . $this->usr_back_link(), E_USER_NOTICE);
		}

		// Reseting participants or pairs depending on request only if user is organizer
		if (($this->request->is_set('secretx_reset') || $this->request->is_set('secretx_reset_pair')) && $secret_santas_info['secretx_organizer'])
		{

			if (!check_form_key($form_key))
			{
			trigger_error($this->language->lang('FORM_INVALID') . $this->usr_back_link(), E_USER_WARNING);
			}
			
			$requested = '';
			$user_ids = array_column($secretxs['participants'], 'user_id');
			if ($this->request->is_set('secretx_reset'))
			{
				// reset participants
				if ($secretxs['count'])
				{
					$requested = 'secretx_reset';
				}
				else
				{
					$errors[] = $this->language->lang('NO_PARTICIPANTS_TO_RESET');
				}
			}
			
			if ($this->request->is_set('secretx_reset_pair'))
			{
				// reset pairs
				$requested = 'secretx_reset_pair';
			}
			if(empty($errors))
			{
				$this->reset_function($requested, $user_ids);
				trigger_error($this->language->lang('SECRETX_USERS_RESET') . $this->usr_back_link(), E_USER_NOTICE);	
			}
		}

		// Pairing the new participants only if user is organizer
		if ($this->request->is_set_post('secretx_pair') && $secret_santas_info['secretx_organizer'])
		{
			// Pair Participants
			$pairs = $this->pair_secretxs($secretxs['participants']);
				
			$this->template->assign_vars([
				'SHOW_SECRETX_LIST'	=>	true,
				'SECRETX_LIST'		=>	$pairs,
				// 'SECRETX_LIST'		=>	$this->format_pairs($pairs),
			]);
			
			$this->save_pairs_and_notify($pairs);
			trigger_error($this->language->lang('SECRETX_PAIR_CONFIRM', $this->format_pairs($pairs)) . $this->usr_back_link(), E_USER_NOTICE);

		}

		if ($text != '')
		{
			$secretx_address	= generate_text_for_display($text, $this->user_id, $bitfield, $flags); //String
		}
		if ($sends_to['secretx_address'] != '')
		{
			$secretx_sends_to	= generate_text_for_display($sends_to['secretx_address'], $this->user_id, $bitfield, $flags);
		}

		$this->template->assign_vars(
			[
				'IS_SECRET_SANTA'				=>	$secret_santas_info['secretx_address'],
				'PARTICIPATING'					=>	$secret_santas_info['secretx_participating'],
				'SECRETX_SENDS_TO'			=>	$secretx_sends_to,
				'SECRETX_ADDRESS_PREVIEW'	=>	$secretx_address,
				'SECRETX_PARTICIPATING'		=>	$secret_santas_info['secretx_participating'],
				'U_ACTION'						=>	$this->u_action,
			]
		);

		return $this->helper->render('@andreask_secretx/secretx_body.html');
	}

	/**
	 * Save current user address
	 *
	 * @param [string] $text formated text
	 * @return void
	 */
	private function save_secretx_address($text)
	{
		$sql_array =
		[
			'secretx_address'	=>	$text,
		];

		$sql ='UPDATE '. USERS_TABLE . '
				SET ' . $this->db->sql_build_array('UPDATE', $sql_array) .'
				WHERE user_id =' . (int) $this->user_id;
		$result = $this->db->sql_query($sql);
	}

	/**
	 * Fetch all the information of the user.
	 *
	 * @return array
	 */
	private function fetch_all()
	{
		$sql = 'SELECT secretx_address, secretx_participating, secretx_sends_to, secretx_gift_sent, secretx_organizer
				FROM '. USERS_TABLE . '
				WHERE ' . $this->db->sql_build_array('SELECT', ['user_id' => $this->user_id]);
		$result = $this->db->sql_query($sql);
		$secretx = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $secretx;
	}

	/**
	 * Gets the address of current user
	 *
	 * @param [array] $recepient user_id
	 * @return string
	 */
	private function get_recepient_address($recepient)
	{
		$sql_array = ['user_id'	=> $recepient,];

		$sql = 'SELECT secretx_address FROM '. USERS_TABLE .'
		 		WHERE ' .$this->db->sql_build_array('SELECT', $sql_array);
		$result = $this->db->sql_query($sql);
		$recepient_address = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $recepient_address;
	}

	/**
	 * Retrievs all participants with their info and their tottal amount
	 *
	 * @return [array]
	 */
	private function get_participants()
	{
		$sql = 'SELECT user_id, secretx_sends_to, secretx_address, secretx_participating FROM ' . USERS_TABLE . ' 
					WHERE secretx_participating = 1';
			$query = $this->db->sql_query($sql);
			$result = $this->db->sql_fetchrowset($query);
			$count = count($result);
			$this->db->sql_freeresult($query);
			return ['count' => $count, 'participants' => $result];
	}

	/**
	 * Sql update on validation cells, depending on the request.
	 *
	 * @param [string] $action preset string
	 * @return void
	 */
	private function validate_action($action)
	{
		$sql = 'UPDATE ' . USERS_TABLE . " SET $action = 1 
		WHERE user_id = $this->user_id";
		
		$result = $this->db->sql_query($sql);
	}

	/**
	 * Returns a formated text with a back link
	 *
	 * @return string
	 */
	private function usr_back_link()
	{
		return '<br /><br /><a href="' . $this->u_action . '">&laquo; ' . $this->language->lang('BACK_TO_PREV') . '</a>';
	}

	/**
	 * Reset users information of the ext such as participation validation or gift sent validation
	 *
	 * @param [string] $action preset string to identify which action has to execute
	 * @param [array] $users Array of user_id(s) to reset.
	 * @return void
	 */
	private function reset_function($action, $users)
	{
		switch ($action)
		{
			case 'secretx_reset':
				$sql_array = ['secretx_participating' => null,];

				$sql = 'UPDATE '. USERS_TABLE . ' SET '. $this->db->sql_build_array('UPDATE', $sql_array) .' 
						WHERE '. $this->db->sql_in_set('user_id', $users);
						$result = $this->db->sql_query($sql);
						$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_SECRETX_PARTICIPANTS_RESET', time());
				break;
						
				case 'secretx_reset_pair':
					$sql_array = ['secretx_sends_to' => null,];
					$sql = 'UPDATE '. USERS_TABLE . ' SET '. $this->db->sql_build_array('UPDATE', $sql_array) .' 
					WHERE '. $this->db->sql_in_set('user_id', $users);				
					$result = $this->db->sql_query($sql);
					$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_SECRETX_PAIRS_RESET', time());
				break;
				
				default:
				break;
		}
	}

	/**
	 * Pairs the users so they are able to see the address of the luck user who will receive the gift
	 *
	 * @param [array] $ids array of user_id(s)
	 * @return [array] of paired users
	 */

	private function pair_secretxs($ids)
	{
		// Initializing pairs array
		$pairs = [];
		// We need a key to walki through the array
		$key = 0;
		// Make a copy of ids to grab and delete rows freely.
		$temp_ids = $ids;
		
		// Walk through the array and create pairs
		while($key != sizeof($ids))
		{
			// Pick a luck user.
			$random_pick = array_rand($temp_ids, 1);
			
			// The lucky user cannot be also the sender of the gift.
			if ($temp_ids[$random_pick]['user_id'] != $ids[$key]['user_id'])
			{
				// Store the secretx id
				$pairs[$key]['user_id'] = $ids[$key]['user_id'];
				// Store the luck user id
				$pairs[$key]['secretx_sends_to'] = $temp_ids[$random_pick]['user_id'];
				
				// remove the lucky user so he is not picked up again.
				unset($temp_ids[$random_pick]);

				// Next key
				$key++;
			}
			// Repeat till $key == amount of array rows.
		}

		// return array
		return $pairs;
	}

	private function format_pairs($pairs)
	{
		$text = '<p>';
		foreach ($pairs as $pair)
		{
			$text .= "{$this->language->lang('THIS_SECRETX', (int) $pair['user_id'])} {$this->language->lang('SENDS_TO', (int) $pair['secretx_sends_to'])}<br>";
		}
		$text .= '</p>';
		return $text;
	}

	private function save_pairs_and_notify($pairs)
	{
		$notify = [];
		foreach($pairs as $pair)
		{
			$update_array = ['secretx_sends_to' => (int) $pair['secretx_sends_to']];
			$sql = 'UPDATE '. USERS_TABLE .' SET ' . $this->db->sql_build_array('UPDATE', $update_array) .' WHERE user_id = '. (int) $pair['user_id'];
			// var_dump($sql);
			// die();
			$result = $this->db->sql_query($sql);
			$this->notify('pair', $pair['user_id']);
		}
		$this->log->add('user', $this->user->data['user_id'], $this->user->ip, 'LOG_SECRETX_PAIRED_USERS', time());
	}

	private function notify($type, $user)
	{
		switch ($type)
		{
			case 'pair':
				$subject = $this->language->lang('SECRETX_YOU_HAVE_A_WINNER');
				break;
			case 'gift':
				$subject = $this->language->lang('SECRETX_YOUR_GIFT_WAS_SENT');
				break;
			case 'default':
				break;
		}

		$this->config->increment('andreask_secretx_notification_id', 1);
		$notification_array = [
			'item_id'			=>	$this->config['andreask_secretx_notification_id'],
			'user_id'			=>	$user,
			'sender_id'			=>	(int) $this->user->data['user_id'],
			'subject'			=>	$subject,
		];

		$this->notification_manager->add_notifications('andreask.secretx.notification.type.inform_organizer', $notification_array);
	}
}
