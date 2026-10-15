<?php

declare(strict_types=1);

$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$basePath = rtrim(dirname($scriptName), '/');
if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}

define('SITE_NAME', 'Tony Handy Work Services');
define('CONTACT_PHONE', '07932 900 012');
define('CONTACT_TEL', '+447932900012');
define('CONTACT_EMAIL', 'tonyhandyworkservices@gmail.com');
define('SERVICE_AREA', 'North London · N15 & surrounding areas');

function asset_url(string $path): string
{
    global $basePath;

    return ($basePath !== '' ? $basePath : '') . '/' . ltrim($path, '/');
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
