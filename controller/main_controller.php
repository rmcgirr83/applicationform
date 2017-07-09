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
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\config\db_text */
	protected $config_text;

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

	/** @var \phpbb\captcha\factory */
	protected $captcha_factory;

	public function __construct(
			\phpbb\auth\auth $auth,
			\phpbb\config\config $config,
			\phpbb\config\db_text $config_text,
			\phpbb\db\driver\driver_interface $db,
			\phpbb\controller\helper $helper,
			\phpbb\request\request $request,
			\phpbb\template\template $template,
			\phpbb\user $user,
			$root_path,
			$php_ext,
			\rmcgirr83\applicationform\core\applicationform $applicationform,
			\phpbb\captcha\factory $captcha_factory,
			\rmcgirr83\topicdescription\event\listener $topicdescription = null)
	{
		$this->auth = $auth;
		$this->config = $config;
		$this->config_text = $config_text;
		$this->db = $db;
		$this->helper = $helper;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->root_path = $root_path;
		$this->php_ext = $php_ext;
		$this->applicationform = $applicationform;
		$this->captcha_factory = $captcha_factory;
		$this->topicdescription = $topicdescription;

		if (!function_exists('submit_post'))
		{
			include($this->root_path . 'includes/functions_posting.' . $this->php_ext);
		}
		if (!function_exists('validate_data'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
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
		$in_nru_group = $this->applicationform->getnruid();

		$allow_guest = (!$this->config['appform_guest'] && $this->user->data['user_id'] == ANONYMOUS) ? false : true;
		$allow_nru = (!$this->config['appform_nru'] && $in_nru_group) ? false : true;
		if ($this->user->data['is_bot'] || !$allow_guest || !$allow_nru)
		{
			throw new http_exception(401, 'NOT_AUTHORISED');
		}

		$this->user->add_lang('ucp');
		$this->user->add_lang_ext('rmcgirr83/applicationform', 'application');

		$attachment_allowed = ($this->config['allow_attachments'] && $this->config['appform_attach']) ? true : false;
		$attachment_req = $this->config['appform_attach_req'];

		add_form_key('applicationform');

		$data = array(
			'username'		=> $this->request->variable('name', '', true),
			'email'			=> ($this->user->data['user_id'] != ANONYMOUS) ? $this->user->data['user_email'] : strtolower($this->request->variable('email', '')),
			'why'			=> $this->request->variable('why', '', true),
			'position'		=> $this->request->variable('position', '', true),
		);

		$appform_questions			= $this->config_text->get_array(array(
			'appform_questions',
		));

		$have_questions = (!empty($appform_questions['appform_questions'])) ? true : false;

		if ($have_questions)
		{
			//convert the questions into an array
			$questions = explode("\n", $appform_questions['appform_questions']);

			$answers = array();
			foreach ($questions as $key => $question)
			{
				$this->template->assign_block_vars('questions', array(
					'QUESTION'	=> $question,
					'FORM_NAME'	=> $key,
					'ANSWER'	=> $this->request->variable($key, '', true),
				));
				$answers[$question] = $this->request->variable($key, '', true);
			}
		}

		// Visual Confirmation - The CAPTCHA kicks in here
		if (!$this->user->data['is_registered'])
		{
			$captcha = $this->captcha_factory->get_instance($this->config['captcha_plugin']);
			$captcha->init((CONFIRM_POST));
		}

		if ($this->request->is_set_post('submit'))
		{
			$error = array();

			// Test if form key is valid
			if (!check_form_key('applicationform'))
			{
				$error[] = $this->user->lang['FORM_INVALID'];
			}

			if (!$this->user->data['is_registered'])
			{
				$error = validate_data($data, array(
					'username'			=> array(
						array('string', false, $this->config['min_name_chars'], $this->config['max_name_chars']),
						array('username', '')),
					'email'			=> array(
						array('string', false, 6, 60),
						array('user_email')),
				));
			}

			if (validate_string($data['why'], false, $this->config['min_post_chars']))
			{
				$error[] = $this->user->lang('APPLICATION_ANSWER_TOO_SHORT', $this->user->lang('APPLICATION_WHY'));
			}

			if ($have_questions)
			{
				//var_dump($answers);
				foreach ($answers as $question => $answer)
				{
					$response = validate_string($answer, false, $this->config['min_post_chars']);
					if ($response)
					{
						$error[] = $this->user->lang('APPLICATION_ANSWER_TOO_SHORT', $question);
					}
				}

			}

			// CAPTCHA check
			if (!$this->user->data['is_registered'] && !$captcha->is_solved())
			{
				$vc_response = $captcha->validate($data);
				if ($vc_response !== false)
				{
					$error[] = $vc_response;
				}

				if ($this->config['max_reg_attempts'] && $captcha->get_attempt_count() > $this->config['max_reg_attempts'])
				{
					$error[] = $this->user->lang('TOO_MANY_REGISTERS');
				}
			}

			$message_parser = new \parse_message();
			$message_parser->parse_attachments('fileupload', 'post', $this->config['appform_forum_id'], true, false, false);

			if (empty($message_parser->attachment_data) && $attachment_req && $attachment_allowed)
			{
				$error[] = $this->user->lang('APPLICATION_REQUIRES_ATTACHMENT');
			}

			// Replace "error" strings with their real, localised form
			$error = array_map(array($this->user, 'lang'), $error);

			// Setting the variables we need to submit the post to the forum where all the applications come in

			$message = censor_text(trim('[quote] ' . $data['why'] . '[/quote]'));
			$subject	= $this->user->lang('APPLICATION_SUBJECT', $data['username'] . ' - ' . $data['position']);

			$url = generate_board_url() . '/memberlist.' . $this->php_ext . '?mode=viewprofile&u=' . $this->user->data['user_id'];
			$color = !empty($this->user->data['user_colour']) ? '[color=#' . $this->user->data['user_colour'] . ']' . $data['username'] . '[/color]' : $data['username'];
			$user_name = $this->user->data['is_registered'] ? '[url=' . $url . ']' . $color . '[/url]' : $data['username'];
			$user_ip = '[url=http://en.utrace.de/?query=' . $this->user->ip . ']' . $this->user->ip . '[/url]';

			$responses = '';
			if ($have_questions)
			{
				foreach ($answers as $key => $value)
				{
					$responses .= "\n".'[b]' . $key .'[/b]' .  $this->user->lang('COLON') . ' ' . censor_text($value);
				}
			}

			$apply_post	= $this->user->lang('APPLICATION_MESSAGE', $user_name, $user_name, $user_ip, $data['email'], $data['position'], $message . $responses);

			$message_parser->message = $apply_post;

			$message_md5 = md5($message_parser->message);

			$poll = array();
			if (!empty($this->config['appform_poll_title']) && !empty($this->config['appform_poll_options']))
			{
				$poll_option_text = implode("/n", array($this->config['appform_poll_options']));
				$poll_max_options = (int) $this->config['appform_poll_max_options'];
				$poll = array(
					'poll_title'		=> $this->config['appform_poll_title'],
					'poll_length'		=> 0,
					'poll_max_options'	=> $poll_max_options,
					'poll_option_text'	=> $poll_option_text,
					'poll_start'		=> time(),
					'poll_last_vote'	=> 0,
					'poll_vote_change'	=> true,
					'enable_bbcode'		=> true,
					'enable_urls'		=> true,
					'enable_smilies'	=> true,
					'img_status'		=> true
				);

				$message_parser->parse_poll($poll);
			}

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

				if ($this->topicdescription !== null)
				{
					$data['topic_desc'] = '';
				}
				// Submit the post!
				submit_post('post', $subject, $this->user->data['username'], POST_NORMAL, $poll, $data);

				//reset captcha
				if (!$this->user->data['is_registered'] && (isset($captcha) && $captcha->is_solved() === true))
				{
					$captcha->reset();
				}

				$message = $this->user->lang['APPLICATION_SEND'];
				$message = $message . '<br /><br />' . sprintf($this->user->lang['RETURN_INDEX'], '<a href="' . append_sid("{$this->root_path}index.$this->php_ext") . '">', '</a>');

				trigger_error($message);
			}
		}
		$form_enctype = (@ini_get('file_uploads') == '0' || strtolower(@ini_get('file_uploads')) == 'off') ? '' : ' enctype="multipart/form-data"';

		// Visual Confirmation - Show images
		$s_hidden_fields = array();

		if (!$this->user->data['is_registered'])
		{
			$s_hidden_fields = array_merge($s_hidden_fields, $captcha->get_hidden_fields());
		}
		$s_hidden_fields = build_hidden_fields($s_hidden_fields);

		if (!$this->user->data['is_registered'])
		{
			$this->template->assign_vars(array(
				'CAPTCHA_TEMPLATE'		=> $captcha->get_template(),
			));
		}
		$this->template->assign_vars(array(
			'REALNAME'				=> ($this->user->data['user_id'] != ANONYMOUS && empty($data['username'])) ?  $this->user->data['username'] : $data['username'],
			'APPLICATION_POSITIONS' => $this->display_positions(explode("\n", $this->config['appform_positions']), $data['position']),
			'APPLICATION_EMAIL'		=> $data['email'],
			'WHY'					=> $data['why'],
			'S_FORM_ENCTYPE'		=> $form_enctype,
			'S_ERROR'				=> (isset($error) && sizeof($error)) ? implode('<br />', $error) : '',
			'S_ATTACH_BOX'			=> ($attachment_allowed && $form_enctype) ? true : false,
			'S_ATTACH_REQ'			=> $attachment_req,
			'S_EMAIL_NEEDED'		=> $this->user->data['user_id'] == ANONYMOUS ? true : false,
			'S_HIDDEN_FIELDS'		=> $s_hidden_fields,
		));

		// Send all data to the template file
		return $this->helper->render('appform_body.html', $this->user->lang('APPLICATION_PAGETITLE'));
	}

	private function display_positions($input_ary, $selected)
	{
		// only accept arrays, no empty ones
		if (!is_array($input_ary) || !sizeof($input_ary))
		{
			return false;
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

	public function whois($user_ip)
	{
		if (!$this->auth->acl_gets('a_', 'm_'))
		{
			throw new http_exception(401, 'NOT_AUTHORISED');
		}
		$this->user->add_lang('acp/users');

		$this->page_title = 'WHOIS';
		$this->tpl_name = 'simple_body';

		$user_ip = phpbb_ip_normalise($user_ip);
		$ipwhois = user_ipwhois($user_ip);

		$this->template->assign_var('WHOIS', $ipwhois);

		return $this->helper->render('viewonline_whois.html', $this->page_title);
	}
}
