<?php

class Seeder {
 
  /*
   * @param mixed $model Paris model or strings to create model
   * @return ORMWrapper
   */
  public static function delete_all($model) {
    $model = self::_model_instance($model);
    $model->delete_many();
    
    return $model;
  }

  /*
   * @param mixed $model Paris model or strings to create model
   * @param null|int $count Number of records to create
   * @param null|Array $data Array of column=>value pairs
   * @param null|function $callback Function to call after each record is created
   * @param null|Faker\Generator The instance that will generate fake data
   * @return ORMWrapper
   */
  public static function delete_all_and_seed($model, $count=1, $data=null, $callback=null, $faker=null) {
    $model = self::_model_instance($model);

    self::delete_all($model);
    self::seed($model, $count, $data, $callback, $faker);

    return $model;
  }

  
  /*
   * empties $target_model then copies all records from $source_model 
   * to $target_model
   *
   * @param mixed $source_model
   * @param mixed $target_model
   * @return ORMWrapper $target_model
   */
  public static function replicate($source_model, $target_model) {

    $source_model = self::_model_instance($source_model);
    $target_model = self::_model_instance($target_model);
    
    self::delete_all($target_model);
    
    // copy all from source table
    $source_records = $source_model->find_many();
    foreach($source_records as $source_record) {
      $new_record = $target_model->create();
      $new_record->set($source_record->as_array());
      $new_record->save();
    }

    return $target_model;
  }

  /*
   * inserts records based on the supplied models 'create_fake' filter
   * see _model_instance() for the accepted $model values
   * $data will override fake values supplied by the model
   * $callback is called after each record is created and is passed
   * the new record and the faker instance
   *
   * @param mixed $model Paris model or strings to create model
   * @param null|int $count Number of records to create
   * @param null|Array $data Array of column=>value pairs
   * @param null|function $callback Function to call after each record is created
   * @param null|Faker\Generator The instance that will generate fake data
   * @return ORMWrapper
   */
  public static function seed($model, $count=1, $data=null, $callback=null, $faker=null) {
    
    $model = self::_model_instance($model);
    if(!$callback) {
      $callback = self::_noop();
    }
    $faker = self::_faker_instance($faker);

    for($i=0; $i<$count; $i++) {
      $record = $model->filter('create_fake', $faker);
      if($data) {
        $record->set($data);
      }
      $record->save();

      call_user_func($callback, $record, $faker);
    }

    return $model;
  }

  private static function _noop() {
    // does nothing
  }

  /*
   * create a faker instance if none was passed in  
   */
  private static function _faker_instance($faker) {
    return ($faker) ? $faker : Faker\Factory::create();
  }

  /*
   * $model needs to be/become an instance of the paris ORMWrapper class
   * Accepted values are:
   *  ORMWrapper instance
   *  [$model_class_name, $idorm_connection_name]
   *  string $model_class_name
   * 
   * @param mixed $model Paris model or strings to create model
   * @return ORMWrapper 
   */
  private static function _model_instance($model) {

    if($model instanceof ORMWrapper){
      return $model;
    } else if(is_array($model)) {
      return Model::factory($model[0], $model[1]);
    } else if(is_string($model)) {
      return Model::factory($model);
    }

    throw new SeederInvalidModelException("Could not create ORMWrapper");
  }

}

class SeederInvalidModelException extends Exception {}

