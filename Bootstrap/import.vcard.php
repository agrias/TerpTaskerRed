<?php

class ImportVCards  {

   protected $ab = array();

   public static function checkType($entry, $type) {
   	
     if(array_key_exists('TYPE', $entry) && array_key_exists('SUBTYPE', $entry)) {
       $all_types = array_merge(array_keys($entry), $entry['TYPE'], $entry['SUBTYPE']);
     } elseif(array_key_exists('TYPE', $entry)) {
       $all_types = array_merge(array_keys($entry), $entry['TYPE']);
     } else {
     	$all_types = array_keys($entry);
     }
     
     return in_array($type, $all_types);
     
   }

   public static function getKeyValue($vcard_line) {
   	
   }
  
  function parseLine($vcards_line) {
  	
    $res = array();
    
    // Apple Addressbook preprocessing
	  // - Remove "itemX." prefixes
	  $vcards_line = preg_replace('/^item\d+\./', '', $vcards_line);
	  
	  // Basic split
    $kv = explode(':', $vcards_line, 2);
 	  $key = strtoupper($kv[0]); 
    $res['key'] = $key;

    if(count($kv) == 2) {
    	
    	$val = $kv[1];
    
    	//
    	// Key Analyzer
    	//
    	$subkeys = explode(';', $key);
    	$pkey    = $subkeys[0];
    	
    	$addr_line = array();
    	$addr_line['SRC'] = $vcards_line;
    	
    	for($i = 1; $i < count($subkeys); $i++) {
    	  $subkey = explode('=', $subkeys[$i]);
    	  $subkey_name = $subkey[0];
    	  if(!isset($subkey[1])) {
    	    $addr_line[$subkey_name] = "";
    	  } else {
    	    $addr_line[$subkey_name][] = $subkey[1];
    	    
    	    if($subkey_name == 'TYPE' && count(explode(',',$subkey[1])) > 1) {
    	      foreach(explode(',',$subkey[1]) as $subtype) {
    	    	  $addr_line['SUBTYPE'][] = $subtype;
    	    	}
    	    }
    	  }
    	}
    	
    	//
    	// Value Analyzer: Replace escape values
    	//
      if(   isset($addr_line["ENCODING"]) 
         && count($addr_line["ENCODING"]) == 1
         && $addr_line["ENCODING"][0] == "QUOTED-PRINTABLE") {
        $val = utf8_encode(quoted_printable_decode($val));
      }
      $val = str_replace("=0D", "\r", $val);
      $val = str_replace("=0A", "\n", $val);
      $val = str_replace("\\r", "\r", $val);
      $val = str_replace("\\n", "\n", $val);      
      
    	$addr_line['VALUE'] = $val;
    	if(count(explode(';', $val)) > 1) {
    	  $addr_line['SEMI-COLON'] = explode(';', $val);  	  
    	}
    
    	$res['pkey']  = $pkey;
    	$res['value'] = $addr_line;    	
    }
  	return $res;
  }

    
  function __construct($file_lines) {

//
// Concat multi-line records (e.g.: photos)
//
$concated_lines = array();
foreach($file_lines as $file_line) {
  $file_line = str_replace("\n", "", $file_line);
  $file_line = str_replace("\r", "", $file_line);
	if(preg_match('/^  /', $file_line)) {
		$concated_lines[count($concated_lines)-1] .= preg_replace('/^  /','', $file_line);
	} else {
		$concated_lines[] = $file_line;
	}
}
$file_lines = $concated_lines;

//
// Split every line to semi-structured records
//
$addresses = array();
foreach($file_lines as $vcards_line) {
  
  // Parse and add a field to the addess.
  $res = self::parseLine($vcards_line);

  if(isset($res['pkey'])) {
    $address[$res['pkey']][] = $res['value'];  
  }
  
  // Init the new address
  if($res['key'] == "BEGIN") {
    $address = array();  	
  }
    
  // Add the  address to the list
  if($res['key'] == "END") {
    $addresses[] = $address;
  }
}

foreach($addresses as $address) {

  $dest_addr = array();

  foreach($address as $type => $entries) {  	
    
    //
    // "N" Type, X.520 based, delimiter ";" (5 fields)
    //
    // Family Name;Given Name;Additional Names;Honorific Prefixes;Honorific Suffixes
    //
    if($type == "N") {
      $dest_addr['lastname']  = $entries[0]['SEMI-COLON'][0];
      $dest_addr['firstname'] = $entries[0]['SEMI-COLON'][1];
    }
    
    
    //
    // "EMAIL" e-Mail addresses
    //
    if($type == "EMAIL") {
    
      $dest_addr['email']  = $entries[0]['VALUE'];
      if(isset($entries[1]['VALUE'])) {
        $dest_addr['email2'] = $entries[1]['VALUE'];
      }
      if(isset($entries[2]['VALUE'])) {
        $dest_addr['email3'] = $entries[2]['VALUE'];
      }
    }
    

    //
    // "TEL" Type, X.500 Telephone Number attribute
    //
    if($type == "TEL") {
    
    	foreach($entries as $entry) {
    		                
        // Mapping:
        // * Paste value in correct field.
        	        if(self::checkType($entry, 'HOME')) { $dest_addr['home']   = $entry['VALUE'];
        	  } elseif(self::checkType($entry, 'FAX'))  { $dest_addr['fax']    = $entry['VALUE'];  	  	
        	  } elseif(self::checkType($entry, 'WORK')) { $dest_addr['work']   = $entry['VALUE'];
        	  } elseif(self::checkType($entry, 'CELL')) { $dest_addr['mobile'] = $entry['VALUE'];  	  	
    	  } else {                        	    $dest_addr['phone2'] = $entry['VALUE'];  	  	
    	  }
    	  	
    	}
    }
    
    }
    

  }
      $this->ab[] = $dest_addr;
    }
  }
  
  function getResult() {
  	return $this->ab;
  }
}
?>