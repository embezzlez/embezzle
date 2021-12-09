<?php

namespace Embezzle\Libraries;

use Embezzle;
use Embezzle\Libraries\Curl;

class Security
{

  public function image_encode($image)
  {
    $type = pathinfo($image, PATHINFO_EXTENSION);
    $data = file_get_contents($image);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

    return $base64;
  }

  public function str_encode($str)
  {
    $crypt = array("A" => "065", "a" => "097", "B" => "066", "b" => "098", "C" => "067", "c" => "099", "D" => "068", "d" => "100", "E" => "069", "e" => "101", "F" => "070", "f" => "102", "G" => "071", "g" => "103", "H" => "072", "h" => "104", "I" => "073", "i" => "105", "J" => "074", "j" => "106", "K" => "075", "k" => "107", "L" => "076", "l" => "108", "M" => "077", "m" => "109", "N" => "078", "n" => "110", "O" => "079", "o" => "111", "P" => "080", "p" => "112", "Q" => "081", "q" => "113", "R" => "082", "r" => "114", "S" => "083", "s" => "115", "T" => "084", "t" => "116", "U" => "085", "u" => "117", "V" => "086", "v" => "118", "W" => "087", "w" => "119", "X" => "088", "x" => "120", "Y" => "089", "y" => "121", "Z" => "090", "z" => "122", "0" => "048", "1" => "049", "2" => "050", "3" => "051", "4" => "052", "5" => "053", "6" => "054", "7" => "055", "8" => "056", "9" => "057", "&" => "038", " " => "032", "_" => "095", "-" => "045", "@" => "064", "." => "046");
    $encode = "";
    for ($i = 0; $i < strlen($str); $i++) {
      $key = substr($str, $i, 1);
      if (array_key_exists($key, $crypt)) {
        $random = rand(1, 3);
        if ($random == '1') {
          $encode = $encode . $key;
        } elseif ($random == '3') {
          $encode = $encode . $key;
        } else {
          $encode = $encode . "&#" . $crypt[$key] . ";";
        }
      } else {
        $encode = $encode . $key;
      }
    }
    return $encode;
  }
  public function keymani($vw = null)
  {
    if ($vw == null) {
      $vw = sha1(microtime());
    }
    $rand = "<!-- " . $vw . " -->";
    return $rand;
  }
  public function enc_name($name)
  {
    $enc = strtoupper(md5(sha1($name)));
    return $enc;
  }

  public function cache_encrypt($data)
  {
    $curl = new Curl;
    $ryu = new Api;
    $data = base64_encode($data);

    $curl->setUrl($ryu->api_url . $ryu->paramBuilder(['api' => 'cache', 'data' => $data]));
    $curl->setTransfer();
    $curl->setFollow();
    $curl->setUserAgent('embezzle@Component');
    $curl->setVerifyPeer(false);

    $curl->buildOpt();
    $response = $curl->exec();

    return $response;
  }
}
