<?php
// Copyright 1999-2018. Plesk International GmbH.
require_once 'sdk.php';
pm_Context::init('domain-connect');

if (!pm_Config::get('dnsProvider')) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 403 The DNS provider is disabled by server configuration', true, 400);
    exit;
}

if (!preg_match('|/v2/(.*)/settings|', $_SERVER['REQUEST_URI'], $matches)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
    exit;
}
$domain = $matches[1];

try {
    $pmDomain = pm_Domain::getByName($domain);
} catch (pm_Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 The domain does not belong to the DNS provider', true, 404);
    exit;
}

$domainDns = new \PleskExt\DomainConnect\DomainDns($pmDomain);

$data = array_filter([
    'providerId' => pm_Config::get('providerId'),
    'providerName' => pm_Config::get('providerName'),
    'providerDisplayName' => pm_Config::get('providerDisplayName', empty($_GET['providerDisplayName']) ? null : $_GET['providerDisplayName']),
    'urlSyncUX' => 'https://' . $_SERVER['HTTP_HOST'] . '/modules/domain-connect/index.php/',
    'urlAPI' => 'https://' . $_SERVER['HTTP_HOST'] . '/modules/domain-connect/index.php/',
    'width' => 750,
    'height' => 750,
    'nameServers' => $domainDns->getNameServers(),
]);
header("Content-Type: application/json");
echo json_encode($data);
