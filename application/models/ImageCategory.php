<?php
class Application_Model_ImageCategory_Type {
	const GALLERY = 0;
	const CATEGORY = -1;
	const ALL_IMAGES = -2;
}

class Application_Model_ImageCategory
{

	protected $_id; //int
	protected $_name; //varchar(255)
	protected $_thumb_image_id; //varchar(255)

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

	public function getImageCategoryName()
	{
		return $this->_name;
	}

	public function setImageCategoryName($categoryName)
	{
		$this->_name = (string)$categoryName;
		return $this;
	}

	public function getImageCategoryThumbId() {
		return $this->_thumb_image_id;
	}

	public function setImageCategoryThumbId($thumbImageId) {
		$this->_thumb_image_id = (string)$thumbImageId;
		return $this;
	}
}

class Application_Model_ImageCategoryMapper
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
			$this->setDbTable('Application_Model_DbTable_ImageCategory');
		}
			
		return $this->_dbTable;
	}

	public function save(Application_Model_ImageCategory $imageCategory)
	{

		$data = array(
				'name'=>$imageCategory->getImageCategoryName(),
				'thumb_image_id'=>$imageCategory->getImageCategoryThumbId()
		);

		if(null === ($id=$imageCategory->getId())){
			unset($data['id']);
			$this->getDbTable()->insert($data);
		}else{
			//update
			$this->find($imageCategory->getId(), $imageCategory);
				
			if($imageCategory!=null) {
					
				if($data['name'] == null ) {
					//update->do nothing!
					$data['name'] = $imageCategory->getImageName();
				}

				if($data['thumb_image_id'] == null) {
					$data['thumb_image_id'] = $imageCategory->getImageCategoryThumbId();
				}
					
				$this->getDbTable()->update($data, array('id=?'=>$imageCategory->getId()));
				return true;
			}
		}
	}

	public function find($id, Application_Model_ImageCategory $imageCategory)
	{
		$result = $this->getDbTable()->find($id);
			
		if(0 == count($result)) {
			return;
		}
			
		$row = $result->current();
		$imageCategory->setId($row->id);
		$imageCategory->setImageCategoryName($row->name);
			
	}

	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Application_Model_ImageCategory();

			$entry->setId($row->id);
			$entry->setImageCategoryName($row->name);
			$entry->setImageCategoryThumbId($row->thumb_image_id);
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