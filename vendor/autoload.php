<?php
define ('BASEDIR', dirname(__FILE__));

spl_autoload_register( function($className){
    $className = ltrim($className, '\\');
    $fileName  = '' . BASEDIR . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
    $namespace = '';
    if ($lastNsPos = strrpos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
	echo 'Fichero a buscar: ' . $fileName . ' para la clase: ' . $className;
    require_once ($fileName);
});
