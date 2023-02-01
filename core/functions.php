<?php
/**
 *
 * User Check. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2023, dmzx, https://www.dmzx-web.net
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace dmzx\usercheck\core;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\request\request_interface;

class functions
{
	/** @var config */
	protected $config;

	/** @var template */
	protected $template;

	/** @var driver_interface */
	protected $db;

	/** @var request_interface */
	protected $request;

	/**
	 * Constructor.
	 *
	 * @param config				$config
	 * @param driver_interface		$db
	 * @param request_interface		$request
	 */
	public function __construct(
		config $config,
		driver_interface $db,
		request_interface $request
	)
	{
		$this->config		= $config;
		$this->db			= $db;
		$this->request 		= $request;
	}

	public function get_proxy()
	{
		$ip	= $this->get_ip();

	 	$curl_yes = (function_exists('curl_version')) ? true : false;

		$proxy = '';

		if ($curl_yes)
		{
			$curl_handle = curl_init();
			$data = '[{"query": "'.$ip.'", "fields": "status,proxy"}]';
			curl_setopt($curl_handle, CURLOPT_URL, 'https://ip-api.com/batch');
			curl_setopt($curl_handle, CURLOPT_HTTPHEADER,['Content-Type: application/json']);
			curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl_handle, CURLOPT_POST, true);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $data);
			$ip_query = curl_exec($curl_handle);
			curl_close($curl_handle);

			if (!empty($ip_query))
			{
				$ip_array = json_decode($ip_query, true);

				if ($ip_array[0]['status'] == 'success')
				{
					$proxy = $ip_array[0]['proxy'];
				}
			}
		}
		return $proxy;
	}

	public function get_ip()
	{
		$server = $this->request->get_super_global(\phpbb\request\request_interface::SERVER);

		if (isset($server['HTTP_CLIENT_IP']))
		{
			return $server['HTTP_CLIENT_IP'];
		}
		elseif (isset($server['HTTP_X_FORWARDED_FOR']))
		{
			return $server['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			return $server['REMOTE_ADDR'];
		}
	}

	public function ip_check()
	{
		$double_account = false;

		$ip	= $this->get_ip();

		$sql = 'SELECT user_id
			FROM ' . USERS_TABLE . '
			WHERE user_ip = "' . $this->db->sql_escape($ip) . '"
				AND user_type <> ' . USER_IGNORE . '
				UNION ALL
			SELECT session_user_id
			FROM ' . SESSIONS_TABLE . '
			WHERE session_ip = "' . $this->db->sql_escape($ip) . '"
				AND session_user_id <> ' . ANONYMOUS . '
				UNION ALL
			SELECT user_id
			FROM ' . SESSIONS_KEYS_TABLE . '
			WHERE last_ip = "' . $this->db->sql_escape($ip) . '"
				AND user_id <> ' . ANONYMOUS;
		$result = $this->db->sql_query($sql);

		while ($row = $this->db->sql_fetchrow($result))
		{
			$double_account = true;
		}
		$this->db->sql_freeresult($result);

		return $double_account;
	}

	public function cookie_check()
	{
		$cookie_account = '';

		if ($this->request->is_set($this->config['cookie_name'] . '_uc', \phpbb\request\request_interface::COOKIE))
		{
			$cookiedata = $this->request->variable($this->config['cookie_name'] . '_uc', '', true, \phpbb\request\request_interface::COOKIE);
			$user_cookie = '';

			$c_userdata = (!empty($cookiedata)) ? explode('|', $cookiedata) : '';
			$cookie_id = isset($c_userdata[0]) ? $c_userdata[0] : '';

			$sql = 'SELECT user_id FROM ' . USERS_TABLE . '
				WHERE user_id = ' . (int) $cookie_id . '
					AND user_id <> ' . ANONYMOUS;
			$result = $this->db->sql_query($sql);

			while ($row = $this->db->sql_fetchrow($result))
			{
				$cookie_account = true;
			}
			$this->db->sql_freeresult($result);
		}

		return $cookie_account;
	}
}
