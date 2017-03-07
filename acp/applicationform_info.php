<?php
/**
*
* @package Application Form
* @copyright (c) 2016 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\applicationform\acp;

class applicationform_info
{
	function module()
	{
		return array(
			'filename'	=> '\rmcgirr83\applicationform\acp\applicationform_module',
			'title'	=> 'ACP_APP_FORM',
			'version'	=> '1.0.6',
			'modes'	=> array(
				'settings'	=> array('title' => 'ACP_APP_FORM', 'auth' => 'ext_rmcgirr83/applicationform && acl_a_board', 'cat' => array('ACP_APP_FORM')),
			),
		);
	}
}
