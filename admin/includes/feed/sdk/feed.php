<?php

function getFeedPath()
{
    return dirname(__FILE__) . DIRECTORY_SEPARATOR;
}

function feedautoloader($class)
{
    include getFeedPath() . 'core' . DIRECTORY_SEPARATOR . $class . '.php';
}

if (!function_exists('json_encode')) {
    require_once getFeedPath() . 'lib/jsonwrapper_inner.php';
}

require_once getFeedPath() . 'lib/http-client.php';

require_once getFeedPath() . 'config.php';

spl_autoload_register('feedautoloader');
