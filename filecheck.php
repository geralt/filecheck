<?php
set_time_limit(0);
error_reporting(0);

/**
 * File integrity checker class
 */
class FileCheck
{
	private $folder = null;
	private $folderClavesFirma;
	private $lastReportFile;
	private $lastReport = array();
	private $actualReportFile;
	private $actualReport = array();
	private $emailReport = array();
	private $emailTo;
	private $emailFrom;
	private $limitFolderRecursion = 3500;
	private $debug;
	
	/**
	 * Constructor
	 *
	 * @return void
	 */	
	public function __construct($folder, $folderClavesFirma)
	{
		$this->setFolder($folder);
		$this->folderClavesFirma($folderClavesFirma);
		$this->setLoggerFolder();
		@date_default_timezone_set('Europe/Madrid');
		$this->actualReportFile = time();
		$this->debug = FALSE;
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
	public function folderClavesFirma($value)
	{
		if(!empty($value) && is_dir($value))
			$this->folderClavesFirma = $value;
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
        $this->debug = (int) $value;
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
		if ( is_dir($this->folderClavesFirma))
		{
			$t = array_diff( scandir ( $this->folderClavesFirma, TRUE), array('..', '.'));
			if (is_array($t) && !empty($t))
			{
				$this->lastReportFile = $this->folderClavesFirma . DIRECTORY_SEPARATOR . $t[0];
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
		@file_put_contents( $this->folderClavesFirma . DIRECTORY_SEPARATOR . $this->actualReportFile, serialize($this->actualReport), LOCK_EX);
	}
	
	/**
	 * Walk recursively through working directory using DirectoryIterator
	 *
	 * @return void
	 */
	private function runWithDirectoryIterator($folder='', $cont=0)
	{
		if(!empty($this->folder) or ( func_num_args() > 0 && !empty($folder))) {
			if (empty($folder))
				$dir = new \DirectoryIterator($this->folder);
			else 
				$dir = new \DirectoryIterator($folder);
			
			foreach ($dir as $fileinfo) {
				if (!$fileinfo->isDot() && $cont <= $this->limitFolderRecursion) {
					if ( $fileinfo->isDir())
						$this->runWithDirectoryIterator ($fileinfo->getPathname(), $cont);
					else 
						$this->checkFile($fileinfo->getPathname(), $fileinfo->isDir());
					$cont++;
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
			// http://php.net/manual/es/class.recursivedirectoryiterator.php
			$Directory = new \RecursiveDirectoryIterator($this->folder);
			// http://php.net/manual/es/class.recursiveiteratoriterator.php
			$Iterator = new \RecursiveIteratorIterator($Directory, \RecursiveIteratorIterator::CHILD_FIRST);
			// http://php.net/manual/es/class.regexiterator.php
			//$Regex = new \RegexIterator($Iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
			$Iterator->rewind();
			// Iterator es un DirectoryIterator: http://php.net/manual/es/class.directoryiterator.php
			while($Iterator->valid()) {
				if (!$Iterator->isDot() && $cont <= $this->limitFolderRecursion) {
					$this->checkFile($Iterator->Key(), $Iterator->isDir());
					$cont++;
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
	 * Create folder for the log file
	 *
	 * @return void
	 */
	private function setLoggerFolder()
	{
		if (!is_dir($this->folderClavesFirma))
		{
			// create
			@mkdir($this->folderClavesFirma, 0650);
			// and rights
			@chown($this->folderClavesFirma, shell_exec("whoami"));
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
		
		if( PHP_SAPI !== 'cli' && $this->debug == TRUE) $this->writeText($this->emailReport);
		//return;
		$headers = array(
			'From' => $this->emailFrom,
			'Reply-To' => $this->emailFrom,
			'To' => $this->emailTo,
			'Subject' => 'FileCheck Report ' . $this->actualReportFile,
			'X-Mailer' => 'PHP/' . phpversion(),
			'MIME-Version' => '1.0',
			// para HTML
			//'Content-type' => 'text/html; charset=iso-8859-1',
		);
		$parameters = '';
		$message = 'FileCheck Report' . "\n\r" . implode('\n\r', $this->emailReport);
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