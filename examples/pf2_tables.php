<?php 

class pf2Tables extends PdoFish2 { 
	
	public function table1() { 
		return $this->set_table('table1');  
	}
	
	public function table2() { 
		return $this->set_table('table2');  
	}
	
}