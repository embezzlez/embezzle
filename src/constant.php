<?php



define('SEPARATOR',DIRECTORY_SEPARATOR);
define('ROOT_PATH'  , dirname(dirname(dirname(dirname(__DIR__)))));
define('PUBLIC_PATH',ROOT_PATH .'/public/');
define('APP_PATH',ROOT_PATH . '/app/');
define('CORE_PATH',dirname(__DIR__).'/src/');
define('LANG_PATH',dirname(__DIR__) .'/src/languages/');
define('BOT_PATH',dirname(__DIR__).'/src/bots/');
define('FUNC_PATH',dirname(__DIR__) .'/src/functions/');
define('LIB_PATH',dirname(__DIR__) .'/src/libraries/');

define('REQ_PATH', ROOT_PATH .'/app/request/');
define('PAGE_PATH', ROOT_PATH.'/app/pages/');
define('CONFIG_PATH', ROOT_PATH .'/app/config/');
define('API_PATH',ROOT_PATH . '/app/api/');
