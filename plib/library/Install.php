<?php
/*
 * https://docs.plesk.com/en-US/onyx/api-rpc/about-xml-api/reference/managing-databases/creating-database-users/creating-multiple-database-users.34472/#creating-a-database-user
 */

class Modules_Wesellin_Install
{
	protected $_scriptFolder = '/usr/share/wesellinsellerapp';
	protected $_overwrite = true;
	protected $_domainId;
	protected $_version;
	protected $_type = 'default';
	
	public function setDomainId($id) {
		$this->_domainId = $id;
	}
	
	public function setVersion($version) {
		$this->_version = $version;
	}
	
	public function setType($type) {
		$this->_type = $type;
	}
	
	public function run() {
	    
		$domain = pm_Domain::getByDomainId($this->_domainId);
		
		if (empty($domain->getName())) {
			throw new \Exception('Domain not found.');
		}
		
		$time = time();
		
		$dbName = 'db_'.$time;
		$dbUsername = 'user_'.$time;
		$dbPassword = 'hs45i4m4';
 		
		$manager = new Modules_Wesellin_DatabaseManager();
		$manager->setDomainId($domain->getId());
		
		$newDb = $manager->createDatabase($dbName);
		
		if (isset($newDb['database']['add-db']['result']['errtext'])) {
		    throw new \Exception($newDb['database']['add-db']['result']['errtext']);
		}
		
		if (isset($newDb['database']['add-db']['result']['id'])) {
		    $dbId = $newDb['database']['add-db']['result']['id'];
		}
		
		if (!$dbId) {
		    throw new \Exception('Can\'t create database.');
		}
		
		if ($dbId) {
		    $newUser = $manager->createUser($dbId, $dbUsername, $dbPassword);
		}
		
		if (isset($newUser['database']['add-db-user']['result']['errtext'])) {
		    throw new \Exception($newUser['database']['add-db-user']['result']['errtext']);
		}
		
		$domainDocumentRoot = $domain->getDocumentRoot();
		$domainName = $domain->getName();
		$domainIsActive = $domain->isActive();
		$domainCreation = $domain->getProperty('cr_date');
		
		$fileManager = new pm_FileManager($domain->getId());
		
		// Remove old files
		$scanDir = $fileManager->scanDir($domainDocumentRoot);
		
		foreach ($scanDir as $dirOrFile) {
			
			if ($dirOrFile == '.' || $dirOrFile == '..') {
				continue;
			}
			
			$dirOrFile = $domainDocumentRoot .'/'. $dirOrFile;
			
			if ($fileManager->isDir($dirOrFile)) {
				$fileManager->removeDirectory($dirOrFile);
			} else {
				$fileManager->removeFile($dirOrFile);
			}
		}
		
		// Build a script
		$symlinkFolders = array();
		//$symlinkFolders[] = 'config';
		$symlinkFolders[] = 'app';
		$symlinkFolders[] = 'vendor';
		$symlinkFolders[] = 'routes';
		$symlinkFolders[] = 'resources';
		//$symlinkFolders[] = '.htaccess';
		$symlinkFolders[] = 'Modules';
		$symlinkFolders[] = 'Themes';
		$symlinkFolders[] = 'assets';
		
		foreach($symlinkFolders as $folder) {
			/* $folder = $this->_scriptFolder . '/' . $folder;
			if (is_dir($folder) || is_file($folder)) {
				$result = pm_ApiCli::callSbin('create_symlink.sh', [$folder, $domainDocumentRoot], pm_ApiCli::RESULT_FULL);
			} */
		}
		
		$filesForCopy = array();
		
		// Copy only for installation
		$filesForCopy[] = 'app';
		$filesForCopy[] = 'vendor';
		$filesForCopy[] = 'routes';
		$filesForCopy[] = 'resources';
		$filesForCopy[] = 'Modules';
		$filesForCopy[] = 'Themes';
		
		$filesForCopy[] = 'storage';
		$filesForCopy[] = 'bootstrap';
		$filesForCopy[] = 'index.php';
		$filesForCopy[] = '.env.example';
		$filesForCopy[] = 'config';  
		$filesForCopy[] = '.htaccess';
		$filesForCopy[] = 'artisan';
		
		foreach($filesForCopy as $file) {
		    try {
		        $fileManager->copyFile($this->_scriptFolder . '/' . $file, $domainDocumentRoot . '/' . $file);
		    } catch (\PleskUtilException $e) {
		       echo 'Cant copy file: ' . $file . PHP_EOL;
		    }
		}
		
		$adminFirstName = '';
		$adminLastName = '';
		$adminEmail = '';
		$adminPassword = '';
		$storeName = '';
		$storeEmail = '';
		
		$installArguments = array();
		$installArguments[] = '--db_name=' . $dbName;
		$installArguments[] = '--db_host=' . $dbHost;
		$installArguments[] = '--db_port=' . $dbPort;
		$installArguments[] = '--db_username=' . $dbUsername;
		$installArguments[] = '--db_password=' . $dbPassword;
		
		$installArguments[] = '--admin_first_name=' . $adminFirstName;
		$installArguments[] = '--admin_last_name=' . $adminLastName;
		$installArguments[] = '--admin_email=' . $adminEmail;
		$installArguments[] = '--admin_password=' . $adminPassword;
		
		$installArguments[] = '--store_name=' . $storeName;
		$installArguments[] = '--store_email=' . $storeEmail;
		
		$installArguments = implode(' ', $installArguments);
		
		
		$command = 'php '.$domainDocumentRoot.'/artisan wesellin:install '. $installArguments;
		
		$runInstall = shell_exec($command);
		var_dump($runInstall); 
		
		$command = 'php '.$domainDocumentRoot.'/artisan wesellin:install-simple-content';
		$runInstall = shell_exec($command);
		var_dump($runInstall); 
		
		//var_dump($runInstall);  
		
		
		/*
		// Copy zip file
		$fileManager->copyFile($this->_scriptFolder . '/wesellin.zip', $domainDocumentRoot);   
		
		// Unzip copied file
		$fileManager->unzip($domainDocumentRoot . '/wesellin.zip', false,  $this->_overwrite);  
		
		// Remove zip file
		$fileManager->removeFile(mkdir . '/wesellin.zip'); */
		
	}
}