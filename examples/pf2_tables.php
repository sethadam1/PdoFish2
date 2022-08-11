<?php 

/*
 * This class extends PdoFish2 
 * These table chaining functions enable powerful, concise scripting as seen in /examples/index.php
 */

class pf2Tables extends PdoFish2 { 
	
	public function table1() { 
		return $this->set_table('table1');  
	}
	
	public function table2() { 
		return $this->set_table('table2');  
	}
	
}