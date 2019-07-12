<?php

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
		
		$domain = pm_Domain::getByDomainId($this->_domainId);
		
		if (empty($domain->getName())) {
			throw new \Exception('Domain not found.');
		}
		
		
		
		
		echo 1;
		die();
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
}