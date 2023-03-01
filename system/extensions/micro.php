<?php
// Micro extension, https://github.com/annaesvensson/yellow-micro

class YellowMicro {
    const VERSION = "0.8.23";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("microStartLocation", "auto");
        $this->yellow->system->setDefault("microNewLocation", "@title");
        $this->yellow->system->setDefault("microEntriesMax", "5");
        $this->yellow->system->setDefault("microPaginationLimit", "5");
    }
    
    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if (substru($name, 0, 4)=="micro" && ($type=="block" || $type=="inline")) {
            switch($name) {
                case "microauthors": $output = $this->getShorcutMicroauthors($page, $name, $text); break;
                case "micropages":   $output = $this->getShorcutMicropages($page, $name, $text); break;
                case "microchanges": $output = $this->getShorcutMicrochanges($page, $name, $text); break;
                case "microrelated": $output = $this->getShorcutMicrorelated($page, $name, $text); break;
                case "microtags":    $output = $this->getShorcutMicrotags($page, $name, $text); break;
                case "microyears":   $output = $this->getShorcutMicroyears($page, $name, $text); break;
                case "micromonths":  $output = $this->getShorcutMicromonths($page, $name, $text); break;
            }
        }
        return $output;
    }
        
    // Return microauthors shortcut
    public function getShorcutMicroauthors($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("microStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("microEntriesMax");
        $microStart = $this->yellow->content->find($startLocation);
        $pages = $this->getMicroPages($startLocation);
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
                $output .= "<li><a href=\"".$microStart->getLocation(true).$this->yellow->lookup->normaliseArguments("author:$key")."\">";
                $output .= htmlspecialchars($key)."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Microauthors '$startLocation' does not exist!");
        }
        return $output;
    }

    // Return micropages shortcut
    public function getShorcutMicropages($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax, $filterTag) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("microStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("microEntriesMax");
        $pages = $this->getMicroPages($startLocation);
        if (!is_string_empty($filterTag)) $pages->filter("tag", $filterTag);
        $pages->sort("title");
        $page->setLastModified($pages->getModified());
        if (!is_array_empty($pages)) {
            if ($entriesMax!=0) $pages->limit($entriesMax);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($pages as $pageMicro) {
                $output .= "<li><a".($pageMicro->isExisting("tag") ? " class=\"".$this->getClass($pageMicro)."\"" : "");
                $output .=" href=\"".$pageMicro->getLocation(true)."\">".$pageMicro->getHtml("title")."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Micropages '$startLocation' does not exist!");
        }
        return $output;
    }
    
    // Return microchanges shortcut
    public function getShorcutMicrochanges($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax, $filterTag) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("microStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("microEntriesMax");
        $pages = $this->getMicroPages($startLocation);
        if (!is_string_empty($filterTag)) $pages->filter("tag", $filterTag);
        $pages->sort("published", false);
        $page->setLastModified($pages->getModified());
        if (!is_array_empty($pages)) {
            if ($entriesMax!=0) $pages->limit($entriesMax);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($pages as $pageMicro) {
                $output .= "<li><a".($pageMicro->isExisting("tag") ? " class=\"".$this->getClass($pageMicro)."\"" : "");
                $output .=" href=\"".$pageMicro->getLocation(true)."\">".$pageMicro->getHtml("title")."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Microchanges '$startLocation' does not exist!");
        }
        return $output;
    }
        
    // Return microrelated shortcut
    public function getShorcutMicrorelated($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("microStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("microEntriesMax");
        $pages = $this->getMicroPages($startLocation);
        $pages->similar($page->getPage("main"));
        $page->setLastModified($pages->getModified());
        if (!is_array_empty($pages)) {
            if ($entriesMax!=0) $pages->limit($entriesMax);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($pages as $pageMicro) {
                $output .= "<li><a".($pageMicro->isExisting("tag") ? " class=\"".$this->getClass($pageMicro)."\"" : "");
                $output .= " href=\"".$pageMicro->getLocation(true)."\">".$pageMicro->getHtml("title")."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Microrelated '$startLocation' does not exist!");
        }
        return $output;
    }
    
    // Return microtags shortcut
    public function getShorcutMicrotags($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("microStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("microEntriesMax");
        $microStart = $this->yellow->content->find($startLocation);
        $pages = $this->getMicroPages($startLocation);
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
                $output .= "<li><a href=\"".$microStart->getLocation(true).$this->yellow->lookup->normaliseArguments("tag:$key")."\">";
                $output .= htmlspecialchars($key)."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Microtags '$startLocation' does not exist!");
        }
        return $output;
    }

    // Return microyears shortcut
    public function getShorcutMicroyears($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("microStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("microEntriesMax");
        $microStart = $this->yellow->content->find($startLocation);
        $pages = $this->getMicroPages($startLocation);
        $page->setLastModified($pages->getModified());
        $years = $this->getYears($pages, "published");
        if (!is_array_empty($years)) {
            if ($entriesMax!=0) $years = array_slice($years, -$entriesMax, $entriesMax, true);
            uksort($years, "strnatcasecmp");
            $years = array_reverse($years, true);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($years as $key=>$value) {
                $output .= "<li><a href=\"".$microStart->getLocation(true).$this->yellow->lookup->normaliseArguments("published:$key")."\">";
                $output .= htmlspecialchars($key)."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Microyears '$startLocation' does not exist!");
        }
        return $output;
    }
    
    // Return micromonths shortcut
    public function getShorcutMicromonths($page, $name, $text) {
        $output = null;
        list($startLocation, $entriesMax) = $this->yellow->toolbox->getTextArguments($text);
        if (is_string_empty($startLocation)) $startLocation = $this->yellow->system->get("microStartLocation");
        if (is_string_empty($entriesMax)) $entriesMax = $this->yellow->system->get("microEntriesMax");
        $microStart = $this->yellow->content->find($startLocation);
        $pages = $this->getMicroPages($startLocation);
        $page->setLastModified($pages->getModified());
        $months = $this->getMonths($pages, "published");
        if (!is_array_empty($months)) {
            if ($entriesMax!=0) $months = array_slice($months, -$entriesMax, $entriesMax, true);
            uksort($months, "strnatcasecmp");
            $months = array_reverse($months, true);
            $output = "<div class=\"".htmlspecialchars($name)."\">\n";
            $output .= "<ul>\n";
            foreach ($months as $key=>$value) {
                $output .= "<li><a href=\"".$microStart->getLocation(true).$this->yellow->lookup->normaliseArguments("published:$key")."\">";
                $output .= htmlspecialchars($this->yellow->language->normaliseDate($key))."</a></li>\n";
            }
            $output .= "</ul>\n";
            $output .= "</div>\n";
        } else {
            $page->error(500, "Micromonths '$startLocation' does not exist!");
        }
        return $output;
    }
    
    // Handle page layout
    public function onParsePageLayout($page, $name) {
        if ($name=="micro-start") {
            $pages = $this->getMicroPages($page->location);
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
                $page->set("microWithFilter", true);
            }
            $page->setPages("micro", $pages);
            $page->setLastModified($pages->getModified());
            $page->setHeader("Cache-Control", "max-age=60");
        }
        if ($name=="micro") {
            $microStartLocation = $this->yellow->system->get("microStartLocation");
            if ($microStartLocation!="auto") {
                $microStart = $this->yellow->content->find($microStartLocation);
            } else {
                $microStart = $page->getParent();
            }
            $page->setPage("microStart", $microStart);
        }
    }
    
    // Handle content file editing
    public function onEditContentFile($page, $action, $email) {
        if ($page->get("layout")=="micro") $page->set("editNewLocation", $this->yellow->system->get("microNewLocation"));
    }

    // Return micro pages
    public function getMicroPages($location) {
        $pages = $this->yellow->content->clean();
        $microStart = $this->yellow->content->find($location);
        if ($microStart && $microStart->get("layout")=="micro-start") {
            if ($this->yellow->system->get("microStartLocation")!="auto") {
                $pages = $this->yellow->content->index();
            } else {
                $pages = $microStart->getChildren();
            }
            $pages->filter("layout", "micro");
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
