<?php 
// This file is part of FreePBX.
//
//    FreePBX is free software: you can redistribute it and/or modify
//    it under the terms of the GNU General Public License as published by
//    the Free Software Foundation, either version 2 of the License, or
//    (at your option) any later version.
//
//    FreePBX is distributed in the hope that it will be useful,
//    but WITHOUT ANY WARRANTY; without even the implied warranty of
//    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//    GNU General Public License for more details.
//
//    You should have received a copy of the GNU General Public License
//    along with FreePBX.  If not, see <http://www.gnu.org/licenses/>.
//
//    Copyright (C) 2006 Rob Thomas



function check_schema_device () {
	$schema = simplexml_load_file(dirname(dirname(__FILE__)) . "/sccp/sccp.xml");
	$eldevice = $schema->xpath("section[@name='device']/params/param");
	$results = sql ("SHOW COLUMNS FROM sccpdevice;","getAll",DB_FETCHMODE_ASSOC);
	foreach ($eldevice as $value){
		$atts_array = (array)$value->attributes();
		$name = $atts_array["@attributes"]["name"];
		$required = $value->required;
		$type = $value->type;
		$size = $value->size;
		$description = $value->description;
		$default = $value->default;
		$generic_parser = $value->generic_parser;
		$match = false;
		foreach ($results as $result){
			if (strtolower($name) == strtolower($result["Field"])){
				$match = true;
			}
		}
		if (!$match){
			//print_r($value);
		}
	}
}
function check_schema_line () {
	$schema = simplexml_load_file("./admin/modules/sccp/sccp.xml");
	$eldevice = $schema->xpath("section[@name='line']/params/param");
	foreach ($eldevice as $value){
		$atts_array = (array)$value->attributes();
		$name = $atts_array["@attributes"]["name"];
		$required = $value->required;
		$type = $value->type;
		$size = $value->size;
		$description = $value->description;
		$default = $value->default;
		$generic_parser = $value->generic_parser;
		$results = sql ("SHOW COLUMNS FROM SCCPDEVICE;");
	}
}

function sccp_devices_list($getall=false) {
        $results = sql("SELECT * FROM sccpdevice order by description,name asc","getAll",DB_FETCHMODE_ASSOC);
        if(is_array($results)){
                foreach($results as $result){
                        // check to see if we have a dept match for the current AMP User.
                                // return this item's dialplan destination, and the description
                                $allowed[] = $result;
                } 
        }
        if (isset($allowed)) {
                return $allowed;
        } else { 
                return null;
        }
}
function sccp_lines_list($getall=false) {
        $results = sql("SELECT * FROM sccpline order by name asc","getAll",DB_FETCHMODE_ASSOC);
        if(is_array($results)){
                foreach($results as $result){
                        // check to see if we have a dept match for the current AMP User.
                                // return this item's dialplan destination, and the description
                                $allowed[] = $result;
                }
        }
        if (isset($allowed)) {
                return $allowed;
        } else {
                return null;
        }
}
function sccp_delete_device($device){
	sql ("delete from sccpdevice where name='" . $device . "'") ;	
}
function sccp_update_device($vars){
	if (strlen($vars["name"])>1){
	$query = "UPDATE sccpdevice SET ";
	$i=0;
	foreach ($vars as $key=>$value){
		$i++;
		if ($i>1){ $query .= ",";}
		$query .= $key ."=";
		if (trim($value)==""){
			$query .= "null";
		}elseif (substr($value,0,2)=="0x" ){
			$query .= "0x" . bin2hex($value);
		}elseif (is_numeric($value)){
				$query .= "'". trim($value) . "'";
		}else{
			$query .= "0x" . bin2hex($value);
		}
	}
	$query .= " WHERE name='" . $vars["name"] . "'";
	sql($query);
	echo $query;
	}else{die ("error!!!");};
}
function sccp_delete_line($line){
	sql ("delete from sccpline where name='" . $line . "'") ;	
}
function sccp_update_line($vars){
	if (strlen($vars["name"])>1){
		$query = "UPDATE sccpline SET ";
		$i=0;
		foreach ($vars as $key=>$value){
			$i++;
			if ($i>1){ $query .= ",";}
			$query .= $key ."=";
			if (trim($value)==""){
				$query .= "null";
			}elseif (substr($value,0,2)=="0x" ){
				$query .= "0x" . bin2hex($value);
			}elseif (is_numeric($value)){
				$query .= "'". trim($value) . "'";
			}else{
				$query .= "0x" . bin2hex($value);
			}
		}
		$query .= " WHERE name='" . $vars["name"] . "'";
		sql($query);
		echo $query;
	}else{die ("error!!!");};
}


function sccp_add_device($vars){
	
	$query = "insert into sccpdevice (";
	$i=0;
	foreach ($vars as $key=>$value){
		$i++;
		if ($i>1){ $query .= ",";}
		$query .= $key;
	}
	$query .= ") VALUES (";
	$i=0;
	foreach ($vars as $key=>$value){
		$i++;
		if ($i>1){ $query .= ",";}
		if (trim($value)==""){
			$query .= "null";
		}elseif (substr($value,0,2)=="0x" ){
				$query .= "0x" . bin2hex($value);
		}elseif (is_numeric($value)){
				$query .= "'". trim($value) . "'";
		}else{
			$query .= "0x" . bin2hex($value);			
		}
	}
	$query .= ")";
	sql($query);
	try {
			if (file_exists("/tftpboot/SEPD" .$vars["type"]. ".cnf.xml")){
	        	copy ("/tftpboot/SEPD" .$vars["type"]. ".cnf.xml", "/tftpboot/".$vars["name"].".cnf.xml");
			}
	}catch(Exception $e){
     
    }
}


function sccp_add_line($vars){

	$query = "insert into sccpline (";
	$i=0;
	foreach ($vars as $key=>$value){
		$i++;
		if ($i>1){ $query .= ",";}
		$query .= $key;
	}
	$query .= ") VALUES (";
	$i=0;
	foreach ($vars as $key=>$value){
		$i++;
		if ($i>1){ $query .= ",";}
		if (trim($value)==""){
			$query .= "null";
		}elseif (is_numeric($value)){
				$query .= "'". trim($value) . "'";
		}else{
			$query .= "0x" . bin2hex($value);
		}
	}
	$query .= ")";
	sql($query);

}

function sccp_add_button($vars){

	$query = "insert into buttonconfig (";
	$i=0;
	foreach ($vars as $key=>$value){
		$i++;
		if ($i>1){ $query .= ",";}
		$query .= $key;
	}
	$query .= ") VALUES (";
	$i=0;
	foreach ($vars as $key=>$value){
		$i++;
		if ($i>1){ $query .= ",";}
		if (trim($value)==""){
			$query .= "null";
		}elseif (is_numeric($value)){
			$query .= "'" . trim($value) ."'";		
		}else{
			$query .= "0x" . bin2hex($value);
		}
	}
	$query .= ")";
	sql($query);
}


function sccp_update($req) {
	foreach ($req as $key => $item) {
		// Split up...
		// 0 - action
		// 1 - modulename
		// 2 - featurename
		$arr = explode("#", $key);
		if (count($arr) == 3) {
			$action = $arr[0];
			$modulename = $arr[1];
			$featurename = $arr[2];
			$fieldvalue = $item;
			
			// Is there a more efficient way of doing this?
			switch ($action)
			{
				case "ena":
					$fcc = new featurecode($modulename, $featurename);
					if ($fieldvalue == 1) {
						$fcc->setEnabled(true);
					} else {
						$fcc->setEnabled(false);
					}
					$fcc->update();
					break;
				case "custom":
					$fcc = new featurecode($modulename, $featurename);
					if ($fieldvalue == $fcc->getDefault()) {
						$fcc->setCode(''); // using default
					} else {
						$fcc->setCode($fieldvalue);
					}
					$fcc->update();
					break;
			}
		}
	}

	needreload();
}

function sccp_check_extensions($exten=true) {
	$extenlist = array();
	if (is_array($exten) && empty($exten)) {
		return $extenlist;
	}
	$featurecodes = featurecodes_getAllFeaturesDetailed();

	foreach ($featurecodes as $result) {
		$thisexten = ($result['customcode'] != '')?$result['customcode']:$result['defaultcode'];

		// Ignore disabled codes, and modules, and any exten not being requested unless all (true)
		//
		if (($result['featureenabled'] == 1) && ($result['moduleenabled'] == 1) && ($exten === true || in_array($thisexten, $exten))) {
			$extenlist[$thisexten]['description'] = _("Featurecode: ").$result['featurename']." (".$result['modulename'].":".$result['featuredescription'].")";
			$extenlist[$thisexten]['status'] = 'INUSE';
			$extenlist[$thisexten]['edit_url'] = 'config.php?type=setup&display=featurecodeadmin';
		}
	}
	return $extenlist;
}
?>
