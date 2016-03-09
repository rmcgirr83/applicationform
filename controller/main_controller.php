<?php
/**
*
* Application Form extension for the phpBB Forum Software package.
*
* @copyright (c) 2016 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\applicationform\controller;

use phpbb\exception\http_exception;

/**
* Main controller
*/
class main_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/* @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string phpBB root path */
	protected $root_path;

	/** @var string phpEx */
	protected $php_ext;

	/* @var \rmcgirr83\applicationform\core\applicationform */
	protected $functions;

	/**
	* Constructor
	*
	* @param \phpbb\config\config               $config         Config object
	* @param \phpbb\db\driver\driver			$db				Database object
	* @param \phpbb\controller\helper           $helper         Controller helper object
	* @param \phpbb\request\request				$request		Request object
	* @param \phpbb\template\template           $template       Template object
	* @param \phpbb\user                        $user           User object
	* @param string                             $root_path      phpBB root path
	* @param string                             $php_ext        phpEx
	* @param \rmcgirr83\applicationform\core\applicationform	$functions	functions to be used by class
	* @access public
	*/
	public function __construct(
			\phpbb\config\config $config,
			\phpbb\db\driver\driver_interface $db,
			\phpbb\controller\helper $helper,
			\phpbb\request\request $request,
			\phpbb\template\template $template,
			\phpbb\user $user,
			$root_path,
			$php_ext,
			\rmcgirr83\applicationform\core\applicationform $functions)
	{
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->functions = $functions;

		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
	}

	/**
	 * Display the form
	 *
	 * @access public
	 */
	public function displayform()
	{
		$nru_group_id = $this->functions->getnruid();
		if ($this->user->data['is_bot'] || $this->user->data['user_id'] == ANONYMOUS || (!$this->config['appform_nru'] && ($nru_group_id === (int) $this->user->data['group_id'])))
		{
			throw new http_exception(401, 'NOT_AUTHORISED');
		}
		$this->user->add_lang_ext('rmcgirr83/applicationform', 'application');

		add_form_key('applicationform');

		if ($this->request->is_set_post('submit'))
		{
			// Test if form key is valid
			if (!check_form_key('applicationform'))
			{
				trigger_error($this->user->lang['FORM_INVALID'], E_USER_WARNING);
			}
			if ($this->request->variable('name', '') === '' || $this->request->variable('why', '') === '')
			{
				trigger_error($this->user->lang['APP_NOT_COMPLETELY_FILLED'], E_USER_WARNING);
			}

			$sql = 'SELECT forum_name
				FROM ' . FORUMS_TABLE . '
				WHERE forum_id = ' . (int) $this->config['appform_forum_id'];
			$result = $this->db->sql_query($sql);
			$forum_name = $this->db->sql_fetchfield('forum_name');
			$this->db->sql_freeresult($result);

			// Setting the variables we need to submit the post to the forum where all the applications come in
			$subject	= sprintf($this->user->lang['APPLICATION_SUBJECT'], $this->user->data['username']);
			$apply_post	= sprintf($this->user->lang['APPLICATION_MESSAGE'], get_username_string('full', $this->user->data['user_id'], $this->user->data['username'], $this->user->data['user_colour']), $this->request->variable('name', '', true), $this->user->data['user_email'], $this->request->variable('postion', '', true), $this->request->variable('why', '', true));

			// variables to hold the parameters for submit_post
			$uid = $bitfield = $options = '';

			generate_text_for_storage($apply_post, $uid, $bitfield, $options, true, true, true);

			$data = array(
				'forum_id'		=> $this->config['appform_forum_id'],
				'icon_id'		=> false,
				'poster_id' 	=> $this->user->data['user_id'],
				'enable_bbcode'		=> true,
				'enable_smilies'	=> true,
				'enable_urls'		=> true,
				'enable_sig'		=> true,

				'message'			=> $apply_post,
				'message_md5'		=> md5($apply_post),

				'bbcode_bitfield'	=> $bitfield,
				'bbcode_uid'		=> $uid,
				'poster_ip'			=> $this->user->ip,

				'post_edit_locked'	=> 0,
				'topic_title'		=> $subject,
				'notify_set'		=> false,
				'notify'			=> false,
				'post_time' 		=> time(),
				'forum_name'		=> $forum_name,
				'enable_indexing'	=> true,
				'force_approved_state'	=> true,
				'force_visibility' => true,
			);
			$poll = array();

			// Submit the post!
			submit_post('post', $subject, $this->user->data['username'], POST_NORMAL, $poll, $data);

			$message = $this->user->lang['APPLICATION_SEND'];
			$message = $message . '<br /><br />' . sprintf($this->user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$this->root_path}index.$this->php_ext") . '">', '</a>');
			trigger_error($message);
		}
		$this->template->assign_vars(array(
			'APPLICATION_POSITIONS' => $this->display_positions(explode("\n", $this->config['appform_positions'])),
		));

		// Send all data to the template file
		return $this->helper->render('appform_body.html', $this->user->lang('APPLICATION_PAGETITLE'));
	}

	private function display_positions($input_ary)
	{
		// only accept arrays, no empty ones
		if (!is_array($input_ary) || !sizeof($input_ary))
		{
			return;
		}
		$select = '';
		foreach ($input_ary as $item)
		{
			$select .= '<option value="' . $item . '">' . $item . '</option>';
		}
		return $select;
	}
}
