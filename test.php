<?php
/*
Example
*/
require_once ('vendor/autoload.php');

// variables
$logFolder = dirname ( __FILE__) . DIRECTORY_SEPARATOR . 'log';
$folder = dirname( __FILE__);

// process
$f = new \FileCheck\FileCheck($folder, $logFolder);
$f->setEmailFrom('sender@example.com');
$f->setEmailTo('someone@example.com');
if ( PHP_SAPI !== 'cli' && isset($_GET['log']) && !empty($_GET['log']) ) $f->setDebug(true);
$f->setExcludedFolders(array( $folder . '/log'));
$f->setNumFileLimit(5000);
$f->run();
$f->getReport();
//$f->sendReportByEmail();
