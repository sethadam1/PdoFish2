<?php

class PdoFish2 { 
	
	var $pdo;
	var $table = null;
	var $creds = array(); 

	function __construct($creds=[]) 
	{ 
		$db = $creds['database']; 
		$u	= $creds['username']; 
		$pw = $creds['password'];
		if(''==$db || '' == $u || '' == $pw) { 
			throw new Exception("You must provide a database, username, and password to proceed!"); 
		}
		$h 	= $creds['host'] ?? 'localhost'; 
		$ch = $creds['charset'] ?? 'utf8'; 
		$pt = $creds['port'] ?? "3006"; 
		$this->pdo = new PDO($ty.":host=".$h.";port=".$pt.";dbname=".$db.";charset=".$ch, $u, $pw);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	public function delete_many(string $column, $vals)
	{
		$str = (is_array($vals)) ? implode(",", $vals) : $vals;
		$stmt = $this->run("DELETE FROM `".$this->table."` WHERE $column IN (".$str.")");
		return $stmt->rowCount();
	}
	
	public function delete_by_id($val)
	{
		$stmt = $this->run("DELETE FROM `".$this->table."` WHERE id = ?", [$val]);
		return $stmt->rowCount();
	}
	
	public function delete_by_column($col, $val)
	{
		$stmt = $this->run("DELETE FROM `".$this->table."` WHERE ".$col." = ?", [$val]);
		return $stmt->rowCount();
	}
	
	public function set_table($tbl) { 
		$this->table = $tbl; 
		return $this; 
	}

	public function use_table($tbl) { 
		$this->table = $tbl; 
	}
	
	public function count($data=[])  
	{
		$stmt = $this->process($data);
		return (int) $stmt->rowCount();
	}
		
	public function delete($where=[], $limit = NULL)
	{
		//collect the values from collection
		$values = array_values($where);
	
		//setup where
		$whereDetails = null;
		$i = 0;
		foreach ($where as $key => $value) {
			$whereDetails .= $i == 0 ? "$key = ?" : " AND $key = ?";
			$i++;
		}
	
		//if limit is a number use a limit on the query
		if (is_numeric($limit)) {
			$limit = "LIMIT $limit";
		}
		error_log("DELETE FROM `".$this->table."` WHERE $whereDetails", $values);
		$stmt = $this->run("DELETE FROM `".$this->table."` WHERE $whereDetails", $values);
		return $stmt->rowCount();
	}
	
	public function update(array $data, array $where)
	{
		//merge data and where together
		$collection = array_merge($data, $where);
	
		//collect the values from collection
		$values = array_values($collection);
	
		//setup fields
		$fieldDetails = null;
		foreach ($data as $key => $value) {
			$fieldDetails .= "$key = ?,";
		}
		$fieldDetails = rtrim($fieldDetails, ',');
	
		//setup where
		$whereDetails = null;
		$i = 0;
		foreach ($where as $key => $value) {
			$whereDetails .= $i == 0 ? "$key = ?" : " AND $key = ?";
			$i++;
		}
		$stmt = $this->run("UPDATE `".$this->table."` SET $fieldDetails WHERE $whereDetails", $values);
		return $stmt->rowCount();
	}	
	
	// an alias for insert()
	public function create($data)
	{
		return $this->insert($tbl, $data);
	}

	public function insert(array $data)
	{
		//add columns into comma separated string
		$columns = implode(',', array_keys($data));
	
		//get values
		$values = array_values($data);
		if(!is_array($values)) { $values = []; } 
	
		$placeholders = array_map(function ($val) {
			return '?';
		}, array_keys($data));
	
		//convert array into comma separated string
		$placeholders = implode(',', array_values($placeholders));
		
		$stmt = $this->pdo->prepare("INSERT INTO `".$this->table."` ($columns) VALUES ($placeholders)");
		$stmt->execute($values);
		return $this->pdo->lastInsertId();
	}
	
	public function find($id, $fetch_mode = NULL)
	{
		if(is_null($fetch_mode)) { $fetch_mode=PDO::FETCH_OBJ; }
		return $this->run("SELECT * FROM `".$this->table."` WHERE id = ?", [$id])->fetch($fetch_mode);
	}
	
	public function find_by_column($col, $val, $fetch_mode = NULL)
	{
		if(is_null($fetch_mode)) { $fetch_mode=PDO::FETCH_OBJ; }
		return $this->run("SELECT * FROM `".$this->table."` WHERE `".$col."` = ?", [$val])->fetch($fetch_mode);
	}

	public function run($sql, $args = [])
	{
		if (empty($args)) {
			return $this->pdo->query($sql);
		}
	
		$stmt = $this->pdo->prepare($sql);
		$stmt->execute($args);
	
		return $stmt;
	}

	private function process(array $data)
	{
		$table = $data['from'] ?? $this->table; 
		$select = $data['select'] ?? "*";
		$sql = "SELECT ".$select." FROM ".$table."";
	
		if(isset($data['joins'])) { $sql .= " ".$data['joins']; }
		if(!empty($data['conditions'])) {
			$sql .= " WHERE ".$data['conditions'][0];
			foreach($data['conditions'] as $k => $c) {
				if(0 == $k) { continue; }
				$conditions[] = $c;
			}
		}
		if($data['group']) {
			$postsql .= " GROUP BY ".$data['group'];
		}
		if($data['having']) {
			$postsql .= " HAVING ".$data['having'];
		}
		if($data['order']) { $postsql .= " ORDER BY ".$data['order']; }
		if($data['limit']) { $postsql .= " LIMIT ".abs(intval($data['limit'])); }
		// uncomment next line for SQL debugger
		// error_log("PdoFish2 logger: ".$sql." ".$postsql. print_r($conditions,1));
		if(!empty($conditions)) {
			$stmt = $this->pdo->prepare($sql." ".$postsql);
			$stmt->execute($conditions);
		} else {
			$stmt = $this->pdo->query($sql." ".$postsql);
		}
		return $stmt;
	}
	
	public function all($data=[], $fetch_mode=NULL)
	{
		if(is_null($fetch_mode)) {
			if(is_null($fetch_mode)) { $fetch_mode=PDO::FETCH_OBJ; }
		}
		$stmt = $this->process($data);
		return $stmt->fetchAll($fetch_mode);
	}
	
	public function first($data, $fetch_mode=NULL)
	{
		$data['limit'] = 1;
		$stmt = $this->process($data);
		if(is_null($fetch_mode)) { $fetch_mode=PDO::FETCH_OBJ; }
		return $stmt->fetch($fetch_mode);
	}

	public function raw($sql)
	{
		$this->pdo->query($sql);
	}
	
	public function find_by_sql($sql, $args=NULL, $fetch_mode=NULL)
	{
		if(is_null($fetch_mode)) { $fetch_mode=PDO::FETCH_OBJ; }
		$stmt = $this->run($sql,$args);
		return $stmt->fetch($fetch_mode);
	}
	
	public function find_all_by_sql($sql, $args=NULL, $fetch_mode=NULL)
	{
		if(is_null($fetch_mode)) { $fetch_mode=PDO::FETCH_OBJ; }
		$stmt = $this->run($sql,$args);
		return $stmt->fetchAll($fetch_mode);
	}
	
	/**
	 * dynamic callable
	 *
	 * @param  string $table table name
	 * must be called via PdoFish2 class
	 */
	
	protected function __call( string $name , array $args )
	{
		# one record
		if (preg_match('/^find_by_(.+)/', $name, $matches)) {
			$var_name = $matches[1];
			$sql = "SELECT * FROM `".$this->table()."` WHERE ".$var_name."=?";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([ $args[0] ]);
			return $stmt->fetch($fetch_mode);
		}
		# multiple records
		if (preg_match('/^find_all_by_(.+)/', $name, $matches)) {
			$var_name = $matches[1];
			$sql = "SELECT * FROM `".$this->table."` WHERE ".$var_name."=?";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([ $args[0] ]);
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}
	}

}