<?php

trait AppDefaultInstall {
	
	protected $_scriptFolder = '/usr/share/credocart/latest';
	protected $_overwrite = true;
	protected $_domainId;
	protected $_type = 'default';
	
	public function setDomainId($id) {
		$this->_domainId = $id;
	}
	
	public function setType($type) {
		$this->_type = $type;
	}
	
	public function run() {
		
		// Your installation code here
		
	}
}