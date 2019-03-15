<?php
// router.php
// php -S localhost:8000 router.php

class DBWrapper{
	private $myPDO;

	function __construct() {
       $this->myPDO = new PDO('sqlite:/Users/kovrin/Documents/bunq/bunq.db');
   	}

	function selectAllMessages() { 
        $result = $this->myPDO->query("SELECT * FROM messages");
        $s = "";
        foreach($result as $row)
	    {
	        $s = $s . $row["message"] ."\n";
	    }
	    return $s;
    }

    function getMessagesByUserId($userId){
    	$result = $this->myPDO->query(
    		"SELECT messages.message_id, messages.message FROM messages LEFT JOIN message_recipients ON message_recipients.message_id = messages.message_id WHERE message_recipients.recipient_id='{$userId}' AND messages.read_status=0"
    	);
    	$data = $result->fetchAll();
    	foreach($data as $value){
    		$this->myPDO->query("UPDATE messages set read_status = 1 WHERE message_id = '{$value["message_id"]}'"); // to not get these messages next time
    	}
    	return $data;
    }

    function pushMessage($userId, $userName, $msg){
		$recipientId = $this->myPDO->query(
			"SELECT user_id FROM users WHERE users.user_name = '{$userName}'"
		)->fetch()["user_id"];

    	$sql = "INSERT INTO messages (time_stamp, message, read_status, sender_id) VALUES (datetime('NOW'), '{$msg}', 0, '{$userId}')";
		$stmt = $this->myPDO->prepare($sql);
		$stmt->execute();

    	$sql = "INSERT INTO message_recipients (message_id, recipient_id) VALUES (last_insert_rowid(),'{$recipientId}')";
    	$stmt = $this->myPDO->prepare($sql);
		$stmt->execute();
    }

    function onLogin($userName){
    	$result = $this->myPDO->query(
    		"SELECT user_id from users WHERE user_name='{$userName}'"
    	)->fetch();
    	
    	$resultId = -1;

    	if (!$result){
    		$sql = "insert into users (user_name) values ('{$userName}');";
	    	$stmt = $this->myPDO->prepare($sql);
			$stmt->execute();

			$resultId = $this->myPDO->query("SELECT last_insert_rowid();")->fetch()["last_insert_rowid()"];
    	} else {
    		$resultId = $result["user_id"];
    	}
    	return $resultId;
    }

}

$foo = new DBWrapper;

if (preg_match('/\.(?:png|jpg|jpeg|gif)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // сервер возвращает файлы напрямую.
} else {

    if ('POST' == $_SERVER['REQUEST_METHOD']){
        echo json_encode(array("a" => 11));
    }
    if ('GET' == $_SERVER['REQUEST_METHOD']){
        //echo json_encode(array("a" => 10));
        // echo $_SERVER['PATH_INFO'];
        $params = explode('/', $_SERVER['PATH_INFO']);
        foreach($params as $key => $value) {
        	echo $key . " = " . $value . "\n";
        }
		// echo $foo->getMessagesByUserId(1);
    }
}

// function some(){
// 	$foo = new DBWrapper;
// 	$foo->pushMessage(1, "rk", "hello again!!!");
//     echo json_encode($foo->getMessagesByUserId(1)); // to get string
// 	echo json_encode( $foo->onLogin("rnk") );
// }
// some();

?>
