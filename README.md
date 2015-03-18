# filecheck
File Integrity Checker.

This is a simple PHP script that check folder's files integrity looking for added, modified or deleted files. It could be usefull to check website's folder to see if someone has upload some unwanted file.

Basic usage:

	set_time_limit(0);
	error_reporting(0);

	require ('filecheck.php');

	// set variables
    $logFolder = '/path/to/log/folder';
    $folder = '/path/folder/to/scan';
    
    // configuring process
    $f = new FileCheck($folder, $logFolder);
    $f->setEmailFrom('sender@example.com');
    $f->setEmailTo('someone@example.com');
	$f->setExcludedFolders(array( $folder . '/folder1', $folder . '/folder2'));
	$f->setNumFileLimit(5000);

	// execute    
	$f->run();
	// send report
    $f->sendReportByEmail();

To-Do.


