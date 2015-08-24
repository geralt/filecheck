<?php
/**/
define ('BASEDIR', __DIR__);

spl_autoload_register( function($className){
    if (substr($className, 0, 9) !== 'FileCheck') {return;}
    $className = ltrim($className, '\\');
    $fileName  = '' . BASEDIR . DIRECTORY_SEPARATOR;
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	//echo 'Fichero a buscar: ' . $fileName . ' para la clase: ' . $className;
    //echo PHP_EOL . 'existe el fichero? ' . (is_file($fileName));
    require_once ($fileName);
});


/**/

/**
require_once( dirname(__FILE__)  . DIRECTORY_SEPARATOR . 'AutoLoader.php');
// Register the directory to your include files
AutoLoader::registerDirectory( dirname(__FILE__)  . DIRECTORY_SEPARATOR . '../FileCheck/');
/**/
