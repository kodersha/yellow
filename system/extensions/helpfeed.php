<?php

class YellowHelpFeed {
    const VERSION = "0.8.16";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("helpFeedLocation", "/helpfeed/");
        $this->yellow->system->setDefault("helpFeedFileXml", "helpfeed.xml");
        $this->yellow->system->setDefault("helpFeedFilterLayout", "none");
        $this->yellow->system->setDefault("helpFeedPaginationLimit", "30");
    }

    // Handle page layout
    public function onParsePageLayout($page, $name) {
        if ($name=="help-feed") {
            $pages = $this->yellow->content->index(false, false);
            $pagesFilter = array();
            if ($page->isRequest("tag")) {
                $pages->filter("tag", $page->getRequest("tag"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("author")) {
                $pages->filter("author", $page->getRequest("author"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("language")) {
                $pages->filter("language", $page->getRequest("language"));
                array_push($pagesFilter, $pages->getFilter());
            }
            if ($page->isRequest("folder")) {
                $pages->match("#".$page->getRequest("folder")."#i", false);
                array_push($pagesFilter, ucfirst($page->getRequest("folder")));
            }
            $helpFeedFilterLayout = $this->yellow->system->get("helpFeedFilterLayout");
            if ($helpFeedFilterLayout!="none") $pages->filter("layout", $helpFeedFilterLayout);
            $chronologicalOrder = ($this->yellow->system->get("helpFeedFilterLayout")!="blog");
            if ($this->isRequestXml($page)) {
                $pages->sort($chronologicalOrder ? "modified" : "published", false);
                $entriesMax = $this->yellow->system->get("helpFeedPaginationLimit");
                if ($entriesMax==0 || $entriesMax>100) $entriesMax = 100;
                $pages->limit($entriesMax);
                $title = !is_array_empty($pagesFilter) ? implode(" ", $pagesFilter)." - ".$this->yellow->page->get("sitename") : $this->yellow->page->get("sitename");
                $this->yellow->page->setLastModified($pages->getModified());
                $this->yellow->page->setHeader("Content-Type", "application/rss+xml; charset=utf-8");
                $output = "<?xml version=\"1.0\" encoding=\"utf-8\"\077>\r\n";
                $output .= "<rss version=\"2.0\" xmlns:content=\"http://purl.org/rss/1.0/modules/content/\" xmlns:dc=\"http://purl.org/dc/elements/1.1/\">\r\n";
                $output .= "<channel>\r\n";
                $output .= "<title>".htmlspecialchars($title)."</title>\r\n";
                $output .= "<link>".$this->yellow->page->scheme."://".$this->yellow->page->address.$this->yellow->page->base."/"."</link>\r\n";
                $output .= "<description>".$this->yellow->page->getHtml("description")."</description>\r\n";
                $output .= "<language>".$this->yellow->page->getHtml("language")."</language>\r\n";
                foreach ($pages as $pageHelpFeed) {
                    $timestamp = strtotime($pageHelpFeed->get($chronologicalOrder ? "modified" : "published"));
                    $content = $this->yellow->toolbox->createTextDescription($pageHelpFeed->getContent(), 0, false, "<!--more-->", "<a href=\"".$pageHelpFeed->getUrl()."\">".$this->yellow->language->getTextHtml("blogMore")."</a>");
                    $output .= "<item>\r\n";
                    $output .= "<title>".$pageHelpFeed->getHtml("title")."</title>\r\n";
                    $output .= "<link>".$pageHelpFeed->getUrl()."</link>\r\n";
                    $output .= "<pubDate>".date(DATE_RSS, $timestamp)."</pubDate>\r\n";
                    $output .= "<guid isPermaLink=\"false\">".$pageHelpFeed->getUrl()."?".$timestamp."</guid>\r\n";
                    $output .= "<dc:creator>".$pageHelpFeed->getHtml("author")."</dc:creator>\r\n";
                    $output .= "<description>".$pageHelpFeed->getHtml("description")."</description>\r\n";
                    $output .= "<content:encoded><![CDATA[".$content."]]></content:encoded>\r\n";
                    $output .= "</item>\r\n";
                }
                $output .= "</channel>\r\n";
                $output .= "</rss>\r\n";
                $this->yellow->page->setOutput($output);
            } else {
                $pages->sort($chronologicalOrder ? "modified" : "published", false);
                if (!is_array_empty($pagesFilter)) {
                    $text = implode(" ", $pagesFilter);
                    $this->yellow->page->set("titleHeader", $text." - ".$this->yellow->page->get("sitename"));
                    $this->yellow->page->set("titleContent", $this->yellow->page->get("title").": ".$text);
                    $this->yellow->page->set("title", $this->yellow->page->get("title").": ".$text);
                }
                $this->yellow->page->set("helpFeedChronologicalOrder", $chronologicalOrder);
                $this->yellow->page->setPages("helpFeed", $pages);
                $this->yellow->page->setLastModified($pages->getModified());
            }
        }
    }

    // Check if XML requested
    public function isRequestXml($page) {
        return $page->getRequest("page")==$this->yellow->system->get("helpFeedFileXml");
    }
}
