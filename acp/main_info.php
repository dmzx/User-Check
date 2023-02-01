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
 * User Check ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\dmzx\usercheck\acp\main_module',
			'title'		=> 'ACP_USERCHECK_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_USERCHECK',
					'auth'	=> 'ext_dmzx/usercheck && acl_a_board',
					'cat'	=> ['ACP_USERCHECK_TITLE']
				],
			],
		];
	}
}
