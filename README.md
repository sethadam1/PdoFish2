# PdoFish2
A wrapper for PHP and PDO.

## Purpose
PdoFish2 is an Active Record-inspired query interface built on top of PDO. This is _not_ a PHP implementation of Active Record, but rather, a class that mimics its best parts while keeping the amount of code needed to use PDO to a minimum.

This project is not for everyone; however, if you're already familiar with Active Record or wish to use an Active Record style DB interface in PHP, this may suit. The aim of this project was simple: readable code that is as thin a layer on top of PDO as possible.

## Installation

**Via Composer:**
```bash
composer require sethadam1/PdoFish2
```

**Manually:**
- Upload the files to your web server.
- Include `PdoFish2.php` in your code.
- Instantiate the class as seen below.

```php
require_once '/path/to/PdoFish2/src/PdoFish2.php';
$pf2 = new PdoFish2();
```

## Currently Supported Methods
```$object->set_table(tbl_name)``` - a chainable function to specify a table  
```$object->t(tbl_name)``` - a shortcut for ```set_table```  
```$object->raw($sql)``` - execute raw SQL  
```$object->find($id)``` - find by a column called "id"  
```$object->all($args)``` - return all rows matching a query  
```$object->first($args)``` - returns the first row matching a query  
```$object->conditions($conditions)``` - shorthand for ```all()``` with a conditions array  
```$object->find_by_sql($sql)``` - returns a single row matching a query  
```$object->find_all_by_sql($sql)``` - returns all rows matching a query  
```$object->find_by_column($col, $val)``` - find a single row by a specific column value  
```$object->find_by_slug($val)``` - shorthand for ```find_by_column('slug', $val)```  
```$object->find_by_ref($val)``` - shorthand for ```find_by_column('ref', $val)```  
```$object->count($args)``` - return matching row count  
```$object->insert($data)``` - insert a record; returns the last insert ID  
```$object->update($data, $where)``` - update field(s)  
```$object->delete($where)``` - delete rows matching criteria  
```$object->delete_by_id($id)``` - delete by a column called "id"  
```$object->delete_by_column($col, $val)``` - delete by any column  
```$object->delete_many($column, $vals)``` - delete multiple rows matching a list of values (uses the IN keyword)  

#### Dynamic function names
```$object->find_by_[field]($val)``` - find a single row by a specific column value  
```$object->find_all_by_[field]($val)``` - find multiple rows where one column matches the given value  

## Basic CRUD
##### For the purposes of these examples, we'll assume you'll assign PdoFish2 to the variable $pf2.

#### Instantiating
```php
// Using credentials array
$pf2 = new PdoFish2([
    'host'     => 'localhost',
    'database' => 'mydb',
    'username' => 'dbuser',
    'password' => 'secret',
]);

// Or using environment variables (DB_HOST, DB_NAME, DB_USER, DB_PASSWORD)
$pf2 = new PdoFish2();
```

#### Create
```php
$data = [
    'col1' => '2020-08-27 09:58:01',
    'col2' => 'a string',
    'col3' => 12345
];
$id = $pf2->t('products')->insert($data);

echo $id;
// example response "3"
```

#### Read

```php
// print an object
$x = $pf2->t('users')->first([ 'conditions' => ['some_field=?', 'some_value'] ]);
print_r($x);
```

```php
// print an associative array
$x = $pf2->first(['from' => 'table_name', 'conditions' => ['some_field=?', 'some_value']], PDO::FETCH_ASSOC);
print_r($x);
```

```php
// print a single row matching a SQL query
$x = $pf2->find_by_sql('select * from random_table where random_field=12');
print_r($x);
```

```php
// print a row where id = 5
$x = $pf2->t('table')->find(5);
print_r($x);
```

```php
// print 5 rows from a complex query
$x = $pf2->all([
    'select'     => 'field1, field2, field3',
    'from'       => 'table t',
    'joins'      => 'LEFT JOIN table2 t2 ON t.field1=t2.other_field',
    'conditions' => ['some_field=?', $some_value],
    'order'      => 'field3 ASC',
    'limit'      => 5
]);
print_r($x);
```

#### Update
```php
// updates column "firstname" to "Boris" where id = 5
$pf2->t('table_name')->update(['firstname' => 'Boris'], ['id' => 5]);

// updates multiple columns where id = 5
$pf2->t('table_name')->update(['firstname' => 'June', 'lastname' => 'Basoon'], ['id' => 5]);
```

#### Delete
```php
// delete rows where column "firstname" is equal to "Boris"
$pf2->t('table_name')->delete(['firstname' => 'Boris']);

// delete row where column "id" is equal to 5
$pf2->t('table_name')->delete_by_id(5);

// delete rows where column "user_id" is equal to 1, 2, or 3
$pf2->t('table_name')->delete_many('user_id', [1, 2, 3]);
```

## Arguments supported
The following arguments are supported in the PdoFish queries:  
```select``` - columns to select  
```from``` - table, or table and an alias _e.g. "prices p"_  
```joins``` - a string of joins in SQL syntax, _e.g. LEFT JOIN table2 on prices.field=table2.field_  
```conditions``` - an array of SQL with ? placeholders and bound arguments _e.g. ['year=? AND mood=?', 2021, 'happy']_  
```group``` - group by field name  
```having``` - having clause _e.g. 'count(x)>3'_  
```order``` - order by _e.g. 'id DESC'_  
```offset``` - row offset (integer)  
```limit``` - a positive integer greater than 0  

## Credits
This project draws inspiration from [David Carr](https://twitter.com/dcblogdev)'s [PDOWrapper](https://dcblog.dev/docs/pdo-wrapper).
