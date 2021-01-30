<?php

include_once 'simplehtmldom/simple_html_dom.php';
include_once 'simplehtmldom/HtmlWeb.php';
include_once 'functions.php';

session_set_cookie_params(array("path" => dirname($_SERVER['PHP_SELF']) . '/'));
session_start();

if (!isset($_REQUEST['url'])) die("need url");
$url = $_REQUEST['url'];

// load the document
if (!isset($_REQUEST['forcerefresh']) && isset($_SESSION[$url])) {
    $doc = new simplehtmldom\HtmlDocument($_SESSION[$url]);
} else {
    // either want to force refresh or new URL
    $n = new simplehtmldom\HtmlWeb();
    $doc = $n->load($url);
    $_SESSION[$url] = "$doc"; // turn into a string
}

// figure out what to show
if (isset($_REQUEST['containersel']) && !empty($_REQUEST['containersel'])) {
    $root = $doc->find($_REQUEST['containersel'], 0);
    if (!$root) die("can't find that selector");
} else {
    $root = $doc->root;
}

// annotate with the selectors
foreach ($root->find("*") as $el) {
    $el->setAttribute("data-selector", compute_selector($root, $el));
}

// remove any javascript and potentially styles
foreach ($doc->find("script") as $el) $el->remove();
if (isset($_REQUEST['removestyles'])) {
    foreach ($doc->find("style") as $el) $el->remove();
    foreach ($doc->find("*[style]") as $el) $el->remove();
    foreach ($doc->find('link[rel="stylesheet"]') as $el) $el->remove();
}

// insert the scripts
/*
foreach (["https://code.jquery.com/jquery.min.js", absurl("proxystuff.js")] as $sc) {
    $scriptel = $doc->createElement("script");
    $scriptel->setAttribute("src", $sc);
    $scriptel->setAttribute("type", "text/javascript");
    $doc->root->find("head", 0)->appendChild($scriptel);
}
*/

// add a simple stylesheet
/*
$styleel = $doc->createElement("link");
$styleel->setAttribute("rel", "stylesheet");
$styleel->setAttribute("href", absurl("proxy_helper.css"));
$doc->root->find("head", 0)->appendChild($styleel);
*/

// add a base reference so relative links work
foreach ($doc->find("base") as $el) $el->remove(); // remove existing if any
$baseel = $doc->createElement("base");
$baseel->setAttribute("href", $url);
$doc->root->find("head", 0)->appendChild($baseel);

// output document
echo $root->innertext . PHP_EOL;

function compute_selector($root, $el) {
    $path = array();
    $continue = true;
    while ($continue && $el != null && $root != $el && $el->nodetype === \simplehtmldom\HtmlNode::HDOM_TYPE_ELEMENT) {    
        $selector = $el->tag;
        if ($el->hasAttribute("id")) {
            $selector = '#' . $el->getAttribute("id");
            $continue = false;
        } else {
            $sib = $el;
            $nth = 0;
            while ($sib->nodetype === \simplehtmldom\HtmlNode::HDOM_TYPE_ELEMENT
                    && ($sib = $sib->previousSibling())) {
                if ($sib->tag === $el->tag) $nth++;
            }
            
            // even if there is more than one, not appending `[0]` is fine since later on I just select the first matching element
            if ($nth > 0) $selector .= "[$nth]";
        }
        
        array_unshift($path, $selector);
        $el = $el->parent;
    }
    
    return implode(" > ", $path);
}