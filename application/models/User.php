<?php

class Application_Model_User
{
	protected $_id; //int
	protected $_first_name; //varchar(255)
	protected $_last_name;//varchar(255)
	protected $_user_name;//varchar(255)
	protected $_email; //varchar(255)
	protected $_password;//text

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
			throw new Exception('Invalid user property');
		}
		return $this->$method();
	}

	public function __set($name,$value)
	{
		$method = 'set' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid user property');
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

	public function getFirstName()
	{
		return $this->_first_name;
	}

	public function setFirstName($name)
	{
		$this->_first_name = (string)$name;
		return $this;
	}

	public function getLastName()
	{
		return $this->_last_name;
	}

	public function setLastName($name)
	{
		$this->_last_name = (string)$name;
		return $this;
	}

	public function getUserName()
	{
		return $this->_user_name;
	}

	public function setUserName($name)
	{
		$this->_user_name = (string)$name;
		return $this;
	}
	
	public function getEmail()
	{
		return $this->_email;
	}
	
	public function setEmail($email)
	{
		$this->_email = (string)$email;
		return $this;
	}

	public function getPassword()
	{
		return $this->_password;
	}

	public function setPassword($password)
	{
		$this->_password = md5($password);
		return $this;
	}
}

class Application_Model_UserMapper
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
			$this->setDbTable('Application_Model_DbTable_User');
		}
			
		return $this->_dbTable;
	}
	
	public function save(Application_Model_User $user)
	{
		$data = array(
				'firstname'=>$user->getFirstName(),
				'lastname'=>$user->getLastName(),
				'email'=>$user->getEmail(),
				'username'=>$user->getUserName(),
				'password'=>$user->getPassword()
		);
		if(null === ($id=$user->getId())){
			unset($data['id']);

			$this->getDbTable()->insert($data);
		}else{

			$this->getDbTable()->update($data, array('id=?'=>$user->getId()));
		}
	}
	
	public function find($id, Application_Model_User $user)
	{
		$result = $this->getDbTable()->find($id);
			
		if(0 == count($result)) {
			return;
		}
			
		$row = $result->current();
		$user->setId($row->id);
		$user->setFirstName($row->firstname);
		$user->setLastName($row->lastname);
		$user->setUserName($row->username);
		$user->setPassword($row->password);
			
	}
	
	public function fetchAll()
	{
		$resultSet = $this->getDbTable()->fetchAll();
		$entries = array();
		foreach ($resultSet as $row) {
			$entry = new Application_Model_User();
	
			$entry->setId($row->id);
			$entry->setFirstName($row->firstname);
			$entry->setLastName($row->lastname);
			$entry->setUsername($row->username);
			$entry->setPassword($row->password);
	
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

