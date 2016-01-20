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

	public function __construct(
		\phpbb\db\driver\driver_interface $db)
	{
		$this->db = $db;
	}

	/**
	 * Get nru group id
	 *
	 * @return int group id
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
		return (int) $group_id;
	}
}
