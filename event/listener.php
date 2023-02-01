<?php
/**
 *
 * User Check. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\usercheck\event;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\user;
use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;
use dmzx\usercheck\core\functions;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var user */
	protected $user;

	/** @var driver_interface */
	protected $db;

	/** @var request_interface */
	protected $request;

	/** @var functions */
	protected $functions;

	/**
	 * Constructor.
	 *
	 * @param config				$config
	 * @param language				$lang
	 * @param user					$user
	 * @param driver_interface		$db
	 * @param request_interface		$request
	 * @param functions				$functions
	 */
	public function __construct(
		config $config,
		language $lang,
		user $user,
		driver_interface $db,
		request_interface $request,
		functions $functions
	)
	{
		$this->config		= $config;
		$this->language 	= $lang;
		$this->user	 		= $user;
		$this->db			= $db;
		$this->request 		= $request;
		$this->functions 	= $functions;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'core.user_setup'				=> 'user_setup',
			'core.ucp_register_data_before'	=> 'ucp_register_data_before',
			'core.ucp_delete_cookies'		=> 'ucp_delete_cookies',
			'core.user_add_after'			=> 'user_add_after',
		];
	}

	public function user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'dmzx/usercheck',
			'lang_set' => 'usercheck',
		];
		$event['lang_set_ext'] = $lang_set_ext;

		if ($this->user->data['user_id'] == ANONYMOUS)
		{
			return;
		}

		if ($this->config['dmzx_usercheck_cookie_check'])
		{
			if (!$this->request->is_set($this->config['cookie_name'] . '_uc', \phpbb\request\request_interface::COOKIE))
			{
				$this->user->set_cookie('uc', $this->user->data['user_id'] . '|' . base64_encode($this->user->data['username']), time() + 518400000);
			}
		}
	}

	public function ucp_register_data_before(): void
	{
		if ($this->config['dmzx_usercheck_enable'])
		{
			if ($this->config['dmzx_usercheck_proxy'])
			{
				$proxy_check = $this->functions->get_proxy();

				if ($proxy_check == true)
				{
					trigger_error("<div class='rules'>" . $this->language->lang('USERCHECK_PROXY') . "</div>");
				}
			}

			if ($this->config['dmzx_usercheck_ip_check'])
			{
				$ip_double_account = $this->functions->ip_check();

				if ($ip_double_account == true)
				{
					trigger_error("<div class='rules'>" . $this->language->lang('USERCHECK_ALREADY') . "</div>");
				}
			}

			if ($this->config['dmzx_usercheck_cookie_check'])
			{
				$if_cookie_account = $this->functions->cookie_check();

				if ($if_cookie_account == true)
				{
					trigger_error("<div class='rules'>" . $this->language->lang('USERCHECK_ALREADY') . "</div>");
				}
			}
		}
	}

	public function ucp_delete_cookies($event)
	{
		if ($event['cookie_name'] === 'uc')
		{
			$event['retain_cookie'] = true;
		}
	}

	public function user_add_after($event)
	{
		$user_id = $event['user_id'];
		$user_row = $event['user_row'];

		if ($user_id === false || defined('ADMIN_START'))
		{
			return;
		}

		if ($this->config['dmzx_usercheck_cookie_check'])
		{

			if (!$this->request->is_set($this->config['cookie_name'] . '_uc', \phpbb\request\request_interface::COOKIE))
			{
				$this->user->set_cookie('uc', $user_id . '|' . base64_encode($user_row['username']), time() + 518400000);
			}
		}
	}
}
