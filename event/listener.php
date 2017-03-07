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

	/* @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/* @var \rmcgirr83\applicationform\core\applicationform */
	protected $applicationform;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\rmcgirr83\applicationform\core\applicationform $applicationform)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->applicationform = $applicationform;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header'	=> 'page_header',
		);
	}

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
		$version = phpbb_version_compare($this->config['version'], '3.2.0-b2', '>=');
		$this->user->add_lang_ext('rmcgirr83/applicationform', 'common');
		$this->template->assign_vars(array(
			'U_APP_FORM' => $this->helper->route('rmcgirr83_applicationform_displayform'),
			'S_FORUM_VERSION' => $version,
		));
	}
}
