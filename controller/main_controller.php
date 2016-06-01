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
	protected $applicationform;

	public function __construct(
			\phpbb\config\config $config,
			\phpbb\db\driver\driver_interface $db,
			\phpbb\controller\helper $helper,
			\phpbb\request\request $request,
			\phpbb\template\template $template,
			\phpbb\user $user,
			$root_path,
			$php_ext,
			\rmcgirr83\applicationform\core\applicationform $applicationform)
	{
		$this->config = $config;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->applicationform = $applicationform;

		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
		if (!class_exists('parse_message'))
		{
			include($this->root_path . 'includes/message_parser.' . $this->php_ext);
		}
	}

	/**
	 * Display the form
	 *
	 * @access public
	 */
	public function displayform()
	{
		$nru_group_id = $this->applicationform->getnruid();

		if ($this->user->data['is_bot'] || $this->user->data['user_id'] == ANONYMOUS || (!$this->config['appform_nru'] && ($nru_group_id === (int) $this->user->data['group_id'])))
		{
			throw new http_exception(401, 'NOT_AUTHORISED');
		}

		$this->user->add_lang('posting');
		$this->user->add_lang_ext('rmcgirr83/applicationform', 'application');

		$attachment_allowed = ($this->config['allow_attachments'] && $this->config['appform_attach']) ? true : false;
		$attachment_req = $this->config['appform_attach_req'];

		add_form_key('applicationform');

		$data = array(
			'name'			=> $this->request->variable('name', '', true),
			'why'			=> $this->request->variable('why', '', true),
			'position'		=> $this->request->variable('position', '', true),
		);

		if ($this->request->is_set_post('submit'))
		{
			$message_parser = new \parse_message();
			$message_parser->parse_attachments('fileupload', 'post', $this->config['appform_forum_id'], true, false, false);

			$error = array();
			// Test if form key is valid
			if (!check_form_key('applicationform'))
			{
				$error[] = $this->user->lang['FORM_INVALID'];
			}

			if ($data['name'] === '' || $data['why'] === '')
			{
				$error[] = $this->user->lang['APP_NOT_COMPLETELY_FILLED'];
			}

			if (empty($message_parser->attachment_data) && $attachment_req && $attachment_allowed)
			{
				$error[] = $this->user->lang['APPLICATION_REQUIRES_ATTACHMENT'];
			}

			// Setting the variables we need to submit the post to the forum where all the applications come in
			$message = censor_text(trim('[quote] ' . $data['why'] . '[/quote]'));
			$subject	= sprintf($this->user->lang['APPLICATION_SUBJECT'], $this->user->data['username']);

			$url = generate_board_url() . '/memberlist.' . $this->php_ext . '?mode=viewprofile&u=' . $this->user->data['user_id'];
			$color = $this->user->data['user_colour'];
			$user_name = $this->user->data['is_registered'] ? '[url=' . $url . '][color=#' . $color . ']' . $this->user->data['username'] . '[/color][/url]' : $data['username'];

			$apply_post	= sprintf($this->user->lang['APPLICATION_MESSAGE'], $user_name, $this->request->variable('name', '', true), $data['position'], $message);

			$message_parser->message = $apply_post;

			$message_md5 = md5($message_parser->message);

			if (sizeof($message_parser->warn_msg))
			{
				$error[] = implode('<br />', $message_parser->warn_msg);
			}
			$message_parser->parse(true, true, true, true, false, true, true);

			// no errors, let's proceed
			if (!sizeof($error))
			{
				$sql = 'SELECT forum_name
					FROM ' . FORUMS_TABLE . '
					WHERE forum_id = ' . (int) $this->config['appform_forum_id'];
				$result = $this->db->sql_query($sql);
				$forum_name = $this->db->sql_fetchfield('forum_name');
				$this->db->sql_freeresult($result);

				$data = array(
					'forum_id'			=> $this->config['appform_forum_id'],
					'icon_id'			=> false,
					'poster_id' 		=> $this->user->data['user_id'],
					'enable_bbcode'		=> true,
					'enable_smilies'	=> true,
					'enable_urls'		=> true,
					'enable_sig'		=> true,

					'message'			=> $message_parser->message,
					'message_md5'		=> $message_md5,
					'attachment_data'	=> $message_parser->attachment_data,
					'filename_data'		=> $message_parser->filename_data,

					'bbcode_bitfield'	=> $message_parser->bbcode_bitfield,
					'bbcode_uid'		=> $message_parser->bbcode_uid,
					'poster_ip'			=> $this->user->ip,

					'post_edit_locked'	=> 0,
					'topic_title'		=> $subject,
					'notify_set'		=> false,
					'notify'			=> true,
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
		}
		$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off') ? '' : ' enctype="multipart/form-data"';

		$this->template->assign_vars(array(
			'REALNAME'				=> isset($data['name']) ? $data['name'] : '',
			'APPLICATION_POSITIONS' => $this->display_positions(explode("\n", $this->config['appform_positions']), $data['position']),
			'WHY'					=> isset($data['why']) ? $data['why'] : '',
			'S_FORM_ENCTYPE'		=> $form_enctype,
			'S_ERROR'				=> (isset($error) && sizeof($error)) ? implode('<br />', $error) : '',
			'S_ATTACH_BOX'			=> ($attachment_allowed && $form_enctype) ? true : false,
			'S_ATTACH_REQ'			=> $attachment_req,
		));

		// Send all data to the template file
		return $this->helper->render('appform_body.html', $this->user->lang('APPLICATION_PAGETITLE'));
	}

	private function display_positions($input_ary, $selected)
	{
		// only accept arrays, no empty ones
		if (!is_array($input_ary) || !sizeof($input_ary))
		{
			return;
		}

		// If selected isn't in the array, use first entry
		if (!in_array($selected, $input_ary))
		{
			$selected = $input_ary[0];
		}

		$select = '';
		foreach ($input_ary as $item)
		{
			$item_selected = ($item == $selected) ? ' selected="selected"' : '';
			$select .= '<option value="' . $item . '"' . $item_selected . '>' . $item . '</option>';
		}
		return $select;
	}
}
