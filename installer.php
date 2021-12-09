<?php


class Init
{
   private $htaccess_dir;
   private $basedir;
   public function __construct()
   {
      $this->htaccess_dir = __DIR__ . '/htaccess.txt';
      $this->basedir = __DIR__;
   }

   private function getDomain()
   {
      $domain = $_SERVER['SERVER_NAME'];
      $domain = ($domain == '127.0.0.1') ? 'localhost' : $domain;
      return $domain;
   }
   private function htaccess()
   {
      @unlink($this->basedir . '/.htaccess');

      $__f = file_get_contents($this->htaccess_dir);
      $__d = str_replace("{domain}", $this->getDomain(), $__f);
      file_put_contents($this->basedir . '/public/.htaccess', $__d);

      return file_put_contents($this->basedir . '/.htaccess', $__d);
   }
   private function config()
   {

      $d = json_decode(file_get_contents($this->basedir . '/app/config/config.json'), true);
      return $d;
   }
   public function init()
   {

      $this->htaccess();
      $PARAM = $this->config()['parameter'];
      @unlink($this->basedir . '/index.php');
      echo "<META HTTP-EQUIV='REFRESH' CONTENT='1;URL=/?{$PARAM}' />";

      return;
   }
}

$INSTALL = new Init;
$INSTALL->init();
