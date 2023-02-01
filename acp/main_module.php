<?php
/**
 *
 * User Check. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\usercheck\acp;

/**
 * User Check ACP module.
 */
class main_module
{
	public $page_title;
	public $tpl_name;
	public $u_action;

	/**
	 * Main ACP module
	 *
	 * @param int	$id	The module ID
	 * @param string $mode The module mode (for example: manage or settings)
	 * @throws Exception
	 */
	public function main($id, $mode)
	{
		global $phpbb_container;

		/** @var acp_controller $acp_controller */
		$acp_controller = $phpbb_container->get('dmzx.usercheck.controller.acp');

		/** @var language $language */
		$language = $phpbb_container->get('language');

		// Load a template from adm/style for our ACP page
		$this->tpl_name = 'acp_usercheck_body';

		// Set the page title for our ACP page
		$this->page_title = $language->lang('ACP_USERCHECK_TITLE');

		// Make the $u_action url available in our ACP controller
		$acp_controller->set_page_url($this->u_action);

		// Load the display options handle in our ACP controller
		$acp_controller->display_options();
	}
}
