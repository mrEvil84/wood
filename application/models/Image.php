<?php
class Application_Model_Image
{

	protected $_id; //int
	protected $_image_name; //varchar(255)
	protected $_thumb_name;//varchar(255)
	protected $_upload_date;//varchar(255)
	protected $_description;//text
	protected $_category_id; //int

	public function __construct(array $options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}

	public function setOptions(array $options)
	{
		$methods = get_class_methods($this);
		foreach ($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (in_array($method, $methods)) {
				$this->$method($value);
			}
		}
		return $this;
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid guestbook property');
		}
		return $this->$method();
	}

	public function __set($name,$value)
	{
		$method = 'set' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid guestbook property');
		}
		$this->$method($value);
	}

	public function getId()
	{
		return $this->_id;
	}

	public function setId($id)
	{
		$this->_id = (int)$id;
		return $this;
	}

	public function getImageName()
	{
		return $this->_image_name;
	}

	public function setImageName($name)
	{
		$this->_image_name = (string)$name;
		return $this;
	}

	public function getThumbName()
	{
		return $this->_thumb_name;
	}

	public function setThumbName($name)
	{
		$this->_thumb_name = (string)$name;
		return $this;
	}

	public function getUploadDate()
	{
		return $this->_upload_date;
	}

	public function setUploadDate($date)
	{
		$this->_upload_date = (string)$date;
		return $this;
	}

	public function getDescription()
	{
		return $this->_description;
	}

	public function setDescription($description)
	{
		$this->_description = $description;
		return $this;
	}
	
	public function getCategoryId() {
		return $this->_category_id;
	}
	
	public function setCategoryId($categoryId) {
		$this->_category_id = $categoryId;
		return $this->_category_id;
	}

}

class Application_Model_ImageMapper
{
	protected $_dbTable;

	public function setDbTable($dbTable)
	{
		if(is_string($dbTable)){
			$dbTable = new $dbTable();
		}
			
		if (!$dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception('Invalid table data gateway provided');
		}
			
		$this->_dbTable = $dbTable;
		return $this;
	}

	public function getDbTable()
	{
		if(null===$this->_dbTable) {
			$this->setDbTable('Application_Model_DbTable_Image');
		}
			
		return $this->_dbTable;
	}

	public function save(Application_Model_Image $image)
	{
		$data = array(
				'image_name'=>$image->getImageName(),
				'thumb_name'=>$image->getThumbName(),
				'upload_date'=>$image->getUploadDate(),
				'description'=>$image->getDescription(),
				'category_id'=>$image->getCategoryId()
		);
		if(null === ($id=$image->getId())){
			unset($data['id']);
			
			$id = $this->getDbTable()->insert($data);
			return $id;
		}else{
			//update
			$this->find($image->getId(), $image);

			if($image!=null) {
					
				if(!isset($data['image_name'])) {
					$data['image_name'] = $image->getImageName();
				}
					
				if(!isset($data['thumb_name'])) {
					$data['thumb_name'] = $image->getThumbName();
				}

				if(!isset($data['upload_date'])) {
					$data['upload_date'] = $image->getUploadDate();
				}
					
				if(!isset($data['description'])) {
					$data['description'] = $image->getDescription();
				}
				
				if(!isset($data['category_id'])) {
					echo "xx";
					$data['category_id'] = $image->getCategoryId();
				}	
				$this->getDbTable()->update($data, array('id=?'=>$image->getId()));
				return true;
			}
		}
	}

	public function find($id, Application_Model_Image $image)
	{
		$result = $this->getDbTable()->find($id);
		if(0 == count($result)) {
			return;
		}
		$row = $result->current();
		$image->setId($row->id);
		$image->setImageName($row->image_name);
		$image->setThumbName($row->thumb_name);
		$image->setUploadDate($row->upload_date);
		$image->setDescription($row->description);
		$image->setCategoryId($row->category_id);
	}

	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Application_Model_Image();

			$entry->setId($row->id);
			$entry->setImageName($row->image_name);
			$entry->setThumbName($row->thumb_name);
			$entry->setUploadDate($row->upload_date);
			$entry->setDescription($row->description);
			$entry->setCategoryId($row->category_id);
			$entries[] = $entry;
		}
		return $entries;
	}
	
	public function fetchAllExceptCategoryImage(){
		$table = $this->getDbTable();
		
		$resultSet = $table->fetchAll($table->select()->where('category_id != ?', Application_Model_ImageCategory_Type::CATEGORY));
		
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Application_Model_Image();
		
			$entry->setId($row->id);
			$entry->setImageName($row->image_name);
			$entry->setThumbName($row->thumb_name);
			$entry->setUploadDate($row->upload_date);
			$entry->setDescription($row->description);
			$entry->setCategoryId($row->category_id);
			$entries[] = $entry;
		}
		return $entries;		
	}
	
	
	public function fetchAllByCategoryImage($categoryId) {
		$table = $this->getDbTable();
		
		$resultSet = $table->fetchAll($table->select()->where('category_id != -1 AND category_id = ?', $categoryId));
		
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Application_Model_Image();
		
			$entry->setId($row->id);
			$entry->setImageName($row->image_name);
			$entry->setThumbName($row->thumb_name);
			$entry->setUploadDate($row->upload_date);
			$entry->setDescription($row->description);
			$entry->setCategoryId($row->category_id);
			$entries[] = $entry;
		}
		return $entries;
	}
	
	public function fetchByCategory($imageCategoryId){
		$table = $this->getDbTable();
		
		$resultSet = $table->fetchAll($table->select()->where('category_id = ?', $imageCategoryId));
		
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Application_Model_Image();
		
			$entry->setId($row->id);
			$entry->setImageName($row->image_name);
			$entry->setThumbName($row->thumb_name);
			$entry->setUploadDate($row->upload_date);
			$entry->setDescription($row->description);
			$entry->setCategoryId($row->category_id);
			$entries[] = $entry;
		}
		return $entries;
	}
	
	public function delete($id)
	{
		$db = Zend_Db_Table::getDefaultAdapter();
		$where = $db->quoteInto('id = ?', $id);
		$result = $this->getDbTable()->delete($where);
		return $result;
	}
}