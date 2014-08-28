<?php

class ImageSupport {
	
	
	private $imageRepositoryPath;
	private $imageThumbRepositoryPath;
	
	public function __construct() {
		$this->imageRepositoryPath = APPLICATION_PATH.'/../public/galleryImages/images/';
		$this->imageThumbRepositoryPath = APPLICATION_PATH.'/../public/galleryImages/thumbs/';
	}
	
	public function setImageRepositoryPath($imageRepositoryPath) {
		$this->imageRepositoryPath = $imageRepositoryPath;
	}
	
	public function getImageRepositoryPath() {
		return $this->imageRepositoryPath;
	}
	
	public function setImageThumbRepositoryPath($imageThumbRepositoryPath) {
		$this->imageThumbRepositoryPath = $imageThumbRepositoryPath;
	}
	
	public function getImageThumbRepositoryPath() {
		return $this->imageThumbRepositoryPath;
	}
	
	protected function deleteImageFromDisc($fileName) {
		unlink($this->imageRepositoryPath.$fileName);
	}
	
	protected function deleteThumbFromDisc($thumbName) {
		unlink($this->imageThumbRepositoryPath.$thumbName);	
	}
	
	public function deleteImageAndThumbFromDisc($fileName) {
		$this->deleteImageFromDisc($fileName);
		$this->deleteThumbFromDisc('thumb_'.$fileName);
	}
	
	
}