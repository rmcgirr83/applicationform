<?php
/**
*
* application [English]
*
* @package language
* @copyright (c) 2016 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* DO NOT CHANGE
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

$lang = array_merge($lang, array(
	'APPLICATION_SUBJECT'			=> 'Application from %s',
	'APPLICATION_MESSAGE'			=> 'A user, [b]%1$s[/b], has applied with the following information using the application form.' . "\n" . '[b]Real name[/b]: %2$s' . "\n" . '[b]IP:[/b] %3$s'. "\n" . '[b]Email:[/b] %4$s'. "\n" . '[b]Applying for[/b]: %5$s' . "\n" . '[b]Reason for applying:[/b] %6$s',
	'APPLICATION_SEND'				=> 'Your application has been sent to the administrators of this board. They’ll decide upon your application in the coming days.',
	'APPLICATION_PAGETITLE'			=> 'Application form',
	'APPLICATION_WELCOME_MESSAGE'	=> 'Welcome to the application form. We have positions open that you may wish to apply for. Please fill out the form below to be considered for the chosen position. Good luck!<br><br><strong><em>Items marked with an asterisk must be filled out!</em></strong>',
	'APPLICATION_REALNAME'			=> 'Real name',
	'APPLICATION_EMAIL'				=> 'E-mail address',
	'APPLICATION_POSITION'			=> 'Position you would like to have',
	'APPLICATION_WHY'				=> 'Why should we choose you?',
	'APP_NOT_COMPLETELY_FILLED'		=> 'You need to complete all fields in the application.',
	'APP_COMPLETE_NAME_WHY'			=> 'You must supply a name and why you want to be considered.',
	'ADD_ATTACHMENT_EXPLAIN'		=> 'Attach a file to assist us in making our determination on your application.',
	'ADD_ATTACHMENT_REQ'			=> '<strong>You must attach a file in order to be considered for the position</strong>.',
	'APPLICATION_REQUIRES_ATTACHMENT'	=> 'Please attach a file for the position you are applying for.',
	'APPLICATION_QUESTIONS'			=> 'Please answer the following questions',
	'APPLICATION_QUESTIONS_REQUIRED'	=> 'All questions must be answered',
	'APPLICATION QUESTION_ERROR'	=> 'You didn’t answer all the questions',
	'APPLICATION_REQUIRES_WHY'		=> 'You need to answer why we should choose you',
	'APPLICATION_ANSWER_TOO_SHORT'	=> 'The question <em>%1s</em> contains too few characters',
));
