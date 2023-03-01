<?php
// Help extension, https://github.com/annaesvensson/yellow-help

class YellowHelp {
    const VERSION = "0.8.23";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("helpStartLocation", "auto");
        $this->yellow->system->setDefault("helpNewLocation", "@title");
        $this->yellow->system->setDefault("helpEntriesMax", "5");
        $this->yellow->system->setDefault("helpPaginationLimit", "5");
    }
    
    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if (substru($name, 0, 4)=="help" && ($type=="block" || $type=="inline")) {
            switch($name) {
                case "helpauthors": $output = $this->getShorcutHelpauthors($page, $name, $text); break;
                case "helppages":   $output = $this->getShorcutHelppages($page, $name, $text); break;
                case "helpchanges": $output = $this->getShorcutHelpchanges($page, $name, $text); break;
                case "helprelated": $output = $this->getShorcutHelprelated($page, $name, $text); break;
                case "helptags":    $output = $this->getShorcutHelptags($page, $name, $text); break;
                case "helpyears":   $output = $this->getShorcutHelpyears($page, $name, $text); break;
                case "helpmonths":  $output = $this->getShorcutHelpmonths($page, $name, $text); break;
            }
        }
        return $output;
    }
        
    // Return helpauthors shortcut
    public function getShorcutHelpauthors($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("helpStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("helpEntriesMax");
        $helpStart = $this->yellow->content->find($startLocation);
        $pages = $this->getHelpPages($startLocation);
        $page->setLastModified($pages->getModified());
        $authors = $this->getMeta($pages, "author");
        if (!is_array_empty($authors)) {
            $authors = $this->yellow->lookup->normaliseArray($authors);
            if ($entriesMax!=0 && count($authors)>$entriesMax) {
                uasort($authors, "strnatcasecmp");
                $authors = array_slice($authors, -$entriesMax, $entriesMax, true);
            }
            uksort($authors, "strnatcasecmp");
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($authors as $key=>$value) {
                $output .= "<li><a href=\"".$helpStart->getLocation(true).$this->yellow->lookup->normaliseArguments("author:$key")."\">";
                $output .= htmlspecialchars($key)."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Helpauthors '$startLocation' does not exist!");
        }
        return $output;
    }

    // Return helppages shortcut
    public function getShorcutHelppages($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax, $filterTag) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("helpStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("helpEntriesMax");
        $pages = $this->getHelpPages($startLocation);
        if (!is_string_empty($filterTag)) $pages->filter("tag", $filterTag);
        $pages->sort("title");
        $page->setLastModified($pages->getModified());
        if (!is_array_empty($pages)) {
            if ($entriesMax!=0) $pages->limit($entriesMax);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($pages as $pageHelp) {
                $output .= "<li><a".($pageHelp->isExisting("tag") ? " class=\"".$this->getClass($pageHelp)."\"" : "");
                $output .=" href=\"".$pageHelp->getLocation(true)."\">".$pageHelp->getHtml("title")."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Helppages '$startLocation' does not exist!");
        }
        return $output;
    }
    
    // Return helpchanges shortcut
    public function getShorcutHelpchanges($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax, $filterTag) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("helpStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("helpEntriesMax");
        $pages = $this->getHelpPages($startLocation);
        if (!is_string_empty($filterTag)) $pages->filter("tag", $filterTag);
        $pages->sort("published", false);
        $page->setLastModified($pages->getModified());
        if (!is_array_empty($pages)) {
            if ($entriesMax!=0) $pages->limit($entriesMax);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($pages as $pageHelp) {
                $output .= "<li><a".($pageHelp->isExisting("tag") ? " class=\"".$this->getClass($pageHelp)."\"" : "");
                $output .=" href=\"".$pageHelp->getLocation(true)."\">".$pageHelp->getHtml("title")."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Helpchanges '$startLocation' does not exist!");
        }
        return $output;
    }
        
    // Return helprelated shortcut
    public function getShorcutHelprelated($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("helpStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("helpEntriesMax");
        $pages = $this->getHelpPages($startLocation);
        $pages->similar($page->getPage("main"));
        $page->setLastModified($pages->getModified());
        if (!is_array_empty($pages)) {
            if ($entriesMax!=0) $pages->limit($entriesMax);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($pages as $pageHelp) {
                $output .= "<li><a".($pageHelp->isExisting("tag") ? " class=\"".$this->getClass($pageHelp)."\"" : "");
                $output .= " href=\"".$pageHelp->getLocation(true)."\">".$pageHelp->getHtml("title")."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Helprelated '$startLocation' does not exist!");
        }
        return $output;
    }
    
    // Return helptags shortcut
    public function getShorcutHelptags($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("helpStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("helpEntriesMax");
        $helpStart = $this->yellow->content->find($startLocation);
        $pages = $this->getHelpPages($startLocation);
        $page->setLastModified($pages->getModified());
        $tags = $this->getMeta($pages, "tag");
        if (!is_array_empty($tags)) {
            $tags = $this->yellow->lookup->normaliseArray($tags);
            if ($entriesMax!=0 && count($tags)>$entriesMax) {
                uasort($tags, "strnatcasecmp");
                $tags = array_slice($tags, -$entriesMax, $entriesMax, true);
            }
            uksort($tags, "strnatcasecmp");
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($tags as $key=>$value) {
                $output .= "<li><a href=\"".$helpStart->getLocation(true).$this->yellow->lookup->normaliseArguments("tag:$key")."\">";
                $output .= htmlspecialchars($key)."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Helptags '$startLocation' does not exist!");
        }
        return $output;
    }

    // Return helpyears shortcut
    public function getShorcutHelpyears($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("helpStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("helpEntriesMax");
        $helpStart = $this->yellow->content->find($startLocation);
        $pages = $this->getHelpPages($startLocation);
        $page->setLastModified($pages->getModified());
        $years = $this->getYears($pages, "published");
        if (!is_array_empty($years)) {
            if ($entriesMax!=0) $years = array_slice($years, -$entriesMax, $entriesMax, true);
            uksort($years, "strnatcasecmp");
            $years = array_reverse($years, true);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($years as $key=>$value) {
                $output .= "<li><a href=\"".$helpStart->getLocation(true).$this->yellow->lookup->normaliseArguments("published:$key")."\">";
                $output .= htmlspecialchars($key)."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Helpyears '$startLocation' does not exist!");
        }
        return $output;
    }
    
    // Return helpmonths shortcut
    public function getShorcutHelpmonths($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("helpStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("helpEntriesMax");
        $helpStart = $this->yellow->content->find($startLocation);
        $pages = $this->getHelpPages($startLocation);
        $page->setLastModified($pages->getModified());
        $months = $this->getMonths($pages, "published");
        if (!is_array_empty($months)) {
            if ($entriesMax!=0) $months = array_slice($months, -$entriesMax, $entriesMax, true);
            uksort($months, "strnatcasecmp");
            $months = array_reverse($months, true);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($months as $key=>$value) {
                $output .= "<li><a href=\"".$helpStart->getLocation(true).$this->yellow->lookup->normaliseArguments("published:$key")."\">";
                $output .= htmlspecialchars($this->yellow->language->normaliseDate($key))."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Helpmonths '$startLocation' does not exist!");
        }
        return $output;
    }
    
    // Handle page layout
    public function onParsePageLayout($page, $name) {
        if ($name=="help-start") {
            $pages = $this->getHelpPages($page->location);
            $pagesFilter = array();
            if ($page->isRequest("tag")) {
                $pages->filter("tag", $page->getRequest("tag"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("author")) {
                $pages->filter("author", $page->getRequest("author"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("published")) {
                $pages->filter("published", $page->getRequest("published"), false);
                array_push($pagesFilter, $this->yellow->language->normaliseDate($pages->getFilter()));
            }
            $pages->sort("published", false);
            if (!is_array_empty($pagesFilter)) {
                $text = implode(" ", $pagesFilter);
                $page->set("titleHeader", $text." - ".$page->get("sitename"));
                $page->set("titleContent", $page->get("title").": ".$text);
                $page->set("title", $page->get("title").": ".$text);
                $page->set("helpWithFilter", true);
            }
            $page->setPages("help", $pages);
            $page->setLastModified($pages->getModified());
            $page->setHeader("Cache-Control", "max-age=60");
        }
        if ($name=="help") {
            $helpStartLocation = $this->yellow->system->get("helpStartLocation");
            if ($helpStartLocation!="auto") {
                $helpStart = $this->yellow->content->find($helpStartLocation);
            } else {
                $helpStart = $page->getParent();
            }
            $page->setPage("helpStart", $helpStart);
        }
    }
    
    // Handle content file editing
    public function onEditContentFile($page, $action, $email) {
        if ($page->get("layout")=="help") $page->set("editNewLocation", $this->yellow->system->get("helpNewLocation"));
    }

    // Return help pages
    public function getHelpPages($location) {
        $pages = $this->yellow->content->clean();
        $helpStart = $this->yellow->content->find($location);
        if ($helpStart && $helpStart->get("layout")=="help-start") {
            if ($this->yellow->system->get("helpStartLocation")!="auto") {
                $pages = $this->yellow->content->index();
            } else {
                $pages = $helpStart->getChildren();
            }
            $pages->filter("layout", "help");
        }
        return $pages;
    }
    
    // Return class for page
    public function getClass($page) {
        $class = "";
        if ($page->isExisting("tag")) {
            foreach (preg_split("/\s*,\s*/", $page->get("tag")) as $tag) {
                $class .= " tag-".$this->yellow->lookup->normaliseArguments($tag, false);
            }
        }
        return trim($class);
    }
    
    // Return meta data from page collection
    public function getMeta($pages, $key) {
        $data = array();
        foreach ($pages as $page) {
            if ($page->isExisting($key)) {
                foreach (preg_split("/\s*,\s*/", $page->get($key)) as $entry) {
                    if (!isset($data[$entry])) $data[$entry] = 0;
                    ++$data[$entry];
                }
            }
        }
        return $data;
    }
    
    // Return years from page collection
    public function getYears($pages, $key) {
        $data = array();
        foreach ($pages as $page) {
            if (preg_match("/^(\d+)\-/", $page->get($key), $matches)) {
                if (!isset($data[$matches[1]])) $data[$matches[1]] = 0;
                ++$data[$matches[1]];
            }
        }
        return $data;
    }
    
    // Return months from page collection
    public function getMonths($pages, $key) {
        $data = array();
        foreach ($pages as $page) {
            if (preg_match("/^(\d+\-\d+)\-/", $page->get($key), $matches)) {
                if (!isset($data[$matches[1]])) $data[$matches[1]] = 0;
                ++$data[$matches[1]];
            }
        }
        return $data;
    }
}
