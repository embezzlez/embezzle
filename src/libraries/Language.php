<?php

namespace Embezzle\Libraries;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Embezzle\Libraries\Env;

class Language
{
    public $lang_path;

    public function __construct()
    {
        $this->lang_path = APP_PATH . '/languages/';
    }

    public function hash_lang($text, $lang)
    {
        $d = array('-', '/', '\\', ',', '.', '#', ':', ';', '\'', '"', '[', ']', '{', '}', ')', '(', '|', '`', '~', '!', '@', '%', '$', '^', '&', '*', '=', '?', '+');
        $format = str_replace($d, '', str_replace(' ', '_', (strtoupper($text))));
        $hash =  strtoupper($lang) . '_' . $format;

        return $hash;
    }
    private function lang_filename($to)
    {
        $app = strtolower(str_replace(" ", "_", CONFIG['web']['app_name']));
        return $app . '_' . $to . '.env';
    }
    public function init($text, $to, $from = 'en')
    {
        $filename = $this->lang_filename($to);

        if (!file_exists($this->lang_path . $filename)) {
            $content = "# TRANSLATED BY GOOGLE TRANSLATE | EMBEZZLE@COMPONENT \n";
            $content .= "# NAMESPACE \Embezzle\Libraries\Language \n";
            $content .= "# LANGUAGE GENERATED DATE : " . date('D,d-m-Y H:i') . "\n\n";
            $content .= "LANGUANGE = " . strtoupper($to) . "\n\n";

            file_put_contents($this->lang_path . $filename, $content);
        }
        $lang_env = new Env($this->lang_path . $filename);
        $lang_env->load();

        $hash = $this->hash_lang($text, $to);
        try {
            if (!empty(getenv($hash))) {
                return getenv($hash);
            } else {
                $gtranslate = new GoogleTranslate();
                $gtranslate->setSource($from);
                $gtranslate->setTarget($to);
                $result = $gtranslate->translate($text);
                file_put_contents($this->lang_path . $filename, "{$hash} = {$result}\n", FILE_APPEND);
                return $result;
            }
        } catch (\Exception $e) {
            return $text;
        }
    }
}
