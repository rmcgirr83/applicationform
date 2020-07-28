<?php
/**
*
* @package Application Form
* @copyright (c) 2020 RMcGirr83
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\applicationform\acp;

class applicationform_module
{
	public	$u_action;

	function main($id, $mode)
	{
		global $phpbb_container;

		$this->tpl_name = 'acp_applicationform';
		$this->page_title = $phpbb_container->get('language')->lang('ACP_APPFORM_TITLE');

		// Get an instance of the admin controller
		$admin_controller = $phpbb_container->get('rmcgirr83.applicationform.admin.controller');

		$admin_controller->display_options();

		$admin_controller->set_page_url($this->u_action);
	}
}
