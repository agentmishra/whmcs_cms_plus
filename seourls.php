<?php
$_SERVER['PHP_SELF'] = $_SERVER['REQUEST_URI'];
$_SERVER['SCRIPT_NAME'] = $_SERVER['REQUEST_URI'];
$_GET['m'] = 'whmcs_cms_plus';
require(dirname(dirname(dirname(dirname(__FILE__)))).'/index.php');