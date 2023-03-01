<?php
// Player extension, experimental

class YellowPlayer {
    const VERSION = "0.8.20";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name=="player" && ($type=="inline" || $type=="block")) {
            if ($this->yellow->extension->isExisting("image")) {
                list($name, $caption, $style, $width, $height) = $this->yellow->toolbox->getTextArguments($text);
                if (!preg_match("/^\w+:/", $name)) {
                    if (empty($width)) $width = "100%";
                    if (empty($height)) $height = $width;
                    $path = $this->yellow->lookup->findMediaDirectory("coreImageLocation");
                    list($src, $width, $height) = $this->yellow->extension->get("image")->getImageInformation($path.$name, $width, $height);
                } else {
                    $src = $this->yellow->lookup->normaliseUrl("", "", "", $name);
                    $width = $height = 0;
                }

                if (empty($style)) $output = "<div class=\"player emerge\" data-expose=\"true\"";
                if (!empty($style)) $output .= "<div class=\"player ".htmlspecialchars($style)." emerge\" data-expose=\"true\"";
                if (!empty($caption)) $output .= " data-video-id=\"".htmlspecialchars($caption)."\"";
                $output .= " >";
                    $output .= "<div class=\"picture\">";
                    $output .= "<img src=\"".htmlspecialchars($src)."\"";
                    if ($width && $height) $output .= " width=\"".htmlspecialchars($width)."\" height=\"".htmlspecialchars($height)."\"";
                    $output .= " />";
                    $output .= "</div>";
                $output .= "</div>";
            } else {
                $page->error(500, "Player requires 'image' extension!");
            }
        }
        return $output;
    }
}