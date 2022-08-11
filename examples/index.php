<?php 

require '../src/PdoFish2.php'; 
require 'pf2_tables.php'; 

// here's a raw PdoFish2 object
$pf2 = new PdoFish2(); 

// here's an instance of the extended class
$tables = new pf2Tables(); 

// this will print the record from the "table1" table with an ID of 1 
// it uses the pf2Tables class to target tables for brevity 
$a = $tables->table1()->find(1);
print_r($a); 

// this will print the record from the "table1" table where the "name" field is "Adam"  
$b = $tables->table1()->first([ 'conditions'=>['name=?',"Adam"] ]);
print_r($b); 

// this will do the same thing
$c = $tables->table1()->find_by_column("name","Adam");
print_r($c);

// you can build complex queries 
$d = $pf2->all([
	'select'=>'t1.field1, t2.field2, t3.field3',
	'from' => 'table1 t1',
	'joins' => 'LEFT JOIN table2 t2 ON t1.col=t2.col LEFT JOIN table3 t3 ON t1.colx=t3.colx',
	'conditions' => ['t1.coly=? AND t2.colz',$val1,$val2],
	'limit'=>3,
	'order'=>'t3.field3 DESC'
]); 

// see more in the README file


