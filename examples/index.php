<?php

require '../src/PdoFish2.php';
require 'pf2_tables.php';

// Load schema.sql into your database first, then set credentials here.
$creds = [
    'host'     => 'localhost',
    'database' => 'testdb',
    'username' => 'dbuser',
    'password' => 'secret',
];

$pf2    = new PdoFish2($creds);
$tables = new pf2Tables($creds);

// -----------------------------------------------------------------------
// FIND by primary key
// -----------------------------------------------------------------------

$user = $tables->users()->find(1);
echo $user->name . "\n"; // Alice

// -----------------------------------------------------------------------
// FIRST — single row matching a condition
// -----------------------------------------------------------------------

$bob = $tables->users()->first(['conditions' => ['name=?', 'Bob']]);
echo $bob->email . "\n"; // bob@example.com

// -----------------------------------------------------------------------
// Dynamic find_by_* — equivalent to find_by_column()
// -----------------------------------------------------------------------

$alice = $tables->users()->find_by_email('alice@example.com');
echo $alice->name . "\n"; // Alice

// -----------------------------------------------------------------------
// ALL — multiple rows, optional conditions / order / limit
// -----------------------------------------------------------------------

$published = $tables->posts()->all([
    'conditions' => ['status=?', 'published'],
    'order'      => 'created_at DESC',
]);
foreach ($published as $post) {
    echo $post->title . "\n";
}

// -----------------------------------------------------------------------
// ALL with a JOIN across tables
// -----------------------------------------------------------------------

$feed = $pf2->all([
    'select'     => 'p.title, p.status, u.name AS author',
    'from'       => 'posts p',
    'joins'      => 'LEFT JOIN users u ON p.user_id = u.id',
    'conditions' => ['p.status=?', 'published'],
    'order'      => 'p.created_at DESC',
    'limit'      => 10,
]);
foreach ($feed as $row) {
    echo $row->author . ': ' . $row->title . "\n";
}

// -----------------------------------------------------------------------
// COUNT
// -----------------------------------------------------------------------

$total = $tables->posts()->count(['conditions' => ['status=?', 'published']]);
echo "Published posts: $total\n";

// -----------------------------------------------------------------------
// INSERT — returns the new row's ID
// -----------------------------------------------------------------------

$newId = $tables->posts()->insert([
    'user_id' => 1,
    'title'   => 'A New Post',
    'body'    => 'Hello again.',
    'status'  => 'draft',
]);
echo "Inserted post ID: $newId\n";

// -----------------------------------------------------------------------
// UPDATE
// -----------------------------------------------------------------------

$tables->posts()->update(['status' => 'published'], ['id' => $newId]);

// -----------------------------------------------------------------------
// DELETE by ID
// -----------------------------------------------------------------------

$tables->posts()->delete_by_id($newId);

// -----------------------------------------------------------------------
// DELETE MANY — remove all posts for a list of user IDs
// -----------------------------------------------------------------------

// $tables->posts()->delete_many('user_id', [1, 2]);
