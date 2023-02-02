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

use phpbb\db\migration\migration;

class usercheck_v101 extends migration
{
	static public function depends_on()
	{
		return [
			'\dmzx\usercheck\migrations\usercheck_install',
		];
	}

	public function update_data()
	{
		return [
			['config.update', ['dmzx_usercheck_version', '1.0.1']],
		];
	}
}
