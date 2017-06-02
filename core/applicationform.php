<?php
/**
*
* @package Application Form
* @copyright (c) 2015 Rich McGirr(RMcGirr83)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

namespace rmcgirr83\applicationform\core;

class applicationform
{
	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(
		\phpbb\auth\auth $auth,
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user)
	{
		$this->auth = $auth;
		$this->db = $db;
		$this->user = $user;
	}

	/**
	 * Get nru group id
	 *
	 * @return true or false
	 */

	public function getnruid()
	{
		$sql = 'SELECT group_id
				FROM ' . GROUPS_TABLE . "
				WHERE group_name = 'NEWLY_REGISTERED'
					AND group_type = " . GROUP_SPECIAL;
		$result = $this->db->sql_query($sql);
		$group_id = $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if (!$group_id)
		{
			return false;
		}

		// now we query the user group table to see if the user is in the nru group
		$sql = 'SELECT group_id
				FROM ' . USER_GROUP_TABLE . '
				WHERE group_id = ' . (int) $group_id . ' AND user_id = ' . (int) $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$group_id = $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if (!$group_id)
		{
			return false;
		}

		return true;
	}

	public function whois($user_ip, $forum_id)
	{
		if (!$this->auth->acl_gets('a_', 'm_') || !$this->auth->acl_get('m_', $forum_id))
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
