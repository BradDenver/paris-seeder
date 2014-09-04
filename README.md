paris-seeder
============

A simple database seeding class using Faker data and Paris models


Example Syntax
```php
Seeder::seed('Seeds\Roles', 2, null, function($record) {
  Seeder::seed('Seeds\Users', 2, array('role_id'=>$record->id));
});
```
The above example would create two roles and two users with each role.

## Install and config
The easiest way to install paris-seeder and its dependencies ([faker](https://packagist.org/packages/fzaninotto/faker), [idiorm](https://packagist.org/packages/j4mie/idiorm), [paris](https://packagist.org/packages/j4mie/paris)) is via [Composer](https://getcomposer.org/). This class is available through [Packagist](https://packagist.org/packages/brad-denver/paris-seeder) with the vendor and package identifier of `brad-denver/paris-seeder`.

Paris-seeder does not require any configuration itself but the following steps show how to configure Idiorm and setup Paris models for its use.

An example Idiorm config
```php
ORM::configure('mysql:host=localhost;dbname=my_database');
ORM::configure('username', 'database_user');
ORM::configure('password', 'top_secret');
```

## Examples
Lets assume we are going to seed a `roles` table

id | title
----- | -----
1 | Boss
2 | Worker

and a `users` table

id | name | role_id
--- | --- | ---
1 | Sally Hard | 2
2 | Bob Lazy | 1

both with auto incrementing id fields.

### Paris Models
fist we need to create classes for each table that extends the Paris Model class
```php
namespace Seeds;

class Roles extends \Model {

  /*
  * use the Paris filter pattern to create a new fake record
  */
  public function create_fake($orm, $faker) {
    $orm->create(array(
      'title' => $faker->word
    ));
    return $orm;
  }
}

class Users extends \Model {

  /*
  * use the Paris filter pattern to create a new fake record
  */
  public function create_fake($orm, $faker) {
    $orm->create(array(
      'name' => $faker->name,
      'role_id' => $faker->randomDigit
    ));
    return $orm;
  }
}

```
The key thing here is that the models have a `create_fake` method that accepts an Idiorm `ORM` instance and `Faker\Generator` instance and returns the record resulting from `$orm->create`.

### Seeder::seed
The seed method expects:
* a paris model instance (or the string/s to create one)
* the count of records to insert (defaults to 1)
* optional data to overide that provided by faker in `create_fake`
* optional callback to be called for record that is inserted (it will be passed the new rocord and the faker instance)
* optional `Faker/Generator` instance to generate fake data (if omitted a new instance will be created)
A basic example.
```php
Seeder::seed('Seeds\Users', 5);
```
Overide faker data.
```php
Seeder::seed('Seeds\Users', 5, array('role_id'=>2));
```
Suppling a callback.
```php
Seeder::seed('Seeds\Roles', 2, null, function($record) {
  Seeder::seed('Seeds\Users', 5, array('role_id'=>$record->id));
});
```
Suppling a faker generator
```php
$faker = Faker\Factory::create('fr_FR'); // create a French faker
Seeder::seed('Seeds\Users', 5, null, null, $faker);
```

### Seeder::replicate
Sometimes there may be no need to use fake data for a certain table. `Seeder::replicate` is helper method to copy all data from one table to another (assuming they have compatible schemas).
```php
// a paris model pointing to our production roles table
$source_model = Model::factory('Roles', 'remote');
// a paris model pointing to our dev roles table that needs to mirror production
$target_model = Model::factory('Roles', 'local');
Seeder::replicate($source_model, $target_model);
```

### Seeder::delete_all
as its name suggests this method simply deletes all records for given models table. It is called as the first step of `Seeder::replicate` and `Seeder::delete_all_and_seed`
```php
Seeder::delete_all('Seeds\Users');
```

### Seeder::delete_all_and_seed
a helper method for the common use case of deleting and reseeding all data in a table. This method simply calls `Seeder::delete_all` followed by `Seeder:seed` for the given model. It accepts the same arguments as `Seeder:seed`
```php
Seeder::delete_all_and_seed('Seeds\Users', 5);
```
