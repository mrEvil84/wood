<?php

class AuthController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
    	
    	$this->_redirect('auth/login');
    }

    public function loginAction()
    {
        // action body
    	$request = $this->getRequest();
    	$form    = new Application_Form_Login();
    	
    	if ($this->getRequest()->isPost()) {
    		if ($form->isValid($request->getPost())) {
    			$data = $form->getValues();
    			$auth = Zend_Auth::getInstance();
    			$user = new Application_Model_DbTable_User();
    			
    			$authAdapter = new Zend_Auth_Adapter_DbTable($user->getAdapter(),'user');
    			$authAdapter->setIdentityColumn('username')->setCredentialColumn('password');
    			$authAdapter->setIdentity($data['username'])->setCredential(md5($data['password']));
    			$result = $auth->authenticate($authAdapter);
    			
    			
    			
    			if($result->isValid()) {
    				$storage = new Zend_Auth_Storage_Session();
    				$storage->write($authAdapter->getResultRowObject());
    				
    				//$data = $storage->read();
    				//pass data to layout 
    				//Zend_Layout::getMvcInstance()->assign('varname', 'Var');
    				//$this->_helper->layout()->logged = true;
    				//$this->_helper->layout()->title = 'Admission Office.net!';
    				//var_dump($this->_helper->layout()); die;
    				
    				$this->_redirect('image/');
    				
    			} else {
    				$this->_redirect('auth/loginfail');
    			}

    		}
    	}
    	
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'login');
    	$this->view->form = $form;
    }

    public function logoutAction()
    {
        // action body
    	$storage = new Zend_Auth_Storage_Session();
    	$storage->clear();
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'logout');
    	$this->_redirect('index/');
    }

    public function loginfailAction()
    {
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'loginfail');
        // action body
    }


}







