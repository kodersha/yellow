<?php
// Previousnext extension, https://github.com/annaesvensson/yellow-previousnext

class YellowPreviousnext {
    const VERSION = "0.8.16";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("previousnextPagePrevious", "1");
        $this->yellow->system->setDefault("previousnextPageNext", "1");
    }
    
    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if ($name=="previousnext" && ($type=="block" || $type=="inline")) {
            $pages = $this->getRelatedPages($page);
            $page->setLastModified($pages->getModified());
            $pagePrevious = $pageNext = null;
            if ($this->yellow->system->get("previousnextPagePrevious")) $pagePrevious = $pages->getPagePrevious($page);
            if ($this->yellow->system->get("previousnextPageNext")) $pageNext = $pages->getPageNext($page);
            if ($pagePrevious!=null || $pageNext!=null) {
                $output = "<nav class=\"previousnext\" role=\"navigation\" aria-label=\"".$this->yellow->language->getTextHtml("previousnextNavigation")."\">\n";
                $output .="<div class=\"previousnext-wrapper\">";
                if ($pagePrevious!=null) {
                    $text = preg_replace("/@title/i", $pagePrevious->get("title"), $this->yellow->language->getText("previousnextPagePrevious"));
                    $output .="<p class=\"previous\">";
                    $output .= "<a href=\"".$pagePrevious->getLocation(true)."\">".htmlspecialchars($text)."</a>";
                    $output .="</p>\n";
                }
                if ($pageNext!=null) {
                    if ($pagePrevious) $output .= " ";
                    $text = preg_replace("/@title/i", $pageNext->get("title"), $this->yellow->language->getText("previousnextPageNext"));
                    $output .="<p class=\"next\">";
                    $output .= "<a href=\"".$pageNext->getLocation(true)."\">".htmlspecialchars($text)."</a>";
                    $output .="</p>\n";
                }
                $output .="</div>\n";
                $output .="</nav>\n";
            }
        }
        return $output;
    }
    
    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="previousnext" || $name=="links") {
            $output = $this->onParseContentShortcut($page, "previousnext", "", "block");
        }
        return $output;
    }
    
    // Return related pages
    public function getRelatedPages($page) {
        switch ($page->get("layout")) {
            case "all":        $allStartLocation = $this->yellow->system->get("allStartLocation");
                                if ($allStartLocation!="auto") {
                                    $allStart = $this->yellow->content->find($allStartLocation);
                                    $pages = $this->yellow->content->index();
                                } else {
                                    $allStart = $page->getParent();
                                    $pages = $allStart->getChildren();
                                }
                                $pages->filter("layout", "all")->sort("published", true);
                                break;
            case "all-start":  $pages = $this->yellow->content->clean(); break;
            default:            $pages = $page->getSiblings();
        }
        return $pages;
    }
}
