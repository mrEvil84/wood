<?php
require_once(APPLICATION_PATH.'/models/Image.php');
require_once(APPLICATION_PATH.'/models/ImageCategory.php');


class ImageController extends Zend_Controller_Action
{

    public function init()
    {
    	//login needed
    	$storage = new Zend_Auth_Storage_Session();
    	$data = $storage->read();
    	if(!$data){
    		$this->_redirect('/auth/login');
    	}
    	$this->view->username = $data->username;
    	
        /* Initialize action controller here */
    	$this->view->headLink()->appendStylesheet('/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');    
    }

    public function indexAction()
    {
        // action body
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'imageIndex');
    }

    public function uploadAction()
    {
    	$this->view->headScript()->appendFile('/js/jquery.min.js');
    	$this->view->headScript()->appendFile('/js/browserplus-min.js');
    	 
    	$this->view->headScript()->appendFile('/js/plupload/plupload.js');
    	$this->view->headScript()->appendFile('/js/plupload/plupload.gears.js');
    	$this->view->headScript()->appendFile('/js/plupload/plupload.silverlight.js');
    	$this->view->headScript()->appendFile('/js/plupload/plupload.flash.js');
    	$this->view->headScript()->appendFile('/js/plupload/plupload.browserplus.js');
    	$this->view->headScript()->appendFile('/js/plupload/plupload.html4.js');
    	$this->view->headScript()->appendFile('/js/plupload/plupload.html5.js');
    	
    	$this->view->headScript()->appendFile('/js/plupload/jquery.plupload.queue/jquery.plupload.queue.js');
    	$this->view->headScript()->appendFile('/js/pluploadFunctions.js');
    	
    	$imageCategories = new Application_Model_ImageCategoryMapper();
    	$this->view->categories=$imageCategories->fetchAll();
    	
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'imageUpload');
    	
    }
    
    public function configurationAction()
    {
    	$params = $this->getRequest()->getParams(); 
    	$pageConfiguration = new Application_Model_PageConfiguration();
    	$imagesPerPageInGallery = $pageConfiguration->getImagesPerPageInGallery();
    	if(isset($params['save'])) {
			$result = $pageConfiguration->updateImagesPerPageInGallery($params);
			$this->view->imagesPerPageInGallery = $params['imagesPerPageInGallery'];
    	}else{
			$this->view->imagesPerPageInGallery = $imagesPerPageInGallery->getValue();
			$this->view->ImagesPerPageInGalleryId = $pageConfiguration->getId();		
			Zend_Layout::getMvcInstance()->assign('bodyClassName', 'imageConfiguration');
		}
    }
    
    
    
    public function editAction()
    {
    	// action body
    	require_once(APPLICATION_PATH.'/models/Image.php');
    	Zend_Layout::getMvcInstance()->assign('bodyClassName', 'imageEdit');
    	
    	$params = $this->getRequest()->getParams(); 

    	if(@$params['ac'] == 'delete') {
    		//delete sigle image
    		$id = $params['id'];
    		$imageName = $params['imageName'];
    		$image = new Application_Model_ImageMapper();
    		$result = $image->delete($id);
    		if($result!=0){
    			//delete also file in directories!
    			unlink(APPLICATION_PATH.'/../public/galleryImages/images/'.$imageName);
    			//delete also thumbfile in directories!
    			unlink(APPLICATION_PATH.'/../public/galleryImages/thumbs/thumb_'.$imageName);
    			//redirect to images
    			$this->redirect("/image/edit?sort_category_id=".$params['sort_category_id']);
    		}else{
    			$this->redirect('/error');
    		}
    	}
    	
    	if(@$params['ac'] == 'update') {
    		$imageMapper = new Application_Model_ImageMapper();
    		$image = new Application_Model_Image();
    		$imageMapper->find($params['imageId'], $image);
    		$image->setDescription($params['imageDescription']);
    		$image->setCategoryId($params['category_id']);
    		$result = $imageMapper->save($image);
    		if($result) {
    			$this->redirect("/image/edit?sort_category_id=".$params['sort_category_id']);
    		}else{
    			$this->redirect('/error');
    		}
    		
    	}
    	
    	if(@$params['ac'] == 'sort') {
    		$imageCategories = new Application_Model_ImageCategoryMapper();
    		$gallery = new Application_Model_ImageMapper();
    		
    		if($params['sort_category_id'] == Application_Model_ImageCategory_Type::ALL_IMAGES){
    			$this->view->entries = $gallery->fetchAllExceptCategoryImage();
    			$this->view->sort_category_id = Application_Model_ImageCategory_Type::ALL_IMAGES;
    		}else{
    			$this->view->entries = $gallery->fetchAllByCategoryImage($params['sort_category_id']);	
    			$this->view->sort_category_id = $params['sort_category_id'];
    		}
    		
    		
    	} else {
    		$imageCategories = new Application_Model_ImageCategoryMapper();
    		$gallery = new Application_Model_ImageMapper();
    		 
    		$this->view->entries = $gallery->fetchAllExceptCategoryImage();
    	}
    	
    	if(!isset($params['ac'])) {
    		$gallery = new Application_Model_ImageMapper();
    		if(isset($params['sort_category_id'])){
    			
    			if($params['sort_category_id'] == Application_Model_ImageCategory_Type::ALL_IMAGES){
    				$this->view->entries = $gallery->fetchAllExceptCategoryImage();	
    			}else{
    				$this->view->entries = $gallery->fetchAllByCategoryImage($params['sort_category_id']);	
    			}
    			
    			
    			$this->view->sort_category_id = $params['sort_category_id'];
    			
    		}else {
    			$this->view->entries = $gallery->fetchAllExceptCategoryImage();
	    		$this->view->sort_category_id = Application_Model_ImageCategory_Type::ALL_IMAGES;
    		}
    	}
    	
		$this->view->categories = $imageCategories->fetchAll();    		
    
    }
    
    private function getFileExtension($fileName) {
    	$fileNameLength = strlen($fileName);
    	if($fileNameLength<=5){
    		$fileParts = explode(".", $fileName);
    		return $fileParts[1];
    	}else{
    		$lastSixCharactersFromFileName = substr($fileName,$fileNameLength-6,6);
    		$filePartsFromSixCharacters = explode(".", $lastSixCharactersFromFileName);
    		return $filePartsFromSixCharacters[1];
    	}
    }
    
    public function uploadproceedAction()
    {
   		$params = $this->_getAllParams();
    	$imageMapper = new Application_Model_ImageMapper();
    	
    	$thumb_directory =   APPLICATION_PATH.'/../public/galleryImages/thumbs';    //Thumbnail folder
    	$orig_directory = APPLICATION_PATH.'/../public/galleryImages/upload_tmp_dir';    //Full image folder
    	
    	/* Opening the thumbnail directory and looping through all the thumbs: */
    	$dir_handle = @opendir($orig_directory); //Open Full image dirrectory
    	if ($dir_handle > 1){ //Check to make sure the folder opened
    	
    		$allowed_types=array('jpg','jpeg','gif','png');
    		$file_parts=array();
    		$ext='';
    		$title='';
    		$i=0;
    	
    		while ($file = @readdir($dir_handle))
    		{
    			/* Skipping the system files: */
    			if($file=='.' || $file == '..') continue;
    	   			
    			$file_parts = explode('.',$file);    //This gets the file name of the images
    			$ext = strtolower($this->getFileExtension($file));
    			$file_parts[1] = $ext;
    			$file = $file_parts[0] .'.'. $ext;
    	
    			/* Using the file name (withouth the extension) as a image title: */
    			$title = implode('.',$file_parts);
    			$title = htmlspecialchars($title);
    	
    			/* If the file extension is allowed: */
    			if(in_array($ext,$allowed_types))
    			{
    				/* If you would like to inpute images into a database, do your mysql query here */
    				/* The code past here is the code at the start of the tutorial */
    				/* Outputting each image: */
    	
    				$nw = 225;
    				$nh = 150;
    				$source = APPLICATION_PATH."/../public/galleryImages/upload_tmp_dir/{$file}";
    				$stype = explode(".", $source);
    				$stype = $stype[count($stype)-1];
    				$dest = APPLICATION_PATH."/../public/galleryImages/thumbs/thumb_{$file}";

    				//set data to database -------------------
    				$image = new Application_Model_Image();
    				$image->setImageName($file);
    				$image->setThumbName('thumb_'.$file);
    				$image->setUploadDate(date("Ymd_His"));
    				$image->setDescription('');
    				$image->setCategoryId($params['category_id']);
    				$imageMapper->save($image);
    				//copy file in normal size!---------------
    				copy(APPLICATION_PATH."/../public/galleryImages/upload_tmp_dir/{$file}",APPLICATION_PATH."/../public/galleryImages/images/{$file}");
    				//create thumb!
    				$size = getimagesize($source);
    				$w = $size[0];
    				$h = $size[1];
    	
    				switch($stype) {
    					case 'gif':
    						$simg = imagecreatefromgif($source);
    						break;
    					case 'jpg':
    						$simg = imagecreatefromjpeg($source);
    						break;
    					case 'jpeg':
    						$simg = imagecreatefromjpeg($source);
    						break;
    					case 'png':
    						$simg = imagecreatefrompng($source);
    						break;
    				}
    	
    				$dimg = imagecreatetruecolor($nw, $nh);
    				$wm = $w/$nw;
    				$hm = $h/$nh;
    				$h_height = $nh/2;
    				$w_height = $nw/2;
    	
    				if($w> $h) {
    					$adjusted_width = $w / $hm;
    					$half_width = $adjusted_width / 2;
    					$int_width = $half_width - $w_height;
    					imagecopyresampled($dimg,$simg,-$int_width,0,0,0,$adjusted_width,$nh,$w,$h);
    				} elseif(($w <$h) || ($w == $h)) {
    					$adjusted_height = $h / $wm;
    					$half_height = $adjusted_height / 2;
    					$int_height = $half_height - $h_height;
    	
    					imagecopyresampled($dimg,$simg,0,-$int_height,0,0,$nw,$adjusted_height,$w,$h);
    				} else {
    					imagecopyresampled($dimg,$simg,0,0,0,0,$nw,$nh,$w,$h);
    				}
    				imagejpeg($dimg,$dest,100);
    				//delete file from tmp directory!
    				unlink(APPLICATION_PATH."/../public/galleryImages/upload_tmp_dir/".$file);
    			}
    		}
    		/* Closing the directory */
    		@closedir($dir_handle);
    	}	

		//redirect to image edit
		$this->_redirect('/image/edit');
    }

    public function startuploadAction()
    {
    	$targetDir =  APPLICATION_PATH.'/../public/galleryImages/upload_tmp_dir';

    	$cleanupTargetDir = true; // Remove old files
    	$maxFileAge = 5 * 3600; // Temp file age in seconds

    	// 5 minutes execution time
    	@set_time_limit(5 * 60);

    	// Uncomment this one to fake upload time
    	// usleep(5000);

    	// Get parameters
    	$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
    	$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
    	$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';

    	// Clean the fileName for security reasons
    	$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
    	// Make sure the fileName is unique but only if chunking is disabled
    	if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
    		$ext = strrpos($fileName, '.');
    		$fileName_a = substr($fileName, 0, $ext);
    		$fileName_b = substr($fileName, $ext);
    		 
    		$count = 1;
    		while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
    			$count++;
    		 
    		$fileName = $fileName_a . '_' . $count . $fileName_b;
    	}

    	$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

    	// Create target dir
    	if (!file_exists($targetDir))
    		@mkdir($targetDir);

    	// Remove old temp files
    	if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
    		while (($file = readdir($dir)) !== false) {
    			$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

    			// Remove temp file if it is older than the max age and is not the current file
    			if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
    				@unlink($tmpfilePath);
    			}
    		}
    		 
    		closedir($dir);
    	} else
    		die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');


    	// Look for the content type header
    	if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
    		$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

    	if (isset($_SERVER["CONTENT_TYPE"]))
    		$contentType = $_SERVER["CONTENT_TYPE"];

    	// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
    	if (strpos($contentType, "multipart") !== false) {
    		if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
    			// Open temp file
    			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
    			if ($out) {
    				// Read binary input stream and append it to temp file
    				$in = fopen($_FILES['file']['tmp_name'], "rb");
    					
    				if ($in) {
    					while ($buff = fread($in, 4096))
    						fwrite($out, $buff);
    				} else
    					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
    				fclose($in);
    				fclose($out);
    				@unlink($_FILES['file']['tmp_name']);
    			} else
    				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    		} else
    			die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
    	} else {
    		// Open temp file
    		$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
    		if ($out) {
    			// Read binary input stream and append it to temp file
    			$in = fopen("php://input", "rb");

    			if ($in) {
    				while ($buff = fread($in, 4096))
    					fwrite($out, $buff);
    			} else
    				die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

    			fclose($in);
    			fclose($out);
    		} else
    			die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
    	}

    	// Check if file has been uploaded
    	if (!$chunks || $chunk == $chunks - 1) {
    		// Strip the temp .part suffix off
    		rename("{$filePath}.part", $filePath);
    	}
    	
    	
    	$dir = opendir($targetDir);
    	while (($file = readdir($dir)) !== false) {
    		echo $file . '<br/>';
    	}

    	// Return JSON-RPC response
    	die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }


}





