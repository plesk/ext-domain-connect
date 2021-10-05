<?php
// Copyright 1999-2021. Plesk International GmbH.

require_once 'sdk.php';
pm_Context::init('domain-connect');

$requestHandler = new \PleskExt\DomainConnect\PublicRequestHandler(
    $_SERVER['HTTP_HOST'],
    $_SERVER['SERVER_PROTOCOL'],
    $_SERVER['REQUEST_URI']
);
$requestHandler->setGetParams($_GET)->handle();
exit;
