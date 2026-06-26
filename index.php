<?php

define('BASE_PATH', __DIR__);
define('PUBLIC_URL_PREFIX', '/public');
define('ROUTE_URL_MODE', 'query');
if (defined('GHP_ROUTE') && empty($_GET['r'])) {
    $_GET['r'] = GHP_ROUTE;
}

require __DIR__ . '/public/index.php';
