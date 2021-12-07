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
 * Secret Santa Generator ACP controller.
 */
class acp_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver_interface */
	protected $db;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string Custom form action */
	protected $u_action;

	protected $phpbb_root_path;
	protected $phpEx;

	protected $secretsanta_organizer;


	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config				$config		Config object
	 * @param \phpbb\db\driver\driver_interface $db	DB Object
	 * @param \phpbb\language\language			$language	Language object
	 * @param \phpbb\log\log					$log		Log object
	 * @param \phpbb\request\request			$request	Request object
	 * @param \phpbb\template\template			$template	Template object
	 * @param \phpbb\user						$user		User object
	 */
	public function __construct(\phpbb\config\config $config,\phpbb\db\driver\driver_interface $db, \phpbb\language\language $language, \phpbb\log\log $log, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, $phpbb_root_path, $phpEx)
	{
		$this->config	= $config;
		$this->db		= $db;
		$this->language	= $language;
		$this->log		= $log;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;
		$this->phpEx	= $phpEx;
		$this->phpbb_root_path	= $phpbb_root_path;
		$this->secretsanta_organizer = $this->get_secretssanta_organizer();
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_options()
	{
		// Add our common language file
		$this->language->add_lang('common', 'andreask/secretsanta');

		// Create a form key for preventing CSRF attacks
		add_form_key('andreask_secretsanta_acp');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('andreask_secretsanta_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$this->config->set('andreask_secretsanta_is_active', $this->request->variable('andreask_secretsanta_is_active', 0));

				// Add option settings change action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_SECRETSANTA_SETTINGS');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->language->lang('ACP_SECRETSANTA_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		if($this->secretsanta_organizer)
		{
			if(count($this->secretsanta_organizer) > 1)
			{
				$errors[] = $this->language->lang('PLEASE_RESET_SECRETSANTA_ORGANIZERS');
			}
			else
			{
				$secretsanta_organizer = array_shift($this->secretsanta_organizer);
				$this->secretsanta_organizer = $secretsanta_organizer['username'];
			}
		}
		elseif(!$this->secretsanta_organizer)
		{
			$this->secretsanta_organizer = 0;
		}

		if ($this->request->is_set_post('secretsanta_username_submit')) 
		{
			if (!check_form_key('andreask_secretsanta_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}
			
			// clean emty lines if exist...
			$user  = trim($this->request->variable('secretsanta_username', ''));

			if ($user == '')
			{
				$errors[] = $this->language->lang('SECRETSANTA_CANNOT_BE_EMPTY');
			}elseif ($this->secretsanta_organizer)
			{
				$errors[] = $this->language->lang('CANNOT_HAVE_MORE_THAN_ONE_SERCRESANTA_ORGANIZERS');
			}

			if ($user)
			{
				$sql = 'SELECT user_id FROM '. USERS_TABLE .' 
						WHERE '. $this->db->sql_in_set('username', $this->db->sql_escape($user));
				$result = $this->db->sql_query($sql);
				$organizer = (int) $this->db->sql_fetchfield('user_id');
				$this->db->sql_freeresult($result);
				
				if (!$organizer)
				{
					$errors[] = $this->language->lang('NO_USER');
				}
			}
			if (!$errors)
			{
				$sql = 'UPDATE '. USERS_TABLE .' SET secretsanta_organizer = 1 
						WHERE '. $this->db->sql_in_set('user_id', $organizer);
				$this->db->sql_query($sql);
				trigger_error($this->language->lang('SECRETSANTA_ORGANIZER_WASSET', $user) . adm_back_link($this->u_action), E_USER_NOTICE);
			}
		}

		if($this->request->is_set_post('secretsanta_username_reset'))
		{
			if(!$this->secretsanta_organizer)
			{
				$errors[] = $this->language->lang('NOTHING_TODO');
			}
			
			if (!$errors)
			{
				$sql = 'UPDATE '. USERS_TABLE .' SET secretsanta_organizer = null
				WHERE secretsanta_organizer = 1';
				// var_dump($sql);
				$query = $this->db->sql_query($sql);
				trigger_error($this->language->lang('SECRETSANTA_ORGANIZER_WASRESET', $this->secretsanta_organizer) . adm_back_link($this->u_action), E_USER_NOTICE);
			}
		}
		
		$s_errors = !empty($errors);

		// Set output variables for display in the template
		$this->template->assign_vars([
			'S_ERROR'		=> $s_errors,
			'ERROR_MSG'		=> $s_errors ? implode('<br />', $errors) : '',

			'U_ACTION'		=> $this->u_action,

			'ANDREASK_SECRETSANTA_IS_ACTIVE'	=> (bool) $this->config['andreask_secretsanta_is_active'],
			'U_FIND_USERNAME'       =>	append_sid("{$this->phpbb_root_path}memberlist.$this->phpEx", 'mode=searchuser&amp;form=andreask_secretsanta_acp&amp;select_single=true&amp;field=secretsanta_username'),
			'SECRETSANTA_ORGANIZER'	=>	$this->language->lang('SECRETSANTA_ORGANIZER', $this->secretsanta_organizer),
		]);
	}

	/**
	 * Set custom form action.
	 *
	 * @param string	$u_action	Custom form action
	 * @return void
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}

	private function get_secretssanta_organizer()
	{
		$sql = 'SELECT username FROM '. USERS_TABLE .' 
				WHERE secretsanta_organizer = 1';
		$query = $this->db->sql_query($sql);
		$result = $this->db->sql_fetchrowset($query);
		// var_dump($result);
		$this->db->sql_freeresult($query);
		
		return $result;	
	}
}
