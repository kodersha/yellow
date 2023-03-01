<?php
// Transliterate extension, experimental

class YellowTransliterate {
    const VERSION = "0.8.17";
    public $yellow;         //access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }
    
    // Handle content file editing
    public function onEditContentFile($page, $action, $email) {
        if (!$page->isExisting("titleSlug")) {
            $replaceData = $this->getReplaceData();
            $titleSlug = str_replace(array_keys($replaceData), array_values($replaceData), $page->get("title"));
            $titleSlug = preg_replace("/[^\pL\d\-\_\ ]/u", "-", $titleSlug);
            $page->rawData = $this->yellow->toolbox->setMetaData($page->rawData, "titleSlug", $titleSlug);
        }
    }
    
    // Return text replace data, UTF8 to ASCII
    public function getReplaceData() {
        return array(
            "А" => "a", "Б" => "b", "В" => "v", "Г" => "g", "Д" => "d", "Е" => "e", "Ё" => "yo", "Ж" => "zh",
            "З" => "z", "И" => "i", "Й" => "y", "К" => "k", "Л" => "l", "М" => "m", "Н" => "n", "О" => "o",
            "П" => "p", "Р" => "r", "С" => "s", "Т" => "t", "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "c",
            "Ч" => "ch", "Ш" => "sh", "Щ" => "sc", "Ъ" => "", "Ы" => "", "Ь" => "", "Э" => "e", "Ю" => "yu",
            "Я" => "ya",
            "а" => "a", "б" => "b", "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ё" => "yo", "ж" => "zh",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l", "м" => "m", "н" => "n", "о" => "o",
            "п" => "p", "р" => "r", "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h", "ц" => "c",
            "ч" => "ch", "ш" => "sh", "щ" => "sc", "ъ" => "", "ы" => "", "ь" => "", "э" => "e", "ю" => "yu",
            "я" => "ya",
            "A" => "a", "B" => "b", "C" => "c", "D" => "d", "E" => "e", "F" => "f", "G" => "g", "H" => "h",
            "I" => "i", "J" => "j", "K" => "k", "L" => "l", "M" => "m", "N" => "n", "O" => "o", "P" => "p",
            "Q" => "q", "R" => "r", "S" => "s", "T" => "t", "U" => "u", "V" => "v", "W" => "w", "X" => "x",
            "Y" => "y", "Z" => "z",
            "0" => "0", "1" => "1", "2" => "2", "3" => "3", "4" => "4", "5" => "5", "6" => "6", "7" => "7",
            "8" => "8", "9" => "9",
            "сы" => "sy", "ые" => "ye",
            " " => "-", "," => "", "." => "", '"' => "", ":" => "", "!" => "", "?" => "",
            // Add more characters here, see also: 
            // https://gist.github.com/sgmurphy/3098978
            // https://github.com/ausi/slug-generator/blob/master/src/Resources/Latin-ASCII.txt
            // https://github.com/jbroadway/urlify/blob/master/URLify.php
        );
    }
}