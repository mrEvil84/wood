<?php

class Application_Form_Login extends Zend_Form
{

	public function init()
	{
		/* Form Elements & Other Definitions Here ... */

		$username = $this->createElement('text','username');
		$username->setLabel('Login: *')
		->setRequired(true);
		 
		$password = $this->createElement('password','password');
		$password->setLabel('Hasło: *')
		->setRequired(true);
		 
		$signin = $this->createElement('submit','signin');
		$signin->setLabel('Zaloguj')
		->setIgnore(true);
		 
		$this->addElements(array(
				$username,
				$password,
				$signin,
		));
		 
	}


}

