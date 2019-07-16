<?php
/*
 * https://docs.plesk.com/en-US/onyx/api-rpc/about-xml-api/reference/managing-databases/creating-database-users/creating-multiple-database-users.34472/#creating-a-database-user
 */

class Modules_Wesellin_Install
{
	protected $_scriptFolder = '/var/www/vhosts/wesellin.net';
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
		
		      
		/* var_dump(run('ls'));
		die();
		$this->_checkUserIsRoot(); */
		
		$domain = pm_Domain::getByDomainId($this->_domainId);
		
		if (empty($domain->getName())) {
			throw new \Exception('Domain not found.');
		}
		
		/* echo shell_exec('ls');
		echo shell_exec('ln -sf /test /test2');
		die();
		$symlink = '/bin/ln -s /usr/share/wesellinsellerapp/config ' . $domain->getDocumentRoot();
		echo $symlink . '<br />';
		$output = exec($symlink);
		var_dump($output); 
		die(); */
		
		$databaseName = 'wesellin_' . rand(111, 999);
		$databaseUser = 'wesellin_' . rand(111, 999);
		$databasePassword = rand(111, 999);
		
		$manager = new Modules_Wesellin_DatabaseManager();
		$manager->setDomainId($domain->getId());
		
		$newDatabase = $manager->createDatabase($databaseName);
		if (isset($newDatabase['database']['add-db']['result']['id'])) {
			$databaseId = $newDatabase['database']['add-db']['result']['id'];
		}
		
		$newUser = $manager->createUser($databaseId, $databaseUser, $databasePassword);
		
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
		
		// Copy zip file
		$fileManager->copyFile($this->_scriptFolder . '/wesellin.zip', $domainDocumentRoot);   
		
		// Unzip copied file
		$fileManager->unzip($domainDocumentRoot . '/wesellin.zip', false,  $this->_overwrite);  
		
		// Remove zip file
		$fileManager->removeFile($domainDocumentRoot . '/wesellin.zip');
		
	}
	
	protected function _checkUserIsRoot()
	{
		$whoami = shell_exec('whoami');
		$whoami = trim($whoami);
		
		if ($whoami != 'root') {
			throw new \Exception("This can only be installed by 'root' (not: '$whoami').");
		}
	}
}