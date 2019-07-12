<?php
// Copyright 1999-2017. Plesk International GmbH.

class Modules_Wesellin_EventListener implements EventListener
{
    public function handleEvent($objectType, $objectId, $action, $oldValue, $newValue)
    {
    	// https://github.com/plesk/ext-aps-autoprovision/blob/master/src/plib/library/EventListener.php
    	
    	if ($action == 'phys_hosting_create') {
    		
    		$newInstallation = new Modules_Wesellin_Install();
    		$newInstallation->setDomainId($objectId);
    		$newInstallation->setVersion(1);
    		$newInstallation->setType('default');
    		$newInstallation->run();
    		
    	}
    }
}

return new Modules_Wesellin_EventListener();