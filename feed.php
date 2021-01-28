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

$channel->addChild('title', strip_tags($doc->find("title", 0)) ?? "Feed");
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
    $el->attrs = array_diff_key($el->attrs, array_flip($scriptattrs));
}

// find container element
foreach ($doc->find($_GET['containersel']) as $container) {
    $item = $channel->addChild('item');
    
    // find title
    $title = $container->find($_GET['titlesel'], 0);
    $titlestr = $title != null ? htmlentities(strip_tags($title->innertext)) : "No title";
    $item->addChild('title', $titlestr);
    
    // find link
    if ($title) {
        $link = $title->hasAttribute("href") ? $title : $title->find("a", 0);
        $actualurl = getAbsoluteUrl($_GET['url'], $link->getAttribute("href") ?? $_GET['url']);
        $item->addChild('link', $actualurl);
        $title->remove();
        $link->remove();
    } else {
        $item->addChild('link', $_GET['url']);
    }
    
    // find date
    $date = $container->find($_GET['datesel'], 0);
    if ($date) {
        $dateval = date_create_from_format($_GET['datefmt'], strip_tags($date->innertext));
        $date_rfc = gmdate(DATE_RFC2822, $dateval->getTimestamp());
        $item->addChild('pubDate', $date_rfc);
        $date->remove();
    }
    
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
            $contentstr .= htmlentities($el->outertext);
        }
    } else {
        $contentstr = htmlentities($container->innertext);
    }
    $item->addChild('description', $contentstr);
    
    // create guid
    $guid = $item->addChild('guid', sha1($item->asXML()));
    $guid->addAttribute('isPermaLink', 'false');
}

unset($doc);

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
    echo $xsl->transformToXml(dom_import_simplexml($rss));
} else {
    header('Content-Type: text/xml; charset=utf-8', true);
    echo $rss->asXML();
}
