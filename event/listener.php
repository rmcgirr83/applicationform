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
	protected $functions;

	public function __construct(
		\phpbb\config\config $config,
		\phpbb\controller\helper $helper,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\rmcgirr83\applicationform\core\applicationform $functions)
	{
		$this->config = $config;
		$this->helper = $helper;
		$this->template = $template;
		$this->user = $user;
		$this->functions = $functions;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.page_header'	=> 'page_header',
		);
	}

	public function page_header($event)
	{
		$nru_group_id = $this->functions->getnruid();

		if ((!$this->config['appform_nru'] && ($nru_group_id === (int) $this->user->data['group_id'])) || $this->user->data['is_bot'] || $this->user->data['user_id'] == ANONYMOUS)
		{
			$this->template->assign_var('U_APP_FORM', false);
			return false;
		}

		$this->user->add_lang_ext('rmcgirr83/applicationform', 'common');
		$this->template->assign_var('U_APP_FORM', $this->helper->route('rmcgirr83_applicationform_displayform'));
	}
}
