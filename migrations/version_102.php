<?php
/**
*
* Application Form extension for the phpBB Forum Software package.
*
* @copyright 2016 Rich McGirr (RMcGirr83)
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

namespace rmcgirr83\applicationform\migrations;

/**
* Primary migration
*/

class version_102 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\rmcgirr83\applicationform\migrations\version_101');
	}

	public function update_data()
	{

		return(array(
			array('config.add', array('appform_attach', 0)),
			array('config.add', array('appform_attach_req', 0)),
		));
	}
}
