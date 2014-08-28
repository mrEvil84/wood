<?php
require_once(APPLICATION_PATH.'/models/User.php');

class UserController extends Zend_Controller_Action
{

    private $currentLang;


    public function init() {
		$uri = $this->_request->getPathInfo();
		@$activeNav = $this->view->navigation()->findByUri($uri);
		@$activeNav->active = true;

		$lang = new Zend_Session_Namespace('lang');
		$iterator = $lang->getIterator();
		$this->currentLang = $iterator->offsetGet('default_lang');
	}

    public function changeadminpasswordAction() {
		
    	$params = $this->_getAllParams();
    	
    	
    	if(isset($params['adminPassword']) && isset($params['confirmationAdminPassword'])){
    		if($this->passwordsAreEqual($params['adminPassword'], $params['confirmationAdminPassword'])) {
    			// update pass
    			$adminId = 1;
    			$adminMapper = new Application_Model_UserMapper();
    			$admin = new Application_Model_User();
    			$adminMapper->find($adminId, $admin);
    			$admin->setPassword($params['adminPassword']);
    			$adminMapper->save($admin);
    			$this->redirect("/image/index");
    				
    		}else{
    			$this->view->passwordsAreEqual = false;
    			$this->view->adminPassword = md5($params['adminPassword']);
    			$this->view->confirmationAdminPassword = md5($params['confirmationAdminPassword']);
    		}
    	}
    	
    	
    	
    	Zend_Layout::getMvcInstance()->assign('menuButtonIndexActive', 'active');
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'userChangeAdminPassword');
    	
    	
		$this->renderScript('user/changeAdminPassword.phtml');
	}
	
	private function passwordsAreEqual($password1,$password2) {
		if(md5($password1) == md5($password2)) {
			return true;
		} 
		return false;
	}

}