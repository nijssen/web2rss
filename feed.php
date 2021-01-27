<?php

include_once 'simplehtmldom/simple_html_dom.php';
include_once 'simplehtmldom/HtmlWeb.php';

//$doc = file_get_html($_GET['url']);
$n = new simplehtmldom\HtmlWeb();
$doc = $n->load($_GET['url']);

// setup feed output
$rss = new SimpleXMLElement('<rss xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:content="http://purl.org/rss/1.0/modules/content/"></rss>');
$rss->addAttribute('version', '2.0');

$channel = $rss->addChild('channel');

$rss->addChild('title', strip_tags($doc->find("title", 0)) ?? "Feed");
//$rss->addChild('description', 'description line goes here');
$rss->addChild('link', $_GET['url']);
//$rss->addChild('language', 'en-us');

// find container element
foreach ($doc->find($_GET['containersel']) as $container) {
    $item = $rss->addChild('item');
    
    // find title
    $title = $container->find($_GET['titlesel'], 0);
    $item->addChild('title', $title != null ? htmlentities(strip_tags($title->innertext)) : "No title");
    
    // find link
    if ($title) {
        $link = $title->hasAttribute("href") ? $title : $title->find("a", 0);
        $actualurl = convertRelativeToAbsolutePath($_GET['url'], $link->getAttribute("href") ?? $_GET['url']);
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


header('Content-Type: text/xml; charset=utf-8', true);
echo $rss->asXML();


function convertRelativeToAbsolutePath($currentUrl, $relativePath) {
	// Converts a relative path to an absolute path given the retrieve URL and a relative path, e.g.
	// $currentUrl = 'http://www.example.com/direcctory/asdf/page.html'
	// $relativePath = '../images/favicon.png'
	
	// If less than length of http://, this isn't a valid $currentUrl
	if (strlen($currentUrl) < 8) {
		return false;
	}
	
	// If absolute already, return
	$p = parse_url($relativePath);
	if (isset($p["scheme"])) {
		return $relativePath;
	}

	// Otherwise, let's build a URL

	// Get base path https://example.com
	$thirdSlashPosition = strpos($currentUrl, '/', 8);
	if ($thirdSlashPosition) {
		$basePath = substr($currentUrl, 0, $thirdSlashPosition);
	} else {	// Just a domain
		$basePath = $currentUrl;
	}

	if ($relativePath[0] == '/') {
		// Relative absolute
		// Append relative path to everything up to the third slash in $currentURL
		return $basePath.$relativePath;
	} else {
		// Fully relative path

		$path = '';

		// Strip all parent and this folder dots (.. and .)
		$relParts = explode("/", $relativePath);
		foreach($relParts as $i => $part) {
			if ($part == '.') {
				$relParts[$i] = null;
			}
			if ($part == '..') {
				$relParts[$i - 1] = null;
				$relParts[$i] = null;
			}
		}

		// Remove empty values
		$relParts = array_filter($relParts);

		// Reassemble string
		return $basePath.'/'.implode("/", $relParts);
	}
}
