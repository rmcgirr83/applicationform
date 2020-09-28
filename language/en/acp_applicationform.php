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
	$lang = [];
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

$lang = array_merge($lang, [
	'ACP_APPLICATIONFORM_SETTINGS'		=> 'Application Form Options',
	'APPLICATIONFORM_SETTINGS'			=> 'Settings',
	'APPLICATIONFORM_FORUM'				=> 'Forum',
	'APPLICATIONFORM_FORUM_EXPLAIN'		=> 'Select the forum where the application should post to.',
	'APPFORM_FORUM_NO_ATTACH'			=> 'The forum selected does not allow attachments.',
	'APPLICATIONFORM_POSITIONS'			=> 'Application positions',
	'APPLICATIONFORM_POSITIONS_EXPLAIN'	=> 'Each position goes on a new line.',
	'APPFORM_SETTINGS_SUCCESS'			=> 'Application Form settings have been saved.',
	'APPFORM_MUST_HAVE_POSITIONS'		=> 'You must have at least one position.',
	'APPFORM_GUEST'						=> 'Allow guests',
	'APPFORM_GUEST_EXPLAIN'				=> 'If set yes, guests visiting your forum will have access to the application form.',
	'APPLICATIONFORM_NRU'				=> 'Allow newly registered group',
	'APPLICATIONFORM_NRU_EXPLAIN'		=> 'If set yes, those in the newly registered group will have access to the application form.',
	'APPLICATIONFORM_ALLOW_ATTACHMENT'	=> 'Allow attachments',
	'APPLICATIONFORM_ALLOW_ATTACHMENT_EXPLAIN' => 'If set to yes, files of types doc, pdf and text will be allowed to be uploaded.',
	'APPLICATIONFORM_ATTACHMENT_REQ'	=> 'Attachment is required',
	'APPLICATIONFORM_ATTACHMENT_REQ_EXPLAIN' => 'If set to yes, the form will require an attachment for the position being applied for.',
	'APPLICATIONFORM_QUESTIONS'			=> 'Questions',
	'APPLICATIONFORM_QUESTIONS_EXPLAIN'	=> 'Any additional questions you want to ask on the form can be put here. Each question goes on a new line.',
	'APPFORM_INFO_PREVIEW'		=> 'Preview',
	'APPFORM_INFO'				=> 'Application Form Information',
	'APPFORM_INFO_EXPLAIN'		=> 'This message is displayed on the application form page',
	//Donation
	'PAYPAL_IMAGE_URL'          => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/silver-pill-paypal-26px.png',
	'PAYPAL_ALT'                => 'Donate using PayPal',
	'BUY_ME_A_BEER_URL'         => 'https://paypal.me/RMcGirr83',
	'BUY_ME_A_BEER'				=> 'Buy me a beer for creating this extension',
	'BUY_ME_A_BEER_SHORT'		=> 'Make a donation for this extension',
	'BUY_ME_A_BEER_EXPLAIN'		=> 'This extension is completely free. It is a project that I spend my time on for the enjoyment and use of the phpBB community. If you enjoy using this extension, or if it has benefited your forum, please consider <a href="https://paypal.me/RMcGirr83" target="_blank" rel="noreferrer noopener">buying me a beer</a>. It would be greatly appreciated. <i class="fa fa-smile-o" style="color:green;font-size:1.5em;" aria-hidden="true"></i>',
]);
