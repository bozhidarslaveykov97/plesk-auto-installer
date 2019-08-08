<?php

class IndexController extends pm_Controller_Action
{

	protected $_accessLevel = [
		'admin'
	];
	
	protected $_moduleName = 'We Sell In';
	
	public function init()
	{
		parent::init();
		
		// Init tabs for all actions
		$this->view->tabs = [
			[
				'title' => 'Domains',
				'action' => 'index'
			],
			[
			'title' => 'Install',
				'action' => 'install'
			],
		    [
		        'title' => 'Update',
		        'action' => 'update'
		    ],
			[
				'title' => 'Settings',
				'action' => 'settings',
			]
		];
	}

	public function indexAction()
	{
		$this->view->pageTitle = $this->_moduleName . ' - Domains';
		$this->view->list = $this->_getDomainsList();
	}
	
	public function updateAction()
	{
	    $this->view->pageTitle = $this->_moduleName . ' - Update';
	    
	    $form = new pm_Form_Simple();
	    
	    $form->addControlButtons([
	        'cancelLink' => pm_Context::getModulesListUrl(),
	    ]);
	    
	    $this->view->form = $form;
	}
	
	public function downloadAction() {
	
	    var_dump(pm_ApiCli::callSbin('download_from_git.sh'));
	    
	    die();
	}
	
	public function versionAction() {
	  
	    echo '1.0';
	    exit;
	}
	
	public function testAction()
	{
        $newInstallation = new Modules_Wesellin_Install();
        $newInstallation->setDomainId(2);
        $newInstallation->setVersion(1);
        $newInstallation->setType('default');
        $newInstallation->run();
	}
	
	public function installAction()
	{
		$this->view->pageTitle = $this->_moduleName . ' - Install';
		
		$domainsSelect = [];
		foreach (pm_Domain::getAllDomains() as $domain) {
			
			$domainId = $domain->getId();
			$domainName = $domain->getName();
			
			$domainsSelect[$domainId] = $domainName;
		}
		
		$form = new pm_Form_Simple();
		$form->addElement('select', 'installation_domain', [
			'label' => 'Domain',
			'multiOptions' => $domainsSelect,
			'required' => true,
		]);
		$form->addElement('select', 'installation_version', [
			'label' => 'Version',
			'multiOptions' => ['1.0'=>'1.0'],
			'required' => true,
		]);
		$form->addElement('radio', 'installation_type', [
			'label' => 'Installation Type',
			'multiOptions' =>
			[
				'default' => 'Default',
				'sym_linked' => 'Sym-Linked'
			],
			'value' => pm_Settings::get('installation_type'),
			'required' => true,
		]);
		
		$form->addControlButtons([
			'cancelLink' => pm_Context::getModulesListUrl(),
		]);
		
		if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
			
			$post = $this->getRequest()->getPost();
			
			$newInstallation = new Modules_Wesellin_Install();
			$newInstallation->setDomainId($post['installation_domain']);
			$newInstallation->setVersion(1);
			$newInstallation->setType($post['installation_type']);
			$newInstallation->run();
		}
		
		$this->view->form = $form;
	}
	
	public function settingsAction() {
		
		$this->view->pageTitle = $this->_moduleName . ' - Settings';
		
		$form = new pm_Form_Simple();
		$form->addElement('radio', 'installation_settings', [
			'label' => 'Installation Settings',
			'multiOptions' => 
				[
					'auto' => 'Automaticlly install WeSellIn on new domains creation.', 
					'manual' => 'Allow users to Manualy install WeSellIn from Plesk.',
					'disabled'=> 'Disabled for all users'
				],
			'value' => pm_Settings::get('installation_settings'),
			'required' => true,
		]);
		
		$form->addElement('radio', 'installation_type', [
			'label' => 'Installation Type',
			'multiOptions' =>
			[
				'default' => 'Default',
				'sym_linked' => 'Sym-Linked (saves a big amount of disk space)'
			],
			'value' => pm_Settings::get('installation_type'),
			'required' => true,
		]);
		
		
		$form->addElement('select', 'installation_database_driver', [
			'label' => 'Database Driver',
			'multiOptions' => ['mysql' => 'MySQL', 'sqlite' => 'SQL Lite'],
			'value' => pm_Settings::get('installation_database_driver'),
			'required' => true,
		]);
		
		$form->addControlButtons([
			'cancelLink' => pm_Context::getModulesListUrl(),
		]);
		
		if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
			
			// Form proccessing
			pm_Settings::set('installation_settings', $form->getValue('installation_settings'));
			pm_Settings::set('installation_type', $form->getValue('installation_type'));
			pm_Settings::set('installation_database_driver', $form->getValue('installation_database_driver'));
			
			$this->_status->addMessage('info', 'Settings was successfully saved.');
			$this->_helper->json(['redirect' => pm_Context::getBaseUrl().'index.php/index/settings']);
		}
		
		$this->view->form = $form;
		
	}
	
	private function _getDomainsList()
	{
		
		$data = [];
		$domains = pm_Domain::getAllDomains();
		
		$i = 0;
		foreach ($domains as $domain) {
			$domainDocumentRoot = $domain->getDocumentRoot();
			$domainName = $domain->getName();
			$domainIsActive = $domain->isActive();
			$domainCreation = $domain->getProperty('cr_date');
			
			$data[$i] = [
				'domain' => '<a href="#">'.$domainName.'</a>',
				'created_date' => $domainCreation,
				'type' => 'Symlink',
				'document_root' => $domainDocumentRoot,
				'active' => ($domainIsActive ? 'Yes' : 'No')
			];
			$i++;
		}
		
		$options = [
			'defaultSortField' => 'active',
			'defaultSortDirection' => pm_View_List_Simple::SORT_DIR_DOWN,
		];
		$list = new pm_View_List_Simple($this->view, $this->_request, $options);
		$list->setData($data);
		$list->setColumns([
			pm_View_List_Simple::COLUMN_SELECTION,
			'domain' => [
				'title' => 'Domain',
				'noEscape' => true,
				'searchable' => true,
			],
			'created_date' => [
				'title' => 'Created at',
				'noEscape' => true,
				'searchable' => true,
			],
			'type' => [
				'title' => 'Type',
				'noEscape' => true,
				'sortable' => false,
			],
			'active' => [
				'title' => 'Active',
				'noEscape' => true,
				'sortable' => false,
			],
			'document_root' => [
				'title' => 'Document Root',
				'noEscape' => true,
				'sortable' => false,
			],
		]);
		
		// Take into account listDataAction corresponds to the URL /list-data/
		$list->setDataUrl(['action' => 'list-data']);
		
		return $list;
	}
}