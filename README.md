# filecheck
File Integrity Checker.

[![Build Status Images](https://travis-ci.org/geralt/filecheck.svg)](https://travis-ci.org/geralt/filecheck/ "Build Status")

This is a simple PHP script that check folder's files integrity looking for added, modified or deleted files. It could be usefull to check website's folder to see if someone has upload some unwanted file.

Basic usage:

	set_time_limit(0);
	error_reporting(0);

	require_once ('vendor/autoload.php');
	
	// variables
	$logFolder = dirname ( __FILE__) . DIRECTORY_SEPARATOR . 'log';
	$folder = dirname( __FILE__);
	
	// process
	$f = new \FileCheck\FileCheck($folder, $logFolder);
    $f->setEmailFrom('sender@example.com');
    $f->setEmailTo('someone@example.com');
	$f->setExcludedFolders(array( $folder . '/log'));
	$f->setNumFileLimit(5000);

	// execute    
	$f->run();
    // view report
    $f->getReport();
	// send report by email
    $f->sendReportByEmail();

To-Do.


