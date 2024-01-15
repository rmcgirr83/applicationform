<?php
/**
*
* Application Form extension for the phpBB Forum Software package.
*
* @copyright 2017 Rich McGirr (RMcGirr83)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rmcgirr83\applicationform\migrations;

/**
* Primary migration
*/

class version_111 extends \phpbb\db\migration\container_aware_migration
{
	static public function depends_on()
	{
		return ['\rmcgirr83\applicationform\migrations\version_110'];
	}

	public function update_data()
	{
		return([
			['config.add', ['appform_visible', true]],
		]);
	}
}
