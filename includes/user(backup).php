<?php
require_once('database.php');

class User {

	public $id;
	public $username;
	public $password;
	public $first_name;
	public $last_name;


	public static function find_all() {
		global $database;
		return self::find_by_sql("SELECT * FROM users");
	}


	public static function find_by_id($id=0) {
		global $database;
		$result_array = self::find_by_sql("SELECT * FROM users WHERE id={$id}");
		$found = $database->fetch_array($result_array);
		return $found;
	}

	public static function find_by_sql($sql="") {
		global $database;
		$result_set = $database->query($sql);
		$object_array = array();
		while ($row = $database->fetch_array($result_set)) {
			$object_array[] = self::instantiate($row);
		}
		return $object_array;
	}

	public function full_name() {
		if (isset($this->first_name) && isset($this->last_name)) {
			return $this->first_name . " " . $this->last_name;
		} else {
			return "" ;
		}
	}

	private static function instantiate ($record) {
		$object = new self;
		/*$object->id         = $record['id'];
        $object->username   = $record['username'];
        $object->first_name = $record['first_name'];
        $object->last_name  = $record['last_name'];
*/
        foreach ($record as $attribute => $value) {
        	if ($object->has_attribute($attribute)) {
        		$object->attribute = $value;
        	}

          }
           return $object;

        }

    private function has_attribute($attribute) {
    	// get_object_vars() возвращает ассоциативный массив
    	// где ключи - атрибуты класса , а значения - текущее значения класса
    	$object_vars = get_object_vars($this);

        // array_key_exists() возвращает true или false
    	return array_key_exists($attribute, $object_vars);
    }    



       
	
}

?>