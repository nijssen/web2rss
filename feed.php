<?php

include_once 'simplehtmldom/simple_html_dom.php';
include_once 'simplehtmldom/HtmlWeb.php';
include_once 'functions.php';

//$doc = file_get_html($_GET['url']);
$n = new simplehtmldom\HtmlWeb();
$doc = $n->load($_GET['url']);
//$doc = $n->load_fopen($_GET['url']);

// setup feed output
$rss = new SimpleXMLElement('<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"></rss>');
$rss->addAttribute('version', '2.0');

$channel = $rss->addChild('channel');

$channel->addChild('title', htmlentities2(strip_tags($doc->find("title", 0))) ?? "Feed");
$channel->addChild('link', $_GET['url']);

// remove scripts
foreach ($doc->find("script") as $el) $el->remove();
$scriptattrs = [
    "onload", "onunload", "onclick", "ondblclick", "onmousedown", "onmouseup",
    "onmouseover", "onmousemove", "onmouseout", "onfocus", "onblur", "onkeypress",
    "onkeydown", "onkeyup", "onsubmit", "onreset", "onselect", "onchange"
];
$scriptattrstr = implode(", ", array_map(function ($x) { return "*[$x]"; }, $scriptattrs));
foreach ($doc->find($scriptattrstr) as $el) {
    if ($el->attrs)
        $el->attrs = array_diff_key($el->attrs, array_flip($scriptattrs));
}

// find container element
foreach ($doc->find($_GET['containersel']) as $container) {
    $item = $channel->addChild('item');
    
    // make links and image sources absolute
    foreach ($container->find("a, img") as $el) {
        foreach (["href", "src"] as $attr) {
            if ($el->hasAttribute($attr)) {
                $el->setAttribute($attr, getAbsoluteUrl($_GET['url'], $el->getAttribute($attr)));
            }
        }
    }
    
    // find title
    $title = $container->find($_GET['titlesel'], 0);
    $titlestr = $title != null ? htmlentities2(strip_tags($title->innertext)) : "No title";
    $item->addChild('title', $titlestr);
    
    // find link
    if ($title) {
        if ($title->hasAttribute("href")) {
            $link = $title;
        } else if ($_GET['usefirstlink']) {
            $link = $container->find("a", 0);
        } else {
            $link = $title->find("a", 0);
        }
        
        if ($link != null && $link->hasAttribute("href")) {
            //$actualurl = getAbsoluteUrl($_GET['url'], $link->getAttribute("href"));
            $actualurl = $link->getAttribute("href");
        } else {
            $actualurl = $_GET['url'];
        }
    } else {
        $actualurl = $_GET['url'];
    }
    $item->addChild('link', htmlentities2($actualurl));
    
    // find date
    $date = $container->find($_GET['datesel'], 0);
    if ($date) {
        if ($date->tag == "time" && $date->hasAttribute("datetime")) {
            $datestr = $date->getAttribute("datetime");
        } else {
            $datestr = trim(strip_tags($date->innertext));
        }
        $dateval = date_create_from_format($_GET['datefmt'], $datestr);
        $maybe_error = date_get_last_errors();
        if ($maybe_error && $maybe_error['error_count'] > 0) {
            print_r($maybe_error);
            die();
        }
        $date_rfc = gmdate(DATE_RFC2822, $dateval->getTimestamp());
        $item->addChild('pubDate', $date_rfc);
    }
    
    if ($title) $title->remove();
    if ($link) $link->remove();
    if ($date) $date->remove();
    
    // remove other things we want gone
    if (isset($_GET['removesel']) && is_array($_GET['removesel'])) {
        foreach ($_GET['removesel'] as $removesel) {
            foreach ($container->find($removesel) as $el) {
                $el->remove();
            }
        }
    }
    
    // is the content the remainder or a specific thing?
    $contentstr = "";
    if (isset($_GET['contentsel']) && !empty($_GET['contentsel'])) {
        foreach ($container->find($_GET['contentsel']) as $el) {
            $contentstr .= $el->outertext;
        }
    } else {
        $contentstr = $container->innertext;
    }
    
    $item->addChild('description', htmlentities2($contentstr));
    
    // create guid
    $guid = $item->addChild('guid', sha1($item->asXML()));
    $guid->addAttribute('isPermaLink', 'false');
}

unset($doc);

$xmldoc = new DomDocument("1.0", "UTF-8");
$xmldoc->appendChild($xmldoc->importNode(dom_import_simplexml($rss), true));

if (isset($_GET['preview'])) {
    // setup XSL
    $xsl = new XSLTProcessor;
    $xsl->registerPHPFunctions();
    $xsldoc = new DomDocument();
    $xsldoc->load("rss2html.xsl");
    $xsl->importStyleSheet($xsldoc);
    
    // convert rss to DOM
    //$xmldoc = new DomDocument();
    //$xmldoc->appendChild($xmldoc->importNode(dom_import_simplexml($rss), true));
    
    // transform
    //echo $xsl->transformToXml($rss);
    echo $xsl->transformToXml($xmldoc);
} else {
    header('Content-Type: text/xml; charset=utf-8', true);
    //echo $rss->asXML();
    echo $xmldoc->saveXML();
}


function htmlentities2($text) {
    return htmlentities($text, ENT_XML1 | ENT_DISALLOWED, "UTF-8");
}