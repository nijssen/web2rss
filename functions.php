<?php

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



function absurl($path) {
    $isHttps = ((array_key_exists('HTTPS', $_SERVER) 
            && $_SERVER['HTTPS']) ||
        (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) 
                && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    );
    $thisurl = 'http' . ($isHttps ? 's' : '') .'://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    return convertRelativeToAbsolutePath($thisurl, $path);
}