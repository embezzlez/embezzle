<?php

namespace Embezzle\Libraries;

class Router
{

	public $route_config;
	public function __construct()
	{
		$this->route_config = CONFIG['route'];
	}
	public function init($cfg, $page)
	{
		$num = array_search($page, $this->route_config);
		$next_page = $this->route_config[$num + 1];
		$route_config = $this->route_config['config'][$num + 1];

		if (isset($num)) {
			return $this->route_config($page, 1);
		} else {

			return CONFIG['app']['default_page'];
		}
	}
	public function route_config($page, $index = 1)
	{

		$num = array_search($page, $this->route_config);
		$next_page = $this->route_config[$num + 1];
		$route_config = $this->route_config['config'];
		if ($route_config[$num + $index] == '' || empty($route_config[$num + $index])) {
			return $this->route_config[$num + $index];
		} elseif ($this->is_on($route_config[$num + $index])) {
			return $this->route_config[$num + $index];
		} else {
			$index += 1;
			return $this->route_config($page, $index);
		}
	}
	public function is_on($cfg_name)
	{
		if (CONFIG['app'][$cfg_name] == 1) {
			return true;
		} else {
			return false;
		}
	}
}
