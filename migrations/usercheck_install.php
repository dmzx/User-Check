<?php
/**
 *
 * User Check. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\usercheck\migrations;

use phpbb\db\migration\container_aware_migration;

class usercheck_install extends container_aware_migration
{
	public static function depends_on()
	{
		return [
			'\phpbb\db\migration\data\v330\v330'
		];
	}

	public function update_data()
	{
		return [
			['config.add', ['dmzx_usercheck_version', '1.0.0']],
			['config.add', ['dmzx_usercheck_enable', 0]],
			['config.add', ['dmzx_usercheck_proxy', 1]],
			['config.add', ['dmzx_usercheck_ip_check', 1]],
			['config.add', ['dmzx_usercheck_cookie_check', 1]],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_USERCHECK_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_USERCHECK_TITLE',
				[
					'module_basename'	=> '\dmzx\usercheck\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
