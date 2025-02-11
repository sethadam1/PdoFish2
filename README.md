# PdoFish2
A wrapper for PHP and PDO.

## Purpose
PdoFish2 attempts to create a flexible, lightweight layer on top of PDO. Its syntax was inspired by Ruby's Active Record. This is _not_ a PHP implementation of Active Record, but rather, a class that attempts to mimic the best part while reducing the amount of code needed to use PDO.   

This project is not for everyone. Sloppy programming can break these functions. However, if you're already familiar with Active Record or wish to use an Active Record style DB interface in PHP, this may suit. The aim of this project was simple: readable code that is as thin a layer on top of PDO as possible. 

## Currently Supported Methods
```$object->set_table(tbl_name)``` - a chainable function to specify a table    
```$object->t()``` - a shortcut for ```set_table```  
```$object->raw()``` - execute raw SQL  
```$object->find()``` - find by a column called "id"  
```$object->all()``` - return all rows matching a query   
```$object->first()``` - returns the first row matching a query  
```$object->find_by_sql()``` - returns a single row matching a query  
```$object->find_all_by_sql()``` - returns all rows matching a query  
```$object->lastInsertId()``` - returns last insert id  
```$object->count()``` - return matching row count  
```$object->insert()``` - insert record  
```$object->insert()``` - alias of insert()  
```$object->update()``` - update field(s)  
```$object->delete()```  - delete a row  
```$object->delete_by_id()``` - delete by a column called "id"   
```$object->deleteMany()``` - delete multiple rows matching criteria (using the IN keyword)   

#### Dynamic function names
```$object->find_by_[field]``` - find a single row by a specific column value  
```$object->find_all_by_[field]``` - find multiple rows where one column matches the given value  

Installation
------------
Composer install coming soon. 

Manually: 
- Upload the files to your web server.  
- Include PdoFish2.php in your code. 
- Instantiate the class as seen below. 

```php  
require_once '/path/to/PdoFish2/PdoFish2.php';  
$pf2 = new PdoFish2(); 
```

## Basic CRUD
##### For the purposes of these examples, we'll assume you'll assign PdoFish2 to the variable $pf2. 

#### Create 
```php  
$data = [
	'id' => 3,  
	'col1' => '2020-08-27 09:58:01',  
	'col2'=> 'a string',  
	'col3' => 12345  
];  
$y = $pf2->set_table('products')->insert($data);  
  
echo $y;   
// example response "3"  
```

#### Read

```php  
//print an object
$x = $pf2->t('users')->first([ 'conditions'=>['some_field=?', 'some_value'] ]);
print_r($x); 
```  

```php  
//print an associative array 
$x = $pf2->first(['from'=>'table_name', 'conditions'=>['some_field=?', 'some_value']], PDO::FETCH_ASSOC);
print_r($x); 
```  
```php  
// print a single row matching SQL query  
$x = $pf2->find_by_sql('select * from random_table where random_field=12');
print_r($x);
```  

```php  
// print a row where id = 5   
$x = $object->t('table')->find(5);
print_r($x); 
```

```php  
// print 5 rows of data from this query   
$x = $object->all([
	'select'=>'field1, field2, field3',
	'from'=>'table t',
	'joins'=>'LEFT JOIN table2 t2 ON t.field1=t2.other_field',
	'conditions' => ['some_field=?', $some_value],
	'order'=>'field3 ASC',
	'limit'=>5
]);
print_r($x);
```

#### Update  
```php    
// updates column "firstname" to "Boris" where id = 5
$object->t('table_name')->update(['firstname'=>'Boris'], ['id'=>5]); 

// updates columns "firstname" to "June", "lastname" to "Basoon" where id = 5
$object->t('table_name')->update(['firstname'=>'June', 'lastname'=>'Basoon'], ['id'=>5]); 
```   

Now consider a table with three columns, "row_id", "columnA", and "columnB."   
```php   
// this will NOT work  
$y = $object->t('table_name')->find(3); //find a model with primary key=3  
```  
  
#### Delete  
```php    
// delete rows where column "firstname" is equal to "Boris"  
$object->t('table_name')->delete(['firstname'=>'Boris']);   
  
// delete row where column "id" is equal to "5"  
$object->t('table_name')->delete_by_id(5);   
  
// delete rows where column "user_id" is equal to 1, 2, or 3  
$object->t('table_name')->deleteMany(['user_id', '1,2,3']);   
```
   
## Arguments supported
The following arguments are supported in the PdoFish queries:  
```select``` - columns to select  
```from``` - table, or table and an alias _e.g. "prices p"_  
```joins``` - a string of joins in SQL syntax, _e.g. LEFT JOIN table2 on prices.field=table2.field_   
```conditions``` - an array of SQL, using ? placeholders, and arguments to be bound _e.g. ['year=? AND mood=?',2021,'happy']_   
```group``` - group by, using a field name  
```having``` - having, _e.g. 'count(x)>3'_  
```order``` - order by, _e.g. 'id DESC'  
```limit``` - a positive integer greater than 0  

## Credits
Some of this code has roots in the [David Carr](https://twitter.com/dcblogdev)'s [PDOWrapper](https://dcblog.dev/docs/pdo-wrapper) project. 
