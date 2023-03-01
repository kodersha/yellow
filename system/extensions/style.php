<?php class YellowStyle {
    const VERSION = "0.1";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }
    
    // Handle update
    public function onUpdate($action) {
        $fileName = $this->yellow->system->get("coreExtensionDirectory").$this->yellow->system->get("coreSystemFile");
        if ($action=="install") {
            $this->yellow->system->save($fileName, array("theme" => "style"));
        } elseif ($action=="uninstall" && $this->yellow->system->get("theme")=="style") {
            $this->yellow->system->save($fileName, array("theme" => $this->yellow->system->getDifferent("theme")));
        }
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
            $output .= "<script type=\"text/javascript\" src=\"{$extensionLocation}jquery.js\"></script>\n";
            $output .= "<script type=\"text/javascript\" src=\"{$extensionLocation}emerge.js\"></script>\n";
            $output .= "<script type=\"text/javascript\" src=\"{$extensionLocation}likely.js\"></script>\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}zoom.js\"></script>\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}template.js\"></script>\n";
        }
        return $output;
    }
}
