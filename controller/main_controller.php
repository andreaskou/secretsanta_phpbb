<?php
/**
 *
 * Secret Santa Generator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Andreas Kourtidis
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace andreask\secretsanta\controller;

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

	protected $u_action;

	private $user_id;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config		$config		Config object
	 * @param \phpbb\db\driver\driver_interface	$db	database interface
	 * @param \phpbb\user				$user		user interface
	 * @param \phpbb\request 			$request	request interface
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\language\language	$language	Language object
	 */

	public function __construct(\phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\request\request $request, \phpbb\controller\helper $helper, \phpbb\template\template $template, \phpbb\language\language $language, \phpbb\notification\manager $notification)
	{
		$this->config	= $config;
		$this->db		= $db;
		$this->user		= $user;
		$this->request	= $request;
		$this->helper	= $helper;
		$this->template	= $template;
		$this->language	= $language;
		$this->user_id = $this->user->data['user_id'];
		$this->u_action	= append_sid(generate_board_url() . '/' . $this->user->page['page']);
		// NOTE: u_action does not have to be this way
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

		$form_key = 'andreask_secretsanta';
		add_form_key($form_key);
		// $l_message = !$this->config['andreask_secretsanta_goodbye'] ? 'SECRETSANTA_HELLO' : 'SECRETSANTA_GOODBYE';
		$this->template->assign_var('SECRETSANTA_MESSAGE', $this->user->data['username']);
		$secret_santas_info = $this->fetch_all();
		$sends_to = $this->get_recepient_address($secret_santas_info['secretsanta_sends_to']);
		$text = $secret_santas_info['secretsanta_address'];

		// check if the user is the organizer to give some more info
		if ($secret_santas_info['secretsanta_organizer'] == 1)
		{
			$secretsantas = $this->get_participants();
			$this->template->assign_var('SECRETSANTA_PARICIPATING_AMOUNT', $secretsantas['count']);
			
			$part_sends_to = array_column($secretsantas['participants'], 'secretsanta_address');
			// $part_sends_to_count = count($participants);
			
			$sent_gifts = array_column($secretsantas['participants'], 'secretsanta_gift_sent');
			$sent_gifts_count = count($sent_gifts);

			$switch_for_pairing = ( $part_sends_to_count == $participants['count'] ) ? 0 : 1;

			$this->template->assign_vars(
				[
					'SECRETSANTA_PARICIPATING_AMOUNT' 	=>	$secretsantas['count'],
					'ORGANIZER'							=>	$secret_santas_info['secretsanta_organizer'],
					'PARTICIPANTS_PAIRED'				=>	$this->language->lang('PARTICIPANTS_PAIRED', (int) $sends_to),
					'PARTICIPANTS_PAIRED_BOOL'			=>	( $participants['count'] <= 3 ),
				]
			);
		}
		
		// Saving address of Secretsanta simply always updating 
		if ($this->request->is_set_post('secretsanta_address_info'))
		{
			if (!check_form_key($form_key))
			{
				trigger_error($this->language->lang('FORM_INVALID') . $this->usr_back_link(), E_USER_WARNING);
			}
			
			if ($this->request->variable('secretsanta_address_info', '') =='')
			{
				trigger_error($this->language->lang('SECRETSANTA_ADDRESS_EMPTY') . $this->usr_back_link(), E_USER_WARNING);
			}

			$uid = $bitfield = $flags = '';
			$text = $this->request->variable('secretsanta_address_info', '');
			$save = generate_text_for_storage($text, $uid, $bitfield, $flags, false, false, false); //inout formated
			$this->save_secretsanta_address($text);
		}
		
		// User is validating his participation
		if ($this->request->is_set_post('secretsanta_part_validation', ''))
		{
			// maybe use confirm_box()?
			if (!check_form_key($form_key))
			{
			trigger_error($this->language->lang('FORM_INVALID') . $this->usr_back_link(), E_USER_ERROR);
			}
			$this->validate_action('secretsanta_participating');
		}
		
		// User is verifying that he sent his gift
		if ($this->request->is_set_post('secretsanta_gift_validation', ''))
		{
			// maybe use confirm_box()?
			if (!check_form_key($form_key))
			{
			trigger_error($this->language->lang('FORM_INVALID') . $this->usr_back_link(), E_USER_ERROR);
			}
			$this->validate_action('secretsanta_gift_sent');
		}

		// Reseting participants or pairs depending on request only if user is organizer
		if (($this->request->is_set('secretsanta_reset') || $this->request->is_set('secretsanta_reset_pair')) && $secret_santas_info['secretsanta_organizer'])
		{

			if (!check_form_key($form_key))
			{
			trigger_error($this->language->lang('FORM_INVALID') . $this->usr_back_link(), E_USER_WARNING);
			}
			
			$requested = '';
			$user_ids = array_column($secretsantas['participants'], 'user_id');
			if ($this->request->is_set('secretsanta_reset'))
			{
				// reset participants
				if ($secretsantas['count'])
				{
					$requested = 'secretsanta_reset';
				}
				else
				{
					$errors[] = $this->language->lang('NO_PARTICIPANTS_TO_RESET');
				}
			}
			
			if ($this->request->is_set('secretsanta_reset_pair'))
			{
				// reset pairs
				$requested = 'secretsanta_reset_pair';
			}
			if(empty($errors))
			{
				$this->reset_function($requested, $user_ids);
				trigger_error($this->language->lang('SECRETSANTA_USERS_RESET') . $this->usr_back_link(), E_USER_NOTICE);	
			}
		}

		// Pairing the new participants only if user is organizer
		if ($this->request->is_set_post('secretsanta_pair') && $secret_santas_info['secretsanta_organizer'])
		{

			// Pair Participants
			$pairs = $this->pair_secretsantas($secretsantas['participants']);
			
			if ($pairs)
			{
				$go_nogo = confirm_box(false, $this->language->lang('SECRETSANTA_PAIR_CONFIRM'), $this->format_pairs($pairs));
			}

			if ($go_nogo)
			{
				echo "Confirmed!";
			}
			else
			{
				echo "Fuck!";
			}
		}


		$secretsanta_address	= generate_text_for_display($text, $this->user_id, $bitfield, $flags); //String
		$secretsanta_sends_to	= generate_text_for_display($sends_to['secretsanta_address'], $this->user_id, $bitfield, $flags);
		// $secretsanta_sends_to	= null;

		$this->template->assign_vars(
			[
				'IS_SECRET_SANTA'				=>	$secret_santas_info['secretsanta_address'],
				'PARTICIPATING'					=>	$secret_santas_info['secretsanta_participating'],
				'SECRETSANTA_SENDS_TO'			=>	$secretsanta_sends_to,
				// 'SECRETSANTA_MESSAGE'			=>	$this->language->lang($l_message, $name),
				'SECRETSANTA_ADDRESS_PREVIEW'	=>	$secretsanta_address,
				'SECRETSANTA_PARTICIPATING'		=>	$secret_santas_info['secretsanta_participating'],
				'U_ACTION'						=>	$this->u_action,
			]
		);

		return $this->helper->render('@andreask_secretsanta/secretsanta_body.html', $name);
	}

	/**
	 * Save current user address
	 *
	 * @param [string] $text formated text
	 * @return void
	 */
	private function save_secretsanta_address($text)
	{
		$sql_array =
		[
			'secretsanta_address'	=>	$text,
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
		$sql = 'SELECT secretsanta_address, secretsanta_participating, secretsanta_sends_to, secretsanta_gift_sent, secretsanta_organizer
				FROM '. USERS_TABLE . '
				WHERE ' . $this->db->sql_build_array('SELECT', ['user_id' => $this->user_id]);
		$result = $this->db->sql_query($sql);
		$secretsanta = $this->db->sql_fetchrow($result);
		$this->db->sql_freeresult($result);

		return $secretsanta;
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

		$sql = 'SELECT secretsanta_address FROM '. USERS_TABLE .'
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
		$sql = 'SELECT user_id, secretsanta_sends_to, secretsanta_address, secretsanta_participating FROM ' . USERS_TABLE . ' 
					WHERE secretsanta_participating = 1';
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
		trigger_error($this->language->lang('SECRETSANTA_VALIDATED_PARTICIPATION') . $this->usr_back_link(), E_USER_NOTICE);
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
		echo "Reset";
		die();
		switch ($action)
		{
			case 'secretsanta_reset':
				$sql_array = ['secretsanta_participating' => null,];

				$sql = 'UPDATE '. USERS_TABLE . ' SET '. $this->db->sql_build_array('UPDATE', $sql_array) .' 
						WHERE '. $this->db->sql_in_set('user_id', $users);
				
						$result = $this->db->sql_query($sql);
				break;
						
				case 'secretsanta_reset_pair':
					$sql_array = ['secretsanta_sends_to' => null,];
					$sql = 'UPDATE '. USERS_TABLE . ' SET '. $this->db->sql_build_array('UPDATE', $sql_array) .' 
					WHERE '. $this->db->sql_in_set('user_id', $users);
				
					$result = $this->db->sql_query($sql);
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

	private function pair_secretsantas($ids)
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
				// Store the secretsanta id
				$pairs[$key]['user_id'] = $ids[$key]['user_id'];
				// Store the luck user id
				$pairs[$key]['secretsanta_sends_to'] = $temp_ids[$random_pick]['user_id'];
				
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
			$text .= "{$this->language->lang('THIS_SECRETSANTA', (int) $pair['user_id'])} {$this->language->lang('SENDS_TO', (int) $pair['secretsanta_sends_to'])}<br>";
		}
		$text .= '</p>';
		return $text;
	}
}
