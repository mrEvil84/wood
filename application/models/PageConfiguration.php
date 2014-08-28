<?php

class Application_Model_PageConfiguration
{
	protected $_id; //int
	protected $_name; //varchar(255)
	protected $_value;//varchar(255)
	
	

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
			throw new Exception('Invalid pageconfiguration property');
		}
		return $this->$method();
	}

	public function __set($name,$value)
	{
		$method = 'set' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid pageconfiguration property');
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

	public function getName()
	{
		return $this->_name;
	}

	public function setName($name)
	{
		$this->_name = (string)$name;
		return $this;
	}

	public function getValue()
	{
		return $this->_value;
	}

	public function setValue($value)
	{
		$this->_value = (string)$value;
		return $this;
	}
	
	public function getImagesPerPageInGallery() 
	{
		$pageConfigurationMapper = new Application_Model_PageConfigurationMapper();
		$pageConfigurationMapper->findByKey('ImagesPerPageInGallery', $this);
		return $this;
	}
	
	public function updateImagesPerPageInGallery(array $data)
	{
		$pageConfigurationMapper = new Application_Model_PageConfigurationMapper();
		
		$this->setId($data['imagesPerPageInGalleryId']);
		$this->setName('ImagesPerPageInGallery');
		$this->setValue($data['imagesPerPageInGallery']);
		
		$result = $pageConfigurationMapper->save($this);
		
		if($result == 0) {
			return true;
		}
		return false;
	}
	
}

class Application_Model_PageConfigurationMapper
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
			$this->setDbTable('Application_Model_DbTable_PageConfiguration');
		}
			
		return $this->_dbTable;
	}
	
	public function save(Application_Model_PageConfiguration $pageConfiguration)
	{
		$data = array(
				
				'name'=>$pageConfiguration->getName(),
				'value'=>$pageConfiguration->getValue()
		);
		
		if(null === ($id=$pageConfiguration->getId())){
			unset($data['id']);
			$this->getDbTable()->insert($data);
		}else{
			$result = $this->getDbTable()->update($data, array('id=?'=>$pageConfiguration->getId()));
			return $result;
		}
	}
	
	public function find($id, Application_Model_PageConfiguration $pageConfiguration)
	{
		$result = $this->getDbTable()->find($id);
			
		if(0 == count($result)) {
			return;
		}
			
		$row = $result->current();
		$pageConfiguration->setId($row->id);
		$pageConfiguration->setName($row->name);
		$pageConfiguration->setValue($row->value);
			
	}
	
	public function findByKey($key, Application_Model_PageConfiguration $pageConfiguration) 
	{
		$select = $this->getDbTable()->select()->where('name=?',$key);
		$row = $this->getDbTable()->fetchRow($select)->toArray();

		if(0 == count($row)) {
			return;
		}
		
		$pageConfiguration->setId($row['id']);
		$pageConfiguration->setName($row['name']);
		$pageConfiguration->setValue($row['value']);
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Application_Model_PageConfiguration();
	
			$entry->setId($row->id);
			$entry->setName($row->name);
			$entry->setValue($row->value);
	
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

