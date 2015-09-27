<?php
require_once(LIB_PATH.DS.'database.php');
Class Photograph extends DatabaseObject {

  protected static $table_name = "photographs";
  protected static $db_fields = array('id', 'filename', 'type',
  'size', 'caption');
  public $id;
  public $filename;
  public $type;
  public $size;
  public $caption;

  private $temp_path;
  protected $upload_dir = "images";
  public $errors = array();

  protected $upload_errors = array(
    UPLOAD_ERR_OK          => "No errors.",
    UPLOAD_ERR_INI_SIZE    => "Larger than upload_max_filesize.",
    UPLOAD_ERR_FORM_SIZE   => "Larger than from MAX_FILE_SIZE.",
    UPLOAD_ERR_PARTIAL     => "Partial upload.",
    UPLOAD_ERR_NO_FILE     => "No file.",
    UPLOAD_ERR_NO_TMP_DIR  => "No temporary directory.",
    UPLOAD_ERR_CANT_WRITE  => "Can't write to disk.",
    UPLOAD_ERR_EXTENSION   => "File upload stopped by extension."
  );
  // Pass in $_FILE['uploaded_file'] as an argument
  public function attach_file($file) {
    // Perform error checking on the form parameters
    if(!$file || empty($file) || !is_array($file)) {
      $this->errors[] = "No file was uploaded. ";
      return false;
    } elseif ($file['error'] != 0 ) {
      $this->errors[] = $this->upload_errors[$file['error']];
    } else {
      // Set object attributes to the form parameters
      $this->temp_path   = $file['tmp_name'];
      $this->filename    = basename($file['name']);
      $this->type        = $file['type'];
      $this->size        = $file['size'];

      return true;
    }

  }

  public function save() {
    if (isset($this->id)) {
      $this->update();
    } else {
      if(!empty($this->errors)) { return false; }
      if(strlen($this->caption) > 255) {
        $this->errors[] = "The caption can only be
         255 characters long.";
        return false;
      }
      // Can't save without filename and temp location
      if(empty($this->filename) || empty($this->temp_path)) {
        $this->errors[] = "The file location is not available.";
        return false;
      }

      // Determine the target_path
      $target_path = SITE_ROOT .DS. 'public' .DS.
      $this->upload_dir .DS. $this->filename;

    // Make sure a file doesn't already exist in the target location
      if(file_exists($target_path)) {
        $this->errors[] = "The file {$this->filename} already exist";
        return false;
      }
      if(move_uploaded_file($this->temp_path, $target_path)) {
        if($this->create()) {
          unset($this->temp_path);
          return true;
        }

      } else {
        $this->errors[] = "The file upload failed,possibly due to
        incorrect permissions on the upload folder.";
        return false;
      }
    }
  }

  public static function find_all() {
		return self::find_by_sql("SELECT * FROM ".self::$table_name);
  }

  public static function find_by_id($id=0) {
    $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");
		return !empty($result_array) ? array_shift($result_array) : false;
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

	private static function instantiate($record) {
		// Could check that $record exists and is an array
    $object = new self;
		// Simple, long-form approach:
		// $object->id 				= $record['id'];
		// $object->username 	= $record['username'];
		// $object->password 	= $record['password'];
		// $object->first_name = $record['first_name'];
		// $object->last_name 	= $record['last_name'];

		// More dynamic, short-form approach:
		foreach($record as $attribute=>$value){
		  if($object->has_attribute($attribute)) {
		    $object->$attribute = $value;
		  }
		}
		return $object;
	}

	private function has_attribute($attribute) {
	  // We don't care about the value, we just want to know if the key exists
	  // Will return true or false
	  return array_key_exists($attribute, $this->attributes());
	}

    protected function attributes () {
      // method return an array  of  attribute keys and their values
      $attributes = array();
      foreach(self::$db_fields as $field) {
        if(property_exists($this, $field)) {
          $attributes[$field] =$this->$field;
        }
      }
      return $attributes;
    }

    protected function sanitized_attributes() {
      global $database;
      $clean_attributes = array();
      foreach($this->attributes() as $key => $value ) {
        $clean_attributes[$key] = $database->escape_value($value);
}
      return $clean_attributes;

}

    /*public function   save() {
      return isset($this->id) ? $this->update() : $this->create();
    }*/

    public function create() {
      global $database;
      $attributes = $this->sanitized_attributes();
      $sql  = "INSERT INTO ".self::$table_name ." (";
      $sql .= join(", ", array_keys($attributes));
      $sql .= ") VALUES ('";
      $sql .= join("', '", array_values($attributes));
      $sql .= "')";
      if($database->query($sql)) {
        $this->id = $database->insert_id();
        return true;
      } else {
        return false;
      }

    }

    public function update() {
      global $database;
      $attributes = $this->sanitized_attributes();
      $attribute_pairs = array();
      foreach($attributes as $key => $value) {
        $attribute_pairs[] = "{$key}= '{$value}'";
      }
      $sql  = "UPDATE ".self::$table_name ." SET ";
      $sql .= join(", ", $attribute_pairs);
      $sql .= " WHERE ID=" . $database->escape_value($this->id) ;
      $database->query($sql);
      return ($database->affected_rows() == 1)? true : false;

    }

    public function delete() {
      global $database;
      $sql  = "DELETE FROM ".self::$table_name ;
      $sql .= " WHERE id=" . $database->escape_value($this->id);
      $sql .= " LIMIT 1";
      $database->query($sql);
      return ($database->affected_rows() == 1)? true : false;

    }
}

?>