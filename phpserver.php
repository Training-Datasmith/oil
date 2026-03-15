<?php

declare(strict_types=1);
/**
 * Fuel is a fast, lightweight, community driven PHP 5.4+ framework.
 *
 * @package    Fuel
 * @version    1.8.2
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2019 Fuel Development Team
 * @link       https://fuelphp.com
 */

// determine the file we're loading, we need to strip the query string for that
if (isset($_SERVER['SCRIPT_NAME'])) {
    $file = $_SERVER['DOCUMENT_ROOT'].$_SERVER['SCRIPT_NAME'];
} else {
    $file = $_SERVER['DOCUMENT_ROOT'].$_SERVER['REQUEST_URI'];
    if (($pos = strpos($file, '?')) !== false) {
        $file = substr($file, 0, $pos);
    }
}

// Resolve the path to prevent directory traversal
$docroot = realpath($_SERVER['DOCUMENT_ROOT']) ?: $_SERVER['DOCUMENT_ROOT'];
$resolved = realpath($file);
if ($resolved !== false && strncmp($resolved, $docroot, strlen($docroot)) !== 0) {
    // Path escapes document root — route normally
    $_SERVER['SCRIPT_NAME'] = __FILE__;
    include $docroot.'/index.php';
    return;
}
$file = $resolved ?: $file;

if (file_exists($file)) {
    // bypass existing file processing
    return false;
} else {
    // route requests though the normal path
    $_SERVER['SCRIPT_NAME'] = __FILE__;
    include $docroot.'/index.php';
}
