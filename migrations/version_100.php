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

class version_100 extends \phpbb\db\migration\migration
{
	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v31x\v314rc1');
	}

	public function update_data()
	{

		return(array(
			array('config.add', array('appform_forum_id', 0)),
			array('config.add', array('appform_positions', 'Moderator')),
			array('module.add', array(
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_APPFORM_TITLE'
			)),

			array('module.add', array(
				'acp',
				'ACP_APPFORM_TITLE',
				array(
					'module_basename'	=> '\rmcgirr83\applicationform\acp\applicationform_module',
					'modes'				=> array('settings'),
				),
			)),
		));
	}
}
