<?php

/**
*
*
* @package - Application Form language
* @copyright 2016 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
// Some characters you may want to copy&paste:
// ’ » “ ” …

$lang = array_merge($lang, array(
	//Module and page titles
	'ACP_APPFORM_TITLE'					=> 'Application Form',
	'ACP_APP_FORM'						=> 'Settings',
	'ACP_APPLICATIONFORM_SETTINGS'		=> 'Application Form Settings',
	'APPLICATIONFORM_FORUM'				=> 'Forum',
	'APPLICATIONFORM_FORUM_EXPLAIN'		=> 'Select the forum, where the application should post to.',
	'APPLICATIONFORM_POSITIONS'			=> 'Application positions',
	'APPLICATIONFORM_POSITIONS_EXPLAIN'		=> 'Enter positions for users to apply for separated by new lines.',
	'APPFORM_SETTINGS_SUCCESS'			=> 'Application Form settings have been saved.',
	'APPFORM_MUST_HAVE_POSITIONS'		=> 'You must have at least one position.',
));
