<?php

function getAbsoluteUrl($baseUrl, $relativeUrl){

    // if already absolute URL 
    if (parse_url($relativeUrl, PHP_URL_SCHEME) !== null){
        return $relativeUrl;
    }

    // parse base URL and convert to: $scheme, $host, $path, $query, $port, $user, $pass
    $url_parts = parse_url($baseUrl);
    extract($url_parts);
    
    // queries and anchors
    if ($relativeUrl[0] === '?'){
        $url_parts['query'] = substr($relativeUrl, 1);
        return unparse_url($url_parts);
    } else if ($relativeUrl[0] === '#') {
        $url_parts['fragment'] = substr($relativeUrl, 1);
        return unparse_url($url_parts);
    }

    // if base URL contains a path remove non-directory elements from $path
    if (isset($path) === true){
        $path = preg_replace('#/[^/]*$#', '', $path);
    }
    else {
        $path = '';
    }

    // if realtive URL starts with //
    if (substr($relativeUrl, 0, 2) === '//'){
        return $scheme.':'.$relativeUrl;
    }

    // if realtive URL starts with /
    if ($relativeUrl[0] === '/'){
        $path = null;
    }

    $abs = null;

    // if realtive URL contains a user
    if (isset($user) === true){
        $abs .= $user;

        // if realtive URL contains a password
        if (isset($pass) === true){
            $abs .= ':'.$pass;
        }

        $abs .= '@';
    }

    $abs .= $host;

    // if realtive URL contains a port
    if (isset($port) === true){
        $abs .= ':'.$port;
    }

    $abs .= $path.'/'.$relativeUrl.(isset($query) === true ? '?'.$query : null);

    // replace // or /./ or /foo/../ with /
    $re = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];
    for ($n = 1; $n > 0; $abs = preg_replace($re, '/', $abs, -1, $n)) {
    }

    // return absolute URL
    return $scheme.'://'.$abs;

}

function absurl($path) {
    $isHttps = ((array_key_exists('HTTPS', $_SERVER) 
            && $_SERVER['HTTPS']) ||
        (array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER) 
                && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
    );
    $thisurl = 'http' . ($isHttps ? 's' : '') . '://' . $_SERVER['SERVER_NAME'] .  $_SERVER['SCRIPT_NAME'];
    return getAbsoluteUrl($thisurl, $path);
    //return convertRelativeToAbsolutePath($_SERVER['PHP_SELF'], $path);
}

// https://www.php.net/manual/en/function.parse-url.php#106731
function unparse_url($parsed_url) {
  $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
  $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
  $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
  $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
  $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
  $pass     = ($user || $pass) ? "$pass@" : '';
  $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
  $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
  $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';
  return "$scheme$user$pass$host$port$path$query$fragment";
}