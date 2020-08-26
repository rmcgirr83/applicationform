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

class version_110 extends \phpbb\db\migration\container_aware_migration
{
	static public function depends_on()
	{
		return ['\rmcgirr83\applicationform\migrations\version_107'];
	}

	public function update_data()
	{
		return([
			['custom', [
				[$this, 'copy_positions']
			]],
			['config.remove', ['appform_positions']],
		]);
	}

	public function copy_positions()
	{

		$config_text = $this->container->get('config_text');

		$config_text->set_array([
			'appform_positions'	=> $this->config['appform_positions'],
			'appform_info'		=> '',
			'appform_info_uid'	=> '',
			'appform_info_bitfield'	=> '',
			'appform_info_flags'	=> OPTION_FLAG_BBCODE + OPTION_FLAG_SMILIES + OPTION_FLAG_LINKS,
		]);
	}
}
