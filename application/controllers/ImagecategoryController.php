<?php
require_once(APPLICATION_PATH.'/models/ImageCategory.php');
require_once(APPLICATION_PATH.'/models/Image.php');

class ImageCategoryController extends Zend_Controller_Action
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
		$this->view->headLink()->appendStylesheet('/js/plupload/jquery.plupload.queue/css/jquery.plupload.queue.css');
	}

	public function addAction() {

		$params = $this->_getAllParams();
		Zend_Layout::getMvcInstance()->assign('bodyClassName', 'categoryAdd');
		
		if(isset($params['categoryName'])) {
			$this->uploadFileToTempDir();
			$thumbImageId = $this->generateThumbImage();
			$imageCategory = new Application_Model_ImageCategory();

			
			if($params['update'] == "true") {
				$imageCategory->setId($params['categoryId']);
			}
				
			$imageCategory->setImageCategoryName($params['categoryName']);
			$imageCategory->setImageCategoryThumbId($thumbImageId);
			$imageCategoryMapper = new Application_Model_ImageCategoryMapper();
			$imageCategoryMapper->save($imageCategory);
			
			$this->_redirect('/imagecategory/list');
		} else {
			
			$this->renderScript('/imagecategory/add.phtml');
		}
			
	}

	public function listAction() {
			
		$imageCategoryMapper = new Application_Model_ImageCategoryMapper();
		$categories = $imageCategoryMapper->fetchAll();

		$imageMapper = new Application_Model_ImageMapper();
		$image = new Application_Model_Image();

		$categoryCollection = array();
		$counter = 0;

		foreach($categories as $category) {
			$imageMapper->find($category->getImageCategoryThumbId(), $image);
			$data = array();
			$data['categoryThumbName'] = $image->getThumbName();
			$data['categoryId'] = $category->getId();
			$data['categoryName'] = $category->getImageCategoryName();
			$data['categoryThumbId'] = $category->getImageCategoryThumbId();
			$categoryCollection[$counter] = $data;
			$counter++;
		}
		
		Zend_Layout::getMvcInstance()->assign('bodyClassName', 'categoryList');
		
		$this->view->categories = $categoryCollection;
		$this->view->thumbsSourcePath = APPLICATION_PATH."/../public/galleryImages/thumbs/";
		$this->renderScript('/imagecategory/list.phtml');

	}

	public function imageupdateAction() {
		$params = $this->_getAllParams();

		$categoryId = $params['categoryId'];
		$categoryImageThumbId = $params['categoryThumbId'];
		$categoryName = $params['categoryName'];

		//delete sigle image category image
		$imageMapper = new Application_Model_ImageMapper();
		$image = new Application_Model_Image();
		$imageMapper->find($categoryImageThumbId,$image);

		$id = $image->getId();
		$imageName = $image->getImageName();

		$result = $imageMapper->delete($id);
		if($result!=0){
			//delete also file in directories!
			unlink(APPLICATION_PATH.'/../public/galleryImages/images/'.$imageName);
			//delete also thumbfile in directories!
			unlink(APPLICATION_PATH.'/../public/galleryImages/thumbs/thumb_'.$imageName);
		}

		$this->view->update = "true";
		$this->view->categoryId = $categoryId;
		$this->view->categoryName = $categoryName;
		$this->renderScript('imagecategory/add.phtml');
	}

	public function deleteAction() {
		$params = $this->_getAllParams();

		$categoryId = $params['categoryId'];
		$categoryThumbId = $params['categoryThumbId'];

		//delete from database
		$categoryMapper = new Application_Model_ImageCategoryMapper();
		$categoryMapper->delete($categoryId);

		//update all images in gallery images goes to gallery category
		$imageMapper = new Application_Model_ImageMapper();
		$imagesInCategory = $imageMapper->fetchByCategory($categoryId);

		foreach($imagesInCategory as $imageInCategory) {
			$imageInCategory->setCategoryId(Application_Model_ImageCategory_Type::GALLERY);
			$imageMapper->save($imageInCategory);
		}

		//delete sigle image category image
		$imageMapper = new Application_Model_ImageMapper();
		$image = new Application_Model_Image();
		$imageMapper->find($categoryImageThumbId,$image);

		$id = $image->getId();
		$imageName = $image->getImageName();

		$result = $imageMapper->delete($id);
		if($result!=0){
			//delete also file in directories!
			unlink(APPLICATION_PATH.'/../public/galleryImages/images/'.$imageName);
			//delete also thumbfile in directories!
			unlink(APPLICATION_PATH.'/../public/galleryImages/thumbs/thumb_'.$imageName);
			//redirect to images
		}
		
		$this->_redirect('/imagecategory/list');
	}

	//------------------------private functions--------------------------------------------
	private function getFileExtension($fileName) {
		$fileParts = explode(".", $fileName);
		return $fileParts[1];
	}

	private function generateUniqueImageFileName($fileExtension) {

		$currentDate = date('Y-m-d H:i:s');
		$currentDateHash = md5($currentDate);

		$uniqueImageFileName = $currentDateHash . '.' . $fileExtension;
		return $uniqueImageFileName;
	}

	private function uploadFileToTempDir() {

		$targetDir =  APPLICATION_PATH.'/../public/galleryImages/upload_tmp_dir';
		$adapter = new Zend_File_Transfer_Adapter_Http();
		$adapter->setDestination($targetDir);
		


		if (!$adapter->receive()) {
			$messages = $adapter->getMessages($targetDir);
			echo implode("\n", $messages);
		}

		$upload = new Zend_File_Transfer();
		
		//$upload->addValidator('Extension', false, array('jpg','jpeg','gif','png'));
		//$adapter->addValidator('Extension', false, array('jpg','jpeg','gif','png'));
		
		$fileInfo = $adapter->getFileInfo();

		$uploadedFileName = $fileInfo['categoryImage']['name'];
		$uploadedFileExtension = $this->getFileExtension($uploadedFileName);
		
		//TODO: usprawnic metodę zwracającą rozszerzenie pliku z nietypowymi nazwami
		$newUploadedFileName = $this->generateUniqueImageFileName($uploadedFileExtension);	
		//var_dump($uploadedFileExtension);
		//die("File in temp dir!");		
		
		rename($targetDir."/".$uploadedFileName, $targetDir."/".$newUploadedFileName);
	}

	private function insertFileInformationsToDatabase($file) {

		$imageMapper = new Application_Model_ImageMapper();
		$image = new Application_Model_Image();
		$image->setImageName($file);
		$image->setThumbName('thumb_'.$file);
		$image->setUploadDate(date("Ymd_His"));
		$image->setDescription('none');
		$image->setCategoryId(Application_Model_ImageCategory_Type::CATEGORY);
		$imageId = $imageMapper->save($image);
		return $imageId;
	}

	private function generateThumbImage() {
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
				$ext = strtolower(array_pop($file_parts));
					
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
					$imageId = $this->insertFileInformationsToDatabase($file);

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
		return $imageId;
	}

}