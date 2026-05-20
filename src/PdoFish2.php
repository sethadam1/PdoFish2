<?php

#[AllowDynamicProperties]
class PdoFish2 { 
	
	var $pdo;
	var $table = null;
	var $creds = array(); 

	function __construct($creds=[]) 
	{ 
		$ty = $creds['type'] ?? 'mysql'; 
		$h 	= $creds['host'] ?? $_ENV['DB_HOST']; 
		$db = $creds['database'] ?? $_ENV['DB_NAME']; 
		$ch = $creds['charset'] ?? 'utf8mb4'; 
		$u	= $creds['username'] ?? $_ENV['DB_USER']; 
		$pw = $creds['password'] ?? $_ENV['DB_PASSWORD']; 
		$pt = $creds['port'] ?? "3306"; 
		$this->pdo = new PDO($ty.":host=".$h.";port=".$pt.";dbname=".$db.";charset=".$ch, $u, $pw);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// Set collation to utf8mb4_general_ci to match database default
		$this->pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_general_ci");
	}
	
	public function delete_many(string $column, $vals)
	{
		$arr = is_array($vals) ? $vals : explode(',', $vals);
		$placeholders = implode(',', array_fill(0, count($arr), '?'));
		$stmt = $this->run("DELETE FROM `".$this->table."` WHERE `$column` IN ($placeholders)", $arr);
		return $stmt->rowCount();
	}
	
	public function delete_by_id($val)
	{
		$stmt = $this->run("DELETE FROM `".$this->table."` WHERE id = ?", [$val]);
		return $stmt->rowCount();
	}
	
	public function delete_by_column($col, $val)
	{
		$stmt = $this->run("DELETE FROM `".$this->table."` WHERE `".$col."` = ?", [$val]);
		return $stmt->rowCount();
	}
	
	public function set_table($tbl) {
		$this->table = $tbl;
		return $this;
	}

	public function t($tbl) {
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
	
		$limitClause = '';
		if (is_numeric($limit)) {
			$limitClause = ' LIMIT ' . abs((int)$limit);
		}
		$stmt = $this->run("DELETE FROM `".$this->table."` WHERE $whereDetails$limitClause", $values);
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
	
		//convert array into comma seperated string
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

	public function find_by_slug($val, $fetch_mode = NULL)
	{
		return $this->find_by_column('slug',$val,$fetch_mode); 
	}
	
	public function find_by_ref($val, $fetch_mode = NULL)
	{
		return $this->find_by_column('ref',$val,$fetch_mode);
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
		$postsql = null; 
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
		} elseif(isset($data['in']) && is_array($data['in'])) {
			$inVals = is_array($data['in'][1]) ? $data['in'][1] : explode(',', $data['in'][1]);
			$placeholders = implode(',', array_fill(0, count($inVals), '?'));
			$sql .= " WHERE ".$data['in'][0]." IN ($placeholders) ";
			$conditions = $inVals;
		}
		if($data['group']) {
			$postsql .= " GROUP BY ".$data['group'];
		}
		if($data['having']) {
			$postsql .= " HAVING ".$data['having'];
		}
		if($data['order']) {
			if (preg_match('/^[a-zA-Z0-9_\.`,\s]+(\s+(ASC|DESC))?$/i', $data['order'])) {
				$postsql .= " ORDER BY ".$data['order'];
			}
		}
		if($data['offset']) { $offset = abs(intval($data['offset'])); } else { $offset = 0; }
		if($data['limit']) { $limit = abs(intval($data['limit'])); } else { $limit = 4000; }
		$postsql .= " LIMIT ".$offset.", ".$limit;
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
	
	public function conditions($conditions) {
		$data['conditions'] = $conditions; 
		return $this->all($data); 
	}
	
	public function first($data, $fetch_mode=NULL)
	{
		$data['limit'] = 1;
		$stmt = $this->process($data);
		return $this->return_data($stmt,$fetch_mode);
	}

	public function raw($sql)
	{
		$this->pdo->query($sql);
	}
	
	public function return_data($stmt, $fetch_mode=NULL)
	{
		if(is_null($fetch_mode)) { $fetch_mode=PDO::FETCH_OBJ; }
		return $stmt->fetch($fetch_mode);
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
	
	public function __call( string $name , array $args )
	{
		# one record
		if (preg_match('/^find_by_(.+)/', $name, $matches)) {
			$var_name = $matches[1];
			$sql = "SELECT * FROM `".$this->table."` WHERE ".$var_name."=?";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([ $args[0] ]);
			return $stmt->fetch(PDO::FETCH_OBJ);
		}
		# multiple records
		if (preg_match('/^find_all_by_(.+)/', $name, $matches)) {
			$var_name = $matches[1];
			$sql = "SELECT * FROM `".$this->table."` WHERE ".$var_name."=?";
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute([ $args[0] ]);
			return $stmt->fetchAll(PDO::FETCH_OBJ);
		}
		if (preg_match('/^view_(.+)/', $name, $matches)) {
			$var_name = $matches[1];
			return $this->set_table("v_".$var_name);
		}
	}

}