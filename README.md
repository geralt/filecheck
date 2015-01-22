# filecheck
File Integrity Checker.

This is a simple PHP script that check folder's files integrity looking for added, modified or deleted files. It could be usefull to check website's folder to see if someone has upload some unwanted file.

Basic usage:

	require ('filecheck.php');
	// set variables
    $folderClavesFirma = '/path/to/log/folder';
    $folder = '/path/folder/to/scan';
    
    // executing process
    $f = new FileCheck($folder, $folderClavesFirma);
    $f->setEmailFrom('sender@example.com');
    $f->setEmailTo('someone@example.com');
    $f->run();
    $f->sendReportByEmail();


