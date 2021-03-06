<?php
/*
NAME:         X10
ABOUT:        Turns off and on X10 devices. Must be preset as shown
DEPENDENCIES: None
*/

$serviceList[] = alice_x10_status();

function alice_x10_status()
{
	if(is_writable("/dev/ttyUSB0"))
	{
		$sMessage = "Awaiting orders.";
		$sStatus = "0";
	}
	else
	{
		$sMessage = "Not listening.";
		$sStatus = "2";
	}

	return array("title"=>"X10", "message"=>$sMessage, "status"=>$sStatus);
}

function alice_x10($device, $action, $amount = 1)
{ 
	#alice_notification_add("x10", "$device - $action - $amount");
	if($action == "on" || $action == "off")
	{
		exec("nohup /usr/local/bin/flipit flip $device $action > /dev/null 2>&1 & echo $!");
	}
	else
	{
		exec("nohup /usr/local/bin/flipit $action $device $amount > /dev/null 2>&1 & echo $!");
	}
	alice_x10_update($device, $action, $amount);
	sleep(1);
	return true;
	
}

function alice_x10_getSingle($code)
{
	$db = new PDO('mysql:host='.MYSQL_SERVER.';dbname='.MYSQL_DB.';charset=utf8', MYSQL_USER, MYSQL_PASS);
	$stmt = $db->prepare("SELECT * FROM a_x10 WHERE (code = :code) LIMIT 1");
	$stmt->execute(array(':code'=>$code));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	$device = $rows[0];
	$name = explode("_", $device['name']);
	$device['name'] = $name[1];
	
	return $device;
}
function alice_x10_getGroup($group)
{
	$db = new PDO('mysql:host='.MYSQL_SERVER.';dbname='.MYSQL_DB.';charset=utf8', MYSQL_USER, MYSQL_PASS);
	$stmt = $db->prepare("SELECT * FROM a_x10 WHERE (name LIKE :name)");
	$stmt->execute(array(':name'=>"%{$group}_%"));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);	
	foreach ($rows as $row)
	{
		$name = str_replace($group."_", "", $row['name']);
		$array[] = array("name"=>$name, "code"=>$row['code'], "type"=>$row['type'], "curState"=>intval($row['curState']));
	}

	return $array;
}
function alice_x10_update($code, $newState = -1, $amount = 1)
{
	$device = alice_x10_getSingle($code);
	
	$db = new PDO('mysql:host='.MYSQL_SERVER.';dbname='.MYSQL_DB.';charset=utf8', MYSQL_USER, MYSQL_PASS);
	
	if ($newState == -1) $stmt = $db->prepare("UPDATE a_x10 SET curState='-1' WHERE (code = :code)");
	elseif ($device['type'] == "chime")
	{
		$stmt = $db->prepare("UPDATE a_x10 SET curState='0' WHERE (code = :code)");
	}
	elseif ($device['type'] == "appliance")
	{
		if ($newState == "on") $stmt = $db->prepare("UPDATE a_x10 SET curState='1' WHERE (code = :code)");
		else $stmt = $db->prepare("UPDATE a_x10 SET curState='0' WHERE (code = :code)");
	}
	else
	{
		if ($newState == "brighten") $stmt = $db->prepare("UPDATE a_x10 SET curState=curState+$amount WHERE (code = :code)");
		elseif ($newState == "dim") $stmt = $db->prepare("UPDATE a_x10 SET curState=curState-$amount WHERE (code = :code)");
		elseif ($newState == "on") $stmt = $db->prepare("UPDATE a_x10 SET curState='10' WHERE (code = :code)");
		else $stmt = $db->prepare("UPDATE a_x10 SET curState='0' WHERE (code = :code)");
	}
	$stmt->execute(array(':code'=>$code));
	$stmt = null;
}
?>
