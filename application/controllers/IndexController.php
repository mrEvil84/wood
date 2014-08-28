<?php

class IndexController extends Zend_Controller_Action
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

    public function indexAction() {
		// action body    
    	Zend_Layout::getMvcInstance()->assign('menuButtonIndexActive', 'active');
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'index');
		$this->renderScript('index/index_' . $this->currentLang . '.phtml');
	}

    public function companyAction() {
    	Zend_Layout::getMvcInstance()->assign('menuButtonCompanyActive', 'active');
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'company');
		$this->renderScript('index/company_' . $this->currentLang . '.phtml');
	}

    public function technologyAction()
    {
    	Zend_Layout::getMvcInstance()->assign('menuButtonTechnologyActive', 'active');
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'technology');
		$this->renderScript('index/technology_' . $this->currentLang . '.phtml'); 
    }

    public function installmentAction() {
		//raty
    	Zend_Layout::getMvcInstance()->assign('menuButtonInstallmentActive', 'active');
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'installment');
		$this->renderScript('index/installment_' . $this->currentLang . '.phtml');
	}
	
	public function auctionsAction() {
		//aukcje
		Zend_Layout::getMvcInstance()->assign('menuButtonAuctionsActive', 'active');
		Zend_Layout::getMvcInstance()->assign('bodyClassName', 'auctions');
		$this->renderScript('index/auctions_' . $this->currentLang . '.phtml');
	}

    public function galleryAction()
    {
		require_once(APPLICATION_PATH.'/models/Image.php');
		require_once(APPLICATION_PATH.'/models/PageConfiguration.php');
		require_once(APPLICATION_PATH.'/models/ImageCategory.php');
		
		
		$params = $this->_getAllParams();
		$categoryId = 0;
		if(isset($params['category_id'])) {
			$categoryId = $params['category_id'];
		}
		
		$pageConfiguration = new Application_Model_PageConfiguration();
		$imagesPerPageInGallery = $pageConfiguration->getImagesPerPageInGallery();
		
	
		
		$gallery = new Application_Model_ImageMapper();
		if($categoryId == Application_Model_ImageCategory_Type::GALLERY) {
			$this->view->entries = $gallery->fetchAllExceptCategoryImage();
			$this->view->categoryName = "Galeria";
		} else {
			$this->view->entries = $gallery->fetchAllByCategoryImage($categoryId);
			$categoryMapper = new Application_Model_ImageCategoryMapper();
			$imageCategory = new Application_Model_ImageCategory(); 
			$categoryMapper->find($categoryId,$imageCategory);
			$this->view->categoryName = $imageCategory->getImageCategoryName();
		}

		
		$page=$this->_getParam('page',1);
		$paginator = Zend_Paginator::factory($this->view->entries);
		$paginator->setItemCountPerPage($imagesPerPageInGallery->getValue());
		$paginator->setCurrentPageNumber($page);
		
		$this->view->imagesPerPageInGallery = $imagesPerPageInGallery;
		$this->view->categoryId = $categoryId;
		$this->view->paginator=$paginator;
		$this->view->page = $page;
		Zend_Layout::getMvcInstance()->assign('menuButtonGalleryActive', 'active');
		Zend_Layout::getMvcInstance()->assign('bodyClassName', 'gallery');
		$this->renderScript('index/gallery_' . $this->currentLang . '.phtml');
    }
    
    public function gallerycategoryAction() {
    	require_once(APPLICATION_PATH.'/models/Image.php');
    	require_once(APPLICATION_PATH.'/models/PageConfiguration.php');
    	require_once(APPLICATION_PATH.'/models/ImageCategory.php');
    	
    	
    	$pageConfiguration = new Application_Model_PageConfiguration();
    	$imagesPerPageInGallery = $pageConfiguration->getImagesPerPageInGallery();

    	//pobierz kategorie 
    	$categoriesMapper = new Application_Model_ImageCategoryMapper();
    	
    	$categories = $categoriesMapper->fetchAll();

    	//pobierz miniaturki kategorii
    	$entries = array();
    	$imageMapper = new Application_Model_ImageMapper();
    	$image = new Application_Model_Image();

    	if(empty($categories)) {
    		$entry = array();
    		//$entry['categoryId'] = 0;
    		//$entry['categoryName'] = "Galeria";
    		//$entry['categoryThumbName'] = '/img/gallery_icon.jpg';
    		//$entries[0] = $entry;
    	}else {
    		$entry = array();
    		//$entry['categoryId'] = 0;
    		//$entry['categoryName'] = "Galeria";
    		//$entry['categoryThumbName'] = '/img/gallery_icon.jpg';
    		//$entries[0] = $entry;
    		
    		$entriesCounter = 1;
    		foreach($categories as $category) {
    			$imageMapper->find($category->getImageCategoryThumbId(), $image);
    			$entry = array();
    			$entry['categoryId'] = $category->getId();
    			$entry['categoryName'] = $category->getImageCategoryName();
    			$entry['categoryThumbName'] = '/galleryImages/thumbs/'.$image->getThumbName();
    			$entries[$entriesCounter] = $entry;
    			$entriesCounter++;
    		}
    	}
    	
    	$rowMax = 3;
    	
    	$rows = array();
    	$row = array();
    	
    	$rowsCounter = 0;
    	$rowCounter = 0;
    	

    	
    	foreach($entries as $entry) {
    		if($rowCounter == $rowMax) {
    			$rowsCounter++;
    			$rows[$rowsCounter][$rowCounter] = $entry;
    			$rowCounter = 0;
    		}else{
    			$rows[$rowsCounter][$rowCounter] = $entry;
    			$rowCounter++;
    		}
    	}

    	
    	$this->view->rows = $rows;
    	Zend_Layout::getMvcInstance()->assign('menuButtonGalleryActive', 'active');
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'gallerycategory');
    	$this->renderScript('index/gallerycategory_' . $this->currentLang . '.phtml');
    }

    public function showsingleimageAction()
    {
    	require_once(APPLICATION_PATH.'/models/Image.php');
    	
		$params = $this->getRequest()->getParams();
				
		$image = new Application_Model_Image();
		$imageMapper = new Application_Model_ImageMapper();
		$imageMapper->find($params['imageId'], $image);
		
		$this->view->imageId = $params['imageId'];
		$this->view->imageName = $image->getImageName();
		$this->view->imageDescription = $image->getDescription();
		$this->view->categoryId = $params['category_id'];
		$this->view->page = $this->_getParam('page',1);
		Zend_Layout::getMvcInstance()->assign('menuButtonGalleryActive', 'active');
		Zend_Layout::getMvcInstance()->assign('bodyClassName', 'showsingleimage');
		$this->renderScript('index/showsingleimage_' . $this->currentLang . '.phtml');
    }

    public function aboutAction()
    {
    	Zend_Layout::getMvcInstance()->assign('menuButtonAboutActive', 'active');
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'about');
		$this->renderScript('index/about_' . $this->currentLang . '.phtml'); 
    }

    public function sitemapAction(){
		$this->view->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
		echo $this->view->navigation()->sitemap();
		Zend_Layout::getMvcInstance()->assign('bodyClassName', 'sitemap');
    }
}