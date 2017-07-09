<?php
/**
*
* Application Form extension for the phpBB Forum Software package.
*
* @copyright (c) 2016 Rich McGirr (RMcGirr83)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rmcgirr83\applicationform\acp;

class applicationform_module
{
	public	$u_action;

	function main($id, $mode)
	{
		global $db, $config, $request, $template, $user, $phpbb_container;
		global $phpbb_root_path, $phpEx;

		$user->add_lang('posting');
		$user->add_lang_ext('rmcgirr83/applicationform', 'acp_applicationform');

		$this->page_title = $user->lang['ACP_APPLICATIONFORM_SETTINGS'];
		$this->tpl_name = 'applicationform_body';

		$log = $phpbb_container->get('log');

		$config_text = $phpbb_container->get('config_text');

		$appform_questions			= $config_text->get_array(array(
			'appform_questions',
		));

		add_form_key('appform');

		if ($request->is_set_post('submit'))
		{
			$error = array();
			// Test if form key is valid
			if (!check_form_key('appform'))
			{
				$error[] = $user->lang('FORM_INVALID');
			}

			$poll_options = $request->variable('appform_poll_options', '', true);
			$poll_title = $request->variable('appform_poll_title', '', true);
			if (!empty($poll_options) || !empty($poll_title))
			{
				if (!class_exists('parse_message'))
				{
					include($phpbb_root_path . 'includes/message_parser.' . $phpEx);
				}
				$message_parser = new \parse_message();
				$poll_max_options = $request->variable('appform_poll_max_options', 0);

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
			if ($request->variable('appform_positions', '', true) === '')
			{
				$error[] = $user->lang('APPFORM_MUST_HAVE_POSITIONS');
			}

			// Set the options the user configured
			if (!sizeof($error))
			{
				$this->set_options();

				$log->add('admin', $user->data['user_id'], $user->ip, 'LOG_APPFORM_CONFIG_SAVED');

				trigger_error($user->lang['APPFORM_SETTINGS_SUCCESS'] . adm_back_link($this->u_action));
			}
		}

		$template->assign_vars(array(
			'ERROR'				=> isset($error) ? ((sizeof($error)) ? implode('<br />', $error) : '') : '',
			'APPFORM_FORUM_ID'	=> $this->appform_forum_select($request->variable('appform_forum_id', $config['appform_forum_id'])),
			'APPFORM_POSITIONS'	=> $request->variable('appform_positions', $config['appform_positions'], true),
			'APPFORM_GUEST'		=> $request->variable('appform_guest', $config['appform_guest'], true),
			'APPFORM_NRU'		=> $request->variable('appform_nru', $config['appform_nru']),
			'APPFORM_ATTACHMENT' => $request->variable('appform_attach', $config['appform_attach']),
			'APPFORM_ATTACHMENT_REQ' => $request->variable('appform_attach_req', $config['appform_attach_req']),
			'APPFORM_QUESTIONS' => $appform_questions['appform_questions'],
			'APPFORM_POLL_TITLE'	=> $request->variable('appform_poll_title', $config['appform_poll_title'], true),
			'APPFORM_POLL_OPTIONS'	=> $request->variable('appform_poll_options', $config['appform_poll_options'], true),
			'APPFORM_POLL_MAX_OPTIONS'	=> $request->variable('appform_poll_max_options', $config['appform_poll_max_options']),
			'L_POLL_OPTIONS_EXPLAIN'	=> $user->lang('POLL_OPTIONS_EXPLAIN', (int) $config['max_poll_options']),

			'U_ACTION'			=> $this->u_action,
		));
	}

	/**
	 * Set the options a user can configure
	 *
	 * @return null
	 * @access protected
	 */
	protected function set_options()
	{
		global $config, $request, $phpbb_container;

		$config_text = $phpbb_container->get('config_text');

		$appform_questions = $request->variable('appform_questions', '', true);
		$config_text->set_array(array(
			'appform_questions'			=> $appform_questions,
		));
		$config->set('appform_forum_id', $request->variable('appform_forum_id', 0));
		$config->set('appform_positions', $request->variable('appform_positions', '', true));
		$config->set('appform_guest', $request->variable('appform_guest', 0));
		$config->set('appform_nru', $request->variable('appform_nru', 0));
		$config->set('appform_attach', $request->variable('appform_attach', 0));
		$config->set('appform_attach_req', $request->variable('appform_attach_req', 0));
		$config->set('appform_poll_title', $request->variable('appform_poll_title', '', true));
		$config->set('appform_poll_options', $request->variable('appform_poll_options', '', true));
		$config->set('appform_poll_max_options', $request->variable('appform_poll_max_options', 0));
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
}
