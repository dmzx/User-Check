<?php
/**
 *
 * User Check. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\usercheck\controller;

use phpbb\config\config;
use phpbb\language\language;
use phpbb\log\log;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;

/**
 * User Check ACP controller.
 */
class acp_controller
{
	/** @var config */
	protected $config;

	/** @var language */
	protected $language;

	/** @var log */
	protected $log;

	/** @var request */
	protected $request;

	/** @var template */
	protected $template;

	/** @var user */
	protected $user;

	/** @var string Custom form action */
	protected $u_action;

	/**
	 * Constructor.
	 *
	 * @param config					$config			 	Config object
	 * @param language					$language			Language object
	 * @param log						$log				Log object
	 * @param request					$request			Request object
	 * @param template					$template			Template object
	 * @param user						$user				User object
	 */
	public function __construct(
		config $config,
		language $language,
		log $log,
		request $request,
		template $template,
		user $user
	)
	{
		$this->config 			= $config;
		$this->language			= $language;
		$this->log				= $log;
		$this->request			= $request;
		$this->template			= $template;
		$this->user				= $user;
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_options()
	{
		// Add our common language file
		$this->language->add_lang('acp_usercheck', 'dmzx/usercheck');

		// Create a form key for preventing CSRF attacks
		add_form_key('dmzx_usercheck_acp');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('dmzx_usercheck_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$this->config->set('dmzx_usercheck_enable', $this->request->variable('dmzx_usercheck_enable', 0));

				$this->config->set('dmzx_usercheck_proxy', $this->request->variable('dmzx_usercheck_proxy', 0));
				$this->config->set('dmzx_usercheck_ip_check', $this->request->variable('dmzx_usercheck_ip_check', 0));
				$this->config->set('dmzx_usercheck_cookie_check', $this->request->variable('dmzx_usercheck_cookie_check', 0));

				// Add option settings change action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_USERCHECK_SETTINGS');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->language->lang('ACP_USERCHECK_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		$s_errors = !empty($errors);

		// Set output variables for display in the template
		$this->template->assign_vars([
			'S_ERROR'						=> $s_errors,
			'ERROR_MSG'						=> $s_errors ? implode('<br>', $errors) : '',
			'USERCHECK_ENABLE'				=> $this->config['dmzx_usercheck_enable'],
			'USERCHECK_PROXY'				=> $this->config['dmzx_usercheck_proxy'],
			'USERCHECK_IP_CHECK'			=> $this->config['dmzx_usercheck_ip_check'],
			'USERCHECK_COOKIE_CHECK'		=> $this->config['dmzx_usercheck_cookie_check'],
			'USERCHECK_VERSION'				=> $this->config['dmzx_usercheck_version'],
			'U_ACTION'						=> $this->u_action,
		]);
	}

	/**
	 * Set custom form action.
	 *
	 * @param string	$u_action	Custom form action
	 * @return void
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
