<?php

/*
 * This class extends PdoFish2.
 * Table-chaining methods like these enable concise, readable scripting
 * without repeating set_table() calls throughout your application.
 * See index.php for usage examples.
 */

class pf2Tables extends PdoFish2 {

    public function users() {
        return $this->set_table('users');
    }

    public function posts() {
        return $this->set_table('posts');
    }

}
