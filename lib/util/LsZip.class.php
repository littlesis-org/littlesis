<?php


class Zip{
  
  static function unzip($filename){
    $created_files = array();
    $zip = zip_open($filename);
    if ($zip) {
      while ($zip_entry = zip_read($zip)) {
        $created_file = zip_entry_name($zip_entry);
        $created_files[] = $created_file;
        $fp = fopen($created_file, "w");
        if (zip_entry_open($zip, $zip_entry, "r")) {
          $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
          fwrite($fp,"$buf");
          zip_entry_close($zip_entry);
          fclose($fp);
        }
      }
      zip_close($zip);
    }
    return $created_files;   
  }

}
