<?php

function getAbsoluteUrl($baseUrl, $relativeUrl){

    // if already absolute URL 
    if (parse_url($relativeUrl, PHP_URL_SCHEME) !== null){
        return $relativeUrl;
    }

    // queries and anchors
    if ($relativeUrl[0] === '#' || $relativeUrl[0] === '?'){
        return $baseUrl.$relativeUrl;
    }

    // parse base URL and convert to: $scheme, $host, $path, $query, $port, $user, $pass
    extract(parse_url($baseUrl));

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