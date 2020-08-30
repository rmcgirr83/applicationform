<?php
/**
*
* Application Form extension for the phpBB Forum Software package.
*
* @copyright (c) 2020 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\applicationform\controller;

use phpbb\config\config;
use phpbb\config\db_text as config_text;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

/**
* Admin controller
*/
class admin_controller implements admin_interface
{
	/** @var config */
	protected $config;

	/** @var db_text */
	protected $config_text;

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

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/** @var string Custom form action */
	protected $u_action;

	/**
	* Constructor
	*
	* @param \phpbb\config\config					$config				Config object
	* @param \phpbb\config\db_text 					$config_text		Config text object
	* @param \phpbb\db\driver\driver_interface		$db					Database object
	* @param \phpbb\language\language				$language			Language object
	* @param \phpbb\log\log							$log				Log object
	* @param \phpbb\request\request					$request			Request object
	* @param \phpbb\template\template				$template			Template object
	* @param \phpbb\user							$user				User object
	* @param string									$root_path			phpBB root path
	* @param string									$php_ext			phpEx
	* @access public
	*/
	public function __construct(
			config $config,
			config_text $config_text,
			driver_interface $db,
			language $language,
			log $log,
			request $request,
			template $template,
			user $user,
			$root_path,
			$php_ext)
	{
		$this->config = $config;
		$this->config_text = $config_text;
		$this->db = $db;
		$this->language = $language;
		$this->log = $log;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
	}

	/**
	 * Display the form
	 *
	 * @access public
	 */
	public function display_options()
	{
		$this->language->add_lang('posting');
		$this->language->add_lang('acp_applicationform', 'rmcgirr83/applicationform');

		$appform_data			= $this->config_text->get_array([
			'appform_questions',
			'appform_positions',
			'appform_info',
			'appform_info_uid',
			'appform_info_bitfield',
			'appform_info_flags',
		]);

		$appform_questions = $appform_data['appform_questions'];
		$appform_positions = $appform_data['appform_positions'];
		$appform_info		= $appform_data['appform_info'];
		$appform_info_uid	= $appform_data['appform_info_uid'];
		$appform_info_bitfield	= $appform_data['appform_info_bitfield'];
		$appform_info_flags		= $appform_data['appform_info_flags'];

		$appform_info = $this->request->variable('appform_info', $appform_info, true);

		add_form_key('appform');

		$error = [];

		if ($this->request->is_set_post('submit') || $this->request->is_set_post('preview'))
		{
			// Test if form key is valid
			if (!check_form_key('appform'))
			{
				$error[] = $this->language->lang('FORM_INVALID');
			}

			$poll_options = $this->request->variable('appform_poll_options', '', true);
			$poll_title = $this->request->variable('appform_poll_title', '', true);
			if (!empty($poll_options) || !empty($poll_title))
			{
				if (!class_exists('parse_message'))
				{
					include($this->root_path . 'includes/message_parser.' . $this->php_ext);
				}
				$message_parser = new \parse_message();
				$poll_max_options = $this->request->variable('appform_poll_max_options', 0);

				$poll = array(
					'poll_title'		=> $poll_title,
					'poll_length'		=> 0,
					'poll_max_options'	=> $poll_max_options,
					'poll_option_text'	=> $poll_options,
					'poll_start'		=> time(),
					'poll_last_vote'	=> 0,
					'poll_vote_change'	=> true,
					'enable_bbcode'		=> true,
					'enable_urls'		=> true,
					'enable_smilies'	=> true,
					'img_status'		=> true,
				);
				$message_parser->parse_poll($poll);

				if (sizeof($message_parser->warn_msg))
				{
					$error[] = implode('<br />', $message_parser->warn_msg);
				}
			}
			if ($this->request->variable('appform_positions', '', true) === '')
			{
				$error[] = $this->language->lang('APPFORM_MUST_HAVE_POSITIONS');
			}

			$appform_questions = $this->request->variable('appform_questions', '', true);
			$appform_positions = $this->request->variable('appform_positions', '', true);

			generate_text_for_storage(
				$appform_info,
				$appform_info_uid,
				$appform_info_bitfield,
				$appform_info_flags,
				!$this->request->variable('disable_bbcode', false),
				!$this->request->variable('disable_magic_url', false),
				!$this->request->variable('disable_smilies', false)
			);

			// Set the options the user configured
			if (!sizeof($error) && $this->request->is_set_post('submit'))
			{

				$this->config_text->set_array([
					'appform_questions'		=> $appform_questions,
					'appform_positions'		=> $appform_positions,
					'appform_info'			=> $appform_info,
					'appform_info_uid'		=> $appform_info_uid,
					'appform_info_bitfield'	=> $appform_info_bitfield,
					'appform_info_flags'	=> $appform_info_flags,
				]);
				$this->set_options();

				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_APPFORM_CONFIG_SAVED');

				trigger_error($this->language->lang('APPFORM_SETTINGS_SUCCESS') . adm_back_link($this->u_action));
			}
		}

		$appform_info_preview = '';
		if ($this->request->is_set_post('preview'))
		{
			generate_text_for_storage(
				$appform_info_preview,
				$appform_info_uid,
				$appform_info_bitfield,
				$appform_info_flags,
				!$this->request->variable('disable_bbcode', false),
				!$this->request->variable('disable_magic_url', false),
				!$this->request->variable('disable_smilies', false)
			);
			$appform_info_preview = generate_text_for_display($appform_info, $appform_info_uid, $appform_info_bitfield, $appform_info_flags);
		}

		$appform_edit = generate_text_for_edit($appform_info, $appform_info_uid, $appform_info_flags);

		$this->template->assign_vars([
			'ERROR'				=> isset($error) ? ((sizeof($error)) ? implode('<br />', $error) : '') : '',

			'APPFORM_INFO'			=> $appform_edit['text'],
			'APPFORM_INFO_PREVIEW'	=> $appform_info_preview,

			'APPFORM_FORUM_ID'	=> $this->appform_forum_select($this->request->variable('appform_forum_id', $this->config['appform_forum_id'])),
			'APPFORM_GUEST'		=> $this->request->variable('appform_guest', $this->config['appform_guest'], true),
			'APPFORM_NRU'		=> $this->request->variable('appform_nru', $this->config['appform_nru']),
			'APPFORM_ATTACHMENT' => $this->request->variable('appform_attach', $this->config['appform_attach']),
			'APPFORM_ATTACHMENT_REQ' => $this->request->variable('appform_attach_req', $this->config['appform_attach_req']),
			'APPFORM_POLL_TITLE'	=> $this->request->variable('appform_poll_title', $this->config['appform_poll_title'], true),
			'APPFORM_POLL_OPTIONS'	=> $this->request->variable('appform_poll_options', $this->config['appform_poll_options'], true),
			'APPFORM_POLL_MAX_OPTIONS'	=> $this->request->variable('appform_poll_max_options', $this->config['appform_poll_max_options']),

			'APPFORM_POSITIONS'	=> $appform_positions,
			'APPFORM_QUESTIONS' => $appform_questions,

			'L_POLL_OPTIONS_EXPLAIN'	=> $this->language->lang('POLL_OPTIONS_EXPLAIN', (int) $this->config['max_poll_options']),

			'BBCODE_STATUS'			=> $this->language->lang('BBCODE_IS_ON', '<a href="' . append_sid("{$this->root_path}faq.$this->php_ext", 'mode=bbcode') . '">', '</a>'),
			'SMILIES_STATUS'		=> $this->language->lang('SMILIES_ARE_ON'),
			'IMG_STATUS'			=> $this->language->lang('IMAGES_ARE_ON'),
			'FLASH_STATUS'			=> $this->language->lang('FLASH_IS_ON'),
			'URL_STATUS'			=> $this->language->lang('URL_IS_ON'),

			'S_BBCODE_DISABLE_CHECKED'		=> !$appform_edit['allow_bbcode'],
			'S_SMILIES_DISABLE_CHECKED'		=> !$appform_edit['allow_smilies'],
			'S_MAGIC_URL_DISABLE_CHECKED'	=> !$appform_edit['allow_urls'],
			'S_BBCODE_ALLOWED'		=> true,
			'S_SMILIES_ALLOWED'		=> true,
			'S_BBCODE_IMG'			=> true,
			'S_BBCODE_FLASH'		=> true,
			'S_LINKS_ALLOWED'		=> true,

			'U_ACTION'			=> $this->u_action,
		]);
		// Assigning custom bbcodes

		if (!function_exists('display_custom_bbcodes'))
		{
			include($this->root_path . 'includes/functions_display.' . $this->php_ext);
		}
		display_custom_bbcodes();
	}

	/**
	 * Set the options a user can configure
	 *
	 * @return null
	 * @access protected
	 */
	protected function set_options()
	{
		$this->config->set('appform_forum_id', $this->request->variable('appform_forum_id', 0));
		$this->config->set('appform_guest', $this->request->variable('appform_guest', 0));
		$this->config->set('appform_nru', $this->request->variable('appform_nru', 0));
		$this->config->set('appform_attach', $this->request->variable('appform_attach', 0));
		$this->config->set('appform_attach_req', $this->request->variable('appform_attach_req', 0));
		$this->config->set('appform_poll_title', $this->request->variable('appform_poll_title', '', true));
		$this->config->set('appform_poll_options', $this->request->variable('appform_poll_options', '', true));
		$this->config->set('appform_poll_max_options', $this->request->variable('appform_poll_max_options', 0));
	}

	/**
	 * Display a drop down of all forums that one can post into
	 *
	 * @return drop down select
	 * @access protected
	 */
	private function appform_forum_select($value)
	{
		return '<select id="appform_forum_id" name="appform_forum_id">' . make_forum_select($value, false, true, true) . '</select>';
	}

	/**
	 * Set page url
	 *
	 * @param string $u_action Custom form action
	 * @return null
	 * @access public
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
