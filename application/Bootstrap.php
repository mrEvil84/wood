<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
	protected function _initDoctype()
	{
		$this->bootstrap('view');
		$view = $this->getResource('view');
		$view->doctype('XHTML1_STRICT');
		$view->headTitle('Konstrukcje Drewniane')->setSeparator(' :: ');
	}

	protected function _initNavigation()
	{
		$this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navigation.xml','nav');
		$navigation = new Zend_Navigation($config);
		$view->navigation($navigation);
	}

	protected function _initSession()
	{
		Zend_Session::start();
		$lang = new Zend_Session_Namespace('lang');
		if (!isset($lang->default_lang)) {
			//only one time set default lang!
			$lang->default_lang = 'pl';
		}
	}


	protected function _initMenuTranslations()
	{
		$menuLang = array(
				'pl'=>array(
						'pageName'=>'Konstrukcje Drewniane',
						'mainPage'=>'Strona główna',
						'company'=>'Firma',
						'technology'=>'Technologia',
						'gallery'=>'Galeria',
						'installment'=>'Raty',
						'auctions'=>'Aukcje',
						'about'=>'Kontakt'
				),
				'en'=>array(
						'pageName'=>'-to-translation-',
						'mainPage'=>'-to-translation-',
						'forWomans'=>'-to-translation-',
						'forMans'=>'-to-translation-',
						'forCompanysAndGroups'=>'-to-translation-',
						'gallery'=>'-to-translation-',
						'about'=>'-to-translation-',
				),
				'de'=>array(
						'pageName'=>'-to-translation-',
						'mainPage'=>'-to-translation-',
						'forWomans'=>'-to-translation-',
						'forMans'=>'-to-translation-',
						'forCompanysAndGroups'=>'-to-translation-',
						'gallery'=>'-to-translation-',
						'about'=>'-to-translation-',
				)
		);
			
		$basicDataLang = array (
				'pl'=>array(
						'pageName'=>'Konstrukcje Drewniane',
						'companyOwner'=>'G.O.Maciej Grzywacz'
				)
		);
			
		Zend_Registry::set('menu_lang', $menuLang);
		Zend_Registry::set('basic_data_lang',$basicDataLang);
	}
}

