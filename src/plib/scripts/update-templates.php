<?php
// Copyright 1999-2018. Plesk International GmbH.

function emptyDir($dir)
{
    foreach (scandir($dir) as $basename) {
        $path = $dir . '/' . $basename;

        if (is_file($path)) {
            unlink($path);
        }
    }
}

function downloadLogo($url, $dir, $providerId, $serviceId)
{
    $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
    $basename = $providerId . '.' . $serviceId . '.' . $ext;
    $file = $dir . '/' . $basename;

    if (!is_file($file)) {
        $data = file_get_contents($url);

        if ($data === false) {
            return '';
        }

        file_put_contents($file, $data);
    }

    return '/modules/domain-connect/logos/' . $basename;
}

$templateDir = dirname(__DIR__) . '/resources/templates';
$logoDir = dirname(dirname(__DIR__)) . '/htdocs/logos';
$archiveUrl = 'https://github.com/plesk/domain-connect-templates/archive/master.zip';
$archiveFile = tempnam(sys_get_temp_dir(), 'DC');

file_put_contents($archiveFile, file_get_contents($archiveUrl));

emptyDir($templateDir);
emptyDir($logoDir);

$zip = new \ZipArchive();

if ($zip->open($archiveFile) === true) {
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $path = $zip->getNameIndex($i);
        $pathInfo = pathinfo($path);

        if (!isset($pathInfo['extension']) || ($pathInfo['extension'] !== 'json')) {
            continue;
        }

        $json = $zip->getFromIndex($i);
        $data = json_decode($json, true);

        if ($data === null) {
            continue;
        }

        if ($data['logoUrl'] !== '') {
            $data['logoUrl'] = downloadLogo($data['logoUrl'], $logoDir, $data['providerId'], $data['serviceId']);
        }

        file_put_contents(
            $templateDir . '/' . $pathInfo['basename'],
            json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n"
        );
    }

    $zip->close();
}

unlink($archiveFile);
