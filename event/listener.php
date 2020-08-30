<?php
/**
*
* @package Application Form
* @copyright (c) 2016 Rich McGirr (RMcGirr83)
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace rmcgirr83\applicationform\event;

/**
* @ignore
*/
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\language\language;
use phpbb\template\template;
use phpbb\user;
use rmcgirr83\applicationform\core\applicationform;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\controller\helper */
	protected $helper;

	/** @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/* @var \rmcgirr83\applicationform\core\applicationform */
	protected $applicationform;

	/**
	* Constructor
	*
	* @param \phpbb\config\config								$config				Config object
	* @param \phpbb\controller\helper 							$helper				Helper object
	* @param \phpbb\language\language							$language			Language object
	* @param \phpbb\template\template							$template			Template object
	* @param \phpbb\user										$user				User object
	* @param \rmcgirr83\applicationform\core\applicationform	$applicationform	Methods for the class
	* @access public
	*/
	public function __construct(
		config $config,
		helper $helper,
		language $language,
		template $template,
		user $user,
		applicationform $applicationform)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->language = $language;
		$this->template = $template;
		$this->user = $user;
		$this->applicationform = $applicationform;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.acp_extensions_run_action_after'	=>	'acp_extensions_run_action_after',
			'core.page_header'	=> 'page_header',
		);
	}

	/* Display additional metdate in extension details
	*
	* @param $event			event object
	* @param return null
	* @access public
	*/
	public function acp_extensions_run_action_after($event)
	{
		if ($event['ext_name'] == 'rmcgirr83/applicationform' && $event['action'] == 'details')
		{
			$this->language->add_lang('acp_applicationform', $event['ext_name']);
			$this->template->assign_var('S_BUY_ME_A_BEER_APPFORM', true);
		}
	}

	/* Display link in header
	*
	* @param $event			event object
	* @param return null
	* @access public
	*/
	public function page_header($event)
	{
		$in_nru_group = $this->applicationform->getnruid();

		$allow_guest = (!$this->config['appform_guest'] && $this->user->data['user_id'] == ANONYMOUS) ? false : true;
		$allow_nru = (!$this->config['appform_nru'] && $in_nru_group) ? false : true;
		if ($this->user->data['is_bot'] || !$allow_guest || !$allow_nru)
		{
			$this->template->assign_var('U_APP_FORM', false);
			return false;
		}

		$this->language->add_lang('common', 'rmcgirr83/applicationform');
		$this->template->assign_vars(array(
			'U_APP_FORM' => $this->helper->route('rmcgirr83_applicationform_displayform')
		));
	}
}
