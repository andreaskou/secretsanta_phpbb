<?php
/**
 *
 * Secret Santa Generator. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2021, Andreas Kourtidis
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace andreask\secretsanta\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Secret Santa Generator Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup'							=> 'load_language_on_setup',
			'core.page_header'							=> 'add_page_header_link',
			'core.viewonline_overwrite_location'		=> 'viewonline_page',
			'core.display_forums_modify_template_vars'	=> 'display_forums_modify_template_vars',
			'core.permissions'							=> 'add_permissions',
		];
	}

	/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\template\template */
	protected $template;

	/** @var string phpEx */
	protected $php_ext;

	/**
	 * Constructor
	 *
	 * @param \phpbb\language\language	$language	Language object
	 * @param \phpbb\controller\helper	$helper		Controller helper object
	 * @param \phpbb\template\template	$template	Template object
	 * @param string                    $php_ext    phpEx
	 */
	public function __construct(\phpbb\language\language $language, \phpbb\config\config $config, \phpbb\controller\helper $helper, \phpbb\template\template $template, $php_ext)
	{
		$this->language = $language;
		$this->config	= $config;
		$this->helper   = $helper;
		$this->template = $template;
		$this->php_ext  = $php_ext;
	}

	/**
	 * Load common language files during user setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function load_language_on_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'andreask/secretsanta',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Add a link to the controller in the forum navbar
	 */
	public function add_page_header_link()
	{
			$this->template->assign_vars([
				'S_SECRETSANTA_IS_ACTIVE' => $this->config['andreask_secretsanta_is_active'],
				'U_SECRETSANTA_PAGE'	=> $this->helper->route('andreask_secretsanta_controller'),
			]);
	}

	/**
	 * Show users viewing Secret Santa Generator page on the Who Is Online page
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function viewonline_page($event)
	{
		if ($event['on_page'][1] === 'app' && strrpos($event['row']['session_page'], 'app.' . $this->php_ext . '/secretsanta') === 0)
		{
			$event['location'] = $this->language->lang('VIEWING_ANDREASK_SECRETSANTA');
			$event['location_url'] = $this->helper->route('andreask_secretsanta_controller', ['name' => 'world']);
		}
	}

	/**
	 * A sample PHP event
	 * Modifies the names of the forums on index
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function display_forums_modify_template_vars($event)
	{
		if ($this->config['andreask_secretsanta_is_active'])
		{
			$forum_row = $event['forum_row'];
			$forum_row['FORUM_NAME'] .= $this->language->lang('SECRETSANTA_EVENT');
			$event['forum_row'] = $forum_row;
		}
	}

	/**
	 * Add permissions to the ACP -> Permissions settings page
	 * This is where permissions are assigned language keys and
	 * categories (where they will appear in the Permissions table):
	 * actions|content|forums|misc|permissions|pm|polls|post
	 * post_actions|posting|profile|settings|topic_actions|user_group
	 *
	 * Developers note: To control access to ACP, MCP and UCP modules, you
	 * must assign your permissions in your module_info.php file. For example,
	 * to allow only users with the a_new_andreask_secretsanta permission
	 * access to your ACP module, you would set this in your acp/main_info.php:
	 *    'auth' => 'ext_andreask/secretsanta && acl_a_new_andreask_secretsanta'
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function add_permissions($event)
	{
		$permissions = $event['permissions'];

		$permissions['a_new_andreask_secretsanta'] = ['lang' => 'ACL_A_NEW_ANDREASK_SECRETSANTA', 'cat' => 'misc'];
		$permissions['m_new_andreask_secretsanta'] = ['lang' => 'ACL_M_NEW_ANDREASK_SECRETSANTA', 'cat' => 'post_actions'];
		$permissions['u_new_andreask_secretsanta'] = ['lang' => 'ACL_U_NEW_ANDREASK_SECRETSANTA', 'cat' => 'post'];

		$event['permissions'] = $permissions;
	}
}
