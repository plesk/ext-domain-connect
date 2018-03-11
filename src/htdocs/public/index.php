<?php
// Copyright 1999-2018. Plesk International GmbH.

require_once 'sdk.php';
pm_Context::init('domain-connect');

if (!preg_match('/\/v2\/(.*)\/settings/', $_SERVER['REQUEST_URI'], $matches)) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request', true, 400);
    exit;
}
$domain = $matches[1];

try {
    $pmDomain = pm_Domain::getByName($domain);
} catch (pm_Exception $e) {
    header($_SERVER['SERVER_PROTOCOL'] . ' 404 Domain not hosted', true, 404);
    exit;
}

$data = [
    'providerId' => pm_Config::get('providerId'),
    'providerName' => pm_Config::get('providerName'),
    'providerDisplayName' => pm_Config::get('providerDisplayName'),
    'urlSyncUX' => 'https://' . $_SERVER['HTTP_HOST'] . '/modules/' . pm_Context::getModuleId() . '/index.php/',
    'urlAPI' => 'https://' . $_SERVER['HTTP_HOST'] . '/modules/' . pm_Context::getModuleId() . '/index.php/',
    'width' => 750, // TODO: add values here
    'height' => 750 // TODO: add values here
];
echo json_encode($data);
