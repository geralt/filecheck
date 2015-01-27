<?php
/*
Example
*/
require ('filecheck.php');

// variables
$logFolder = '/path/to/log/folder';
$folder = '/path/folder/to/scan';

// process
$f = new FileCheck($folder, $logFolder);
$f->setEmailFrom('sender@example.com');
$f->setEmailTo('someone@example.com');
if ( PHP_SAPI !== 'cli' && isset($_GET['log']) && !empty($_GET['log']) ) $f->setDebug(true);
$f->setExcludedFolders(array('/path/folder1', '/path/folder2'));
$f->setNumFileLimit(5000);
$f->run();
$f->sendReportByEmail();
