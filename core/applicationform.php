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

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\user */
	protected $user;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\user $user)
	{
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
				WHERE group_id = ' . (int) $group_id . ' AND user_id = ' . $this->user->data['user_id'];
		$result = $this->db->sql_query($sql);
		$group_id = $this->db->sql_fetchfield('group_id');
		$this->db->sql_freeresult($result);

		if (!$group_id)
		{
			return false;
		}

		return true;
	}
}
