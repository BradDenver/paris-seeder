<?php

class Seeder {

  public function __construct($model) {
    $this->model = $model;
  }

  // static version of constructor to allow chaining
  // eg. $seeder = Seeder::init($model)->truncate();
  public static function init($model) {
    return new self($model);
  }

  public function replicate($source_model) {
    $this->truncate();
    
    // copy all from source table
    $source_records = $source_model->find_many();
    foreach($source_records as $source_record) {
      $new_record = $this->model->create();
      $new_record->set($source_record->as_array());
      $new_record->save();
    }
  }

  public function seed($faker, $count, $data, $callback=null) {
    if(!$callback) {
      $callback = self::_noop();
    }

    for($i=0; $i<$count; $i++) {
      $record = $this->model->filter('create_fake', $faker);
      if($data) {
        $record->set($data);
      }
      $record->save();

      call_user_func($callback, $record, $faker);
    }
  }

  public function truncate() {
    $this->model->delete_many();
  }

  public function truncate_and_seed($faker, $count, $data, $callback=self::_noop) {
    $this->truncate();
    $this->seed($faker, $count, $data, $callback);
  }

  private static function _noop() {
    // does nothing
  }

}


// $faker = Faker\Factory::create();
// $roles = new RolesModel();
// $roleSeeder = new Seeder($roles);
// $roleSeeder->seed($faker, 10, null, function($faker, $role) {
//   $users = new UsersModel();
//   $userSeeder = new Seeder($users);
//   $userSeeder->seed($faker, 5, array('role_id'=>$role->id));
// });
