<?php
/**
*
* @package Application Form
* @copyright (c) 2020 RMcGirr83
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace rmcgirr83\applicationform\acp;

class applicationform_info
{
	function module()
	{
		return [
			'filename'	=> '\rmcgirr83\applicationform\acp\applicationform_module',
			'title'	=> 'ACP_APP_FORM',
			'version'	=> '1.1.0',
			'modes'	=> [
				'settings'	=> ['title' => 'ACP_APP_FORM', 'auth' => 'ext_rmcgirr83/applicationform && acl_a_board', 'cat' => ['ACP_APP_FORM']],
			],
		];
	}
}
