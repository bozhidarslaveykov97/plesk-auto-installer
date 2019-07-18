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
		
		$databaseName = 'wesellin_' . 'db';
		$databaseUser = 'wesellin_' . 'user';
		$databasePassword = rand(1111, 9999);
		
		$manager = new Modules_Wesellin_DatabaseManager();
		$manager->setDomainId($domain->getId());
		
		$newDatabase = $manager->createDatabase($databaseName);
		
		if (isset($newDatabase['database']['add-db']['result']['id'])) {
			$databaseId = $newDatabase['database']['add-db']['result']['id'];
		}
		
		if ($databaseId) {
			$newUser = $manager->createUser($databaseId, $databaseUser, $databasePassword);
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
		$symlinkFolders[] = 'config';
		$symlinkFolders[] = 'app';
		$symlinkFolders[] = 'vendor';
		$symlinkFolders[] = 'routes';
		$symlinkFolders[] = 'resources';
		$symlinkFolders[] = '.htaccess';
		$symlinkFolders[] = 'Modules';
		$symlinkFolders[] = 'Themes';
		$symlinkFolders[] = 'assets';
		
		foreach($symlinkFolders as $folder) {
			$folder = $this->_scriptFolder . '/' . $folder;
			if (is_dir($folder) || is_file($folder)) {
				$result = pm_ApiCli::callSbin('create_symlink.sh', [$folder, $domainDocumentRoot], pm_ApiCli::RESULT_FULL);
			}
		}
		
		$filesForCopy = array();
		$filesForCopy[] = 'storage';
		$filesForCopy[] = 'bootstrap';
		$filesForCopy[] = 'index.php';
		$filesForCopy[] = '.env';
		
		foreach($filesForCopy as $file) {
			$fileManager->copyFile($this->_scriptFolder . '/' . $file, $domainDocumentRoot . '/' . $file);
		}
		
		/*
		// Copy zip file
		$fileManager->copyFile($this->_scriptFolder . '/wesellin.zip', $domainDocumentRoot);   
		
		// Unzip copied file
		$fileManager->unzip($domainDocumentRoot . '/wesellin.zip', false,  $this->_overwrite);  
		
		// Remove zip file
		$fileManager->removeFile(mkdir . '/wesellin.zip'); */
		
	}
}