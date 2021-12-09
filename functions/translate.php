<?php
global $lang_path;
$lang_path = dirname(__DIR__) . '/languages/';


/**
 * lang_enc
 *
 * @param  mixed $text
 * @return void
 * 
 *  Language  hash to identify text 
 */
function lang_enc($text)
{
    $text = strip_tags(stripslashes($text));
    $text = md5(sha1($text));
    return $text;
}

/**
 * translate
 *
 * @param  mixed $text
 * @param  mixed $to
 * @param  mixed $from
 * @return void
 * 
 * Every text of language is encrypted to md5(sha1($language)) 
 * Save to ../languages/[Lang_Code].ini
 */
function translate($text , $to,$satu=0)
{global $lang_path;
    $to = strtolower($to);
    $key = lang_enc($text);

    $lang_file_format = $to.'-lang.ryujin.ini';
    if(file_exists($lang_path . $lang_file_format))
    {
        $parse = parse_ini_file($lang_path . $lang_file_format);
        if(array_key_exists($key, $parse))
        {
            $result = $parse[$key];
        }else{
            $result = $text;
        }
    }else{
        $result = $text;
    }
    if($satu == 1){
    return str_enc($result);
    }else{
      return lettering($result);
    }
}

/**
 * str_enc
 *
 * @param  mixed $str
 * @return void
 */
function str_enc($str) {
    $crypt = array(
      "A" => "065",
      "a" => "097",
      "B" => "066",
      "b" => "098",
      "C" => "067",
      "c" => "099",
      "D" => "068",
      "d" => "100",
      "E" => "069",
      "e" => "101",
      "F" => "070",
      "f" => "102",
      "G" => "071",
      "g" => "103",
      "H" => "072",
      "h" => "104",
      "I" => "073",
      "i" => "105",
      "J" => "074",
      "j" => "106",
      "K" => "075",
      "k" => "107",
      "L" => "076",
      "l" => "108",
      "M" => "077",
      "m" => "109",
      "N" => "078",
      "n" => "110",
      "O" => "079",
      "o" => "111",
      "P" => "080",
      "p" => "112",
      "Q" => "081",
      "q" => "113",
      "R" => "082",
      "r" => "114",
      "S" => "083",
      "s" => "115",
      "T" => "084",
      "t" => "116",
      "U" => "085",
      "u" => "117",
      "V" => "086",
      "v" => "118",
      "W" => "087",
      "w" => "119",
      "X" => "088",
      "x" => "120",
      "Y" => "089",
      "y" => "121",
      "Z" => "090",
      "z" => "122",
      "0" => "048",
      "1" => "049",
      "2" => "050",
      "3" => "051",
      "4" => "052",
      "5" => "053",
      "6" => "054",
      "7" => "055",
      "8" => "056",
      "9" => "057",
      "&" => "038",
      " " => "032",
      "_" => "095",
      "-" => "045",
      "@" => "064",
      "." => "046"
    );
    $encode = "";
    for ($i=0; $i < strlen($str); $i++) {
      $key = substr($str, $i, 1);
      if (array_key_exists($key, $crypt)) {
        $random = rand(1, 3);
     /*   if ($random == '1') {
          $encode = $encode.$key;
        } else if ($random == '3') {
          $encode = $encode.$key;
        } else {*/
          $encode = $encode."&#".$crypt[$key].";";
       /* }*/
      } else {
        $encode = $encode.$key;
      }
    }
    return $encode;
  }


function lettering($b)
{
  $kata = explode(" ",$b);
  $new = '';
  foreach($kata as $word) {
    $new.= $word . ' <font style="font-size:0px">' .md5(time()) . '</font>';
    $new.= '<!-- '.strrev($b).' -->';
  }

  return $new;
}