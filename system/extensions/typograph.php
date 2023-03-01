<?php

class YellowTypograph {
    const VERSION = "0.8.21";
    public $yellow; // access to API

    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    public function onParseContentHtml($page, $text) {
        require_once("typograph-emt.php"); 

        $output = null;
        $content = $text;

        $typograph = new EMTypograph();
        $options = [
            "Text.paragraphs" => "off",
            "Text.breakline" => "off",
            "OptAlign.oa_oquote" => "off",
            "OptAlign.oa_obracket_coma" => "off",
        ];
        $typograph->setup($options);
        $typograph->set_text($content);
        $result = $typograph->apply();
        return $result;
    }
}