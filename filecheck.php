<?php

/**
 * File integrity checker class
 */
class FileCheck
{
    private $folder = null;
    private $logFolder;
    private $lastReportFile;
    private $lastReport = array();
    private $actualReportFile;          # nombre del fichero del informe
    private $actualReport = array();    # informe actual
    private $emailReport = array();     # contenido del email
    
    private $emailTo;
    private $emailFrom;
    
    private $limitFolderRecursion = 3500;
    private $debug;
    private $excludedFolders = array();
    
    /**
     * Constructor
     *
     * @return void
     */    
    public function __construct($folder, $logFolder, $excludedFolders=array())
    {
        $this->setFolder($folder);
        $this->setLogFolder($logFolder);
        $this->setLoggerFolder();
        @date_default_timezone_set('Europe/Madrid');
        $this->actualReportFile = time();
        $this->debug = FALSE;
        $this->setExcludedFolders($excludedFolders);
    }
    
    /**
     * Set working folder
     *
     * @param string $value Folder's path to scan
     * @return void
     */        
    public function setFolder($value)
    {
        if(!empty($value) && is_dir($value))
            $this->folder = $value;
        else
            throw new \InvalidArgumentException('Cannot find the path '.$value);
    }
    
    /**
     * Set destination email 
     *
     * @param string $value Destination email.
     * @return void
     */
    public function setEmailTo($value)
    {
        if(!empty($value))
            $this->emailTo = $value;
        else
            throw new \InvalidArgumentException('Empty value');
    }
    
    /**
     * Set sender email 
     *
     * @param string $value Email from sender
     * @return void
     */
    public function setEmailFrom($value)
    {
        if(!empty($value))
            $this->emailFrom = $value;
        else
            throw new \InvalidArgumentException('Empty value');
    }    
    
    /**
     * Set log's folder
     *
     * @param string $value Path to the folder where store log files.
     * @return void
     */    
    public function setLogFolder($value)
    {
        if(!empty($value) && is_dir($value))
            $this->logFolder = $value;
        else
            throw new \InvalidArgumentException('Cannot find the path '.$value);
    }
    
    /**
     * Set debug level
     *
     * @param boolean $value TRUE/FALSE value for debug.
     * @return void
     */
    public function setDebug($value)
    {
        $this->debug = (bool) $value;
    }
    
    /**
     * Set folders excluded of scan
     *
     * @param array $values array with folders paths.
     * @return void
     */
    public function setExcludedFolders($values)
    {
        if(is_array($values) && !empty($values))
            $this->excludedFolders = array_map('strtolower', $values);
    }
    
    /**
     * Set maximum file to load
     *
     * @param int $value Maximum files to be load
     * @return void
     */
    public function setNumFileLimit($value)
    {
        $this->limitFolderRecursion = (int) $value;
    }
    
    /**
     * Run process
     *
     * @return void
     */
    public function run()
    {
        if( FALSE !== $this->readLastReport() )
        {
            if ( version_compare(PHP_VERSION, '5.1.0') >= 0)
                $this->runWithRecursiveDirectoryIterator();
            else
                $this->runWithDirectoryIterator();
            $this->saveActualReport();
        } 
    }
    
    /**
     * Load information from last report file.
     *
     * @return void
     */
    private function readLastReport()
    {
        /*
         * ToDo: check last log file's size to avoid security problem with it.
         */
        $this->lastReport = array();
        if ( is_dir($this->logFolder))
        {
            $t = array_diff( scandir ( $this->logFolder, TRUE), array('..', '.'));
            if (is_array($t) && !empty($t))
            {
                $this->lastReportFile = $this->logFolder . DIRECTORY_SEPARATOR . $t[0];
                $t = file_get_contents($this->lastReportFile);
                $this->lastReport = unserialize($t);
            }
        }
    }
    
    /**
     * Save the report data into log file
     *
     * @return void
     */
    private function saveActualReport()
    {
        if ( TRUE === $this->debug)  $this->writeText('Enter into saveActualReport()');
        @file_put_contents( $this->logFolder . DIRECTORY_SEPARATOR . $this->actualReportFile, serialize($this->actualReport), LOCK_EX);
    }
    
    /**
     * Walk recursively through working directory using DirectoryIterator
     *
     * @return void
     */
    private function runWithDirectoryIterator($folder='', $cont=0)
    {
        if ( TRUE === $this->debug)  $this->writeText('Enter into runWithDirectoryIterator()');
        if(!empty($this->folder) or ( func_num_args() > 0 && !empty($folder))) {
            if (empty($folder))
                $dir = new \DirectoryIterator($this->folder);
            else 
                $dir = new \DirectoryIterator($folder);
            
            foreach ($dir as $fileinfo) {
                if (!$fileinfo->isDot() && $cont <= $this->limitFolderRecursion) {
                    if ( $fileinfo->isDir() ) {
                        if ( FALSE === $this->search_in_array($fileinfo->getPathname(), $this->excludedFolders )) {
                            if ( TRUE === $this->debug)  $this->writeText($fileinfo->getPathname());
                            $this->runWithDirectoryIterator ($fileinfo->getPathname(), $cont);
                            //$cont++;
                        }
                    }
                    else {
                        $this->checkFile($fileinfo->getPathname(), $fileinfo->isDir());
                        $cont++;
                    }
                }
            }
            if (empty($folder))
                $this->checkDelete();
        }
        else {
            throw new \InvalidArgumentException('the Folder var is empty');    
        }
    }
    /**
     * Walk recursively through working directory using RecursiveDirectoryIterator
     *
     * @return void
     */
    private function runWithRecursiveDirectoryIterator()
    {
        $cont = 0;
        if(!empty($this->folder)) {
            if ( TRUE === $this->debug)  $this->writeText('Enter into runWithRecursiveDirectoryIterator()');
            
            // http://php.net/manual/es/class.recursivedirectoryiterator.php
            $Directory = new \RecursiveDirectoryIterator($this->folder, RecursiveDirectoryIterator::FOLLOW_SYMLINKS);
            // http://php.net/manual/es/class.recursiveiteratoriterator.php
            $Iterator = new \RecursiveIteratorIterator($Directory, \RecursiveIteratorIterator::CHILD_FIRST);
            // http://php.net/manual/es/class.regexiterator.php
            //$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
            $Iterator->rewind();
            // Iterator es un DirectoryIterator: http://php.net/manual/es/class.directoryiterator.php
            while($Iterator->valid()) {
                if (!$Iterator->isDot() && $cont <= $this->limitFolderRecursion) {
                    //if ( !$Iterator->isDir() || ( $Iterator->isDir() && FALSE === $this->search_in_array(strtolower($Iterator->Key()), array_map('strtolower', $this->excludedFolders)) ) )
                    if ( FALSE === $this->search_in_array(strtolower($Iterator->Key()), $this->excludedFolders) )
                    {
                        if ( TRUE === $this->debug)  $this->writeText($Iterator->Key());
                        $this->checkFile($Iterator->Key(), $Iterator->isDir());
                        $cont++;
                    }
                    
                }
                $Iterator->next();
            }
            $this->checkDelete();
        }
        else {
            throw new \InvalidArgumentException('the Folder var is empty');    
        }
    }
    
    
    
    /**
     * Check file's status and saving into logging variable
     *
     * @param string $file Entry to analyze
     * @param boolean $isDir Flag to say if $file is a directory
     * @return void
     */
    private function checkFile($file, $isDir)
    {
        $out = array(
            'file' => '',
            'status' => '=',
            'md5' => '',
            'date' => $this->actualReportFile,
            );
        // http://php.net/manual/es/directoryiterator.isdot.php
        $out['file'] = $file;
        $out['md5'] = '';
        // http://php.net/manual/es/directoryiterator.isdir.php
        if ( !$isDir)
            $out['md5'] = md5_file($file);
        $index = $this->recursive_array_search($file, $this->lastReport);
        if (FALSE !== $index ) {
            if ( $out['md5'] != $this->lastReport[$index]['md5'] )
            {
                $out['status'] = '*';
                $this->emailReport[] = $file . ' has been modified.';
            }
        }
        else 
        {
            $this->emailReport[] = $file . ' has been added.';
        }
        $this->actualReport[] = $out;
    }
    
    /**
     * Check for deleted entries
     *
     * @return void
     */
    private function checkDelete()
    {
        // buscamos los posibles eliminados, comparamos $this->lastReport con $this->actualReport
        foreach ( $this->lastReport as $c=>$v) {
            $index = $this->recursive_array_search($v['file'], $this->actualReport);
            if (FALSE === $index ) {
                $this->emailReport[] = $v['file'] . ' has been deleted.';
            }
        }
    }
    
    /**
     * Search recursively data into an array
     *
     * @param string $needle String to locate
     * @param array $haystack Array where we search $needle
     * @return int/FALSE Returns the key for needle if it is found in the array, FALSE otherwise
     * @source http://php.net/manual/es/function.array-search.php#91365
     */
    private function recursive_array_search($needle, $haystack) 
    {
        foreach ($haystack as $key=>$value) {
            $index =  array_search ( $needle , $value);
            if ( FALSE !== $index )
                return $key;
        }
        return false;
    }
    
    /**
     * Search string into array's values
     *
     * @param string $needle String to locate
     * @param array $haystack Array where we search $needle
     * @return int/FALSE Returns the key for needle if it is found in the array, FALSE otherwise
     * @source http://php.net/manual/es/function.array-search.php#91365
     */
    private function search_in_array($needle, $haystack) 
    {
        $needle = str_replace('\\', '/', $needle);
        $out = FALSE;
        foreach ($haystack as $key=>$value) {
            $value = str_replace('\\', '/', $value);
            $index = stripos( $needle, $value);
            //print_r ( array($needle, $value, $index) );    
            if ( FALSE !== $index )
                $out = $key;
        }
        return $out;
    }
    
    
    /**
     * Create folder for the log file
     *
     * @return void
     */
    private function setLoggerFolder()
    {
        if (!is_dir($this->logFolder))
        {
            // create
            @mkdir($this->logFolder, 0650);
            // and rights
            @chown($this->logFolder, shell_exec("whoami"));
        }        
    }
    
    /**
     * Send report by email
     *
     * @return void
     */
    public function sendReportByEmail()
    {
        if(empty($this->emailReport)) return;
        if(empty($this->emailFrom) or is_null($this->emailFrom)) return;
        if(empty($this->emailTo) or is_null($this->emailTo)) return;
        
        if( TRUE === $this->debug ) {
            $this->writeText($this->emailReport);
        }
        $headers = array(
            'From: ' . $this->emailFrom,
            'Reply-To: ' . $this->emailFrom,
            'To: ' . $this->emailTo,
            'Subject: ' . 'FileCheck Report ' . date('G:i:s m-d-Y', $this->actualReportFile),
            'X-Mailer: ' . 'PHP/' . phpversion(),
            'MIME-Version: ' . '1.0',
            // for HTML
            //'Content-type: text/html; charset=iso-8859-1',
        );
        $parameters = '';
        $message = wordwrap('FileCheck Report:' . "\n" . implode("\r\n", $this->emailReport), 70, "\r\n");
        @mail($this->emailTo, 'FileCheck Report ' . $this->actualReportFile , $message, implode("\r\n", $headers), $parameters);
    }
    /**
     * Write formatted text for several environment
     *
     * @param string $text Text to be prepared
     * @return boolean Success/fail of save
     */
    private function writeText($text)
    {
        if (is_array($text)){
            foreach($text as $c=>$v)
                $this->writeText ($v);
        }
        else {        
            if( PHP_SAPI !== 'cli')
                echo $text . '<br>';
            else
                echo $text . PHP_EOL;
        }
    }
}