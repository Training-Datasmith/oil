<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$zipFile = getenv('OIL_ZIP_FILE');
if ($zipFile && preg_match('#/demopkg/zipball/#', $path) && is_file($zipFile))
{
	header('Content-Type: application/zip');
	readfile($zipFile);
	return true;
}
http_response_code(404);
echo 'not found';
return true;
