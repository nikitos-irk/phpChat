<?php
// php -S localhost:8000

class UserNameException extends Exception { }
class PushMessageException extends Exception { }

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
            "SELECT messages.message_id, messages.message, users.user_name FROM messages
             LEFT JOIN message_recipients ON message_recipients.message_id = messages.message_id
             LEFT JOIN users ON users.user_id = messages.sender_id
             WHERE message_recipients.recipient_id='{$userId}' AND messages.read_status=0"
    	);
    	
        $data = $result->fetchAll();
    	foreach($data as $value){
    		$this->myPDO->query("UPDATE messages set read_status = 1 WHERE message_id = '{$value["message_id"]}'"); // to not get these messages next time
    	}

        // to delete extra data from request response
        $resultMsgs = array();
        foreach ($data as $msgRecord) {
            array_push($resultMsgs,
                array(
                    "message_id" => $msgRecord["message_id"],
                    "message" => $msgRecord["message"],
                    "user_name" => $msgRecord["user_name"]
                )
            );
        }
    	return $resultMsgs;
    }

    function pushMessage($userId, $userName, $msg){

    	try{
			$recipientId = $this->myPDO->query(
				"SELECT user_id FROM users WHERE users.user_name = '{$userName}'"
			)->fetch()["user_id"];
		} catch (Exception $e) {
			throw new UserNameException("Can't get user id by userName", 1);
		}

		$data = ['msg' => $msg, 'userId' => $userId];
		try{
	    	$sql = "INSERT INTO messages (time_stamp, message, read_status, sender_id) VALUES (datetime('NOW'), :msg, 0, :userId)";
			$this->myPDO->prepare($sql)->execute($data);

	    	$sql = "INSERT INTO message_recipients (message_id, recipient_id) VALUES (last_insert_rowid(),'{$recipientId}')";
	    	$this->myPDO->prepare($sql)->execute();
    	} catch (Exception $e){
    		throw new PushMessageException("Push message exception.", 1);
    	}
		
    }

    function onLogin($userName){
    	$result = $this->myPDO->query(
    		"SELECT user_id from users WHERE user_name='{$userName}'"
    	)->fetch();
    	
    	$resultId = -1;

    	if (!$result){
    		$sql = "insert into users (user_name) values ('{$userName}');";
	    	$this->myPDO->prepare($sql)->execute();

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
        // header($_SERVER['SERVER_PROTOCOL'] . ' 407 Internal Server Error', true, 200);
        // echo json_encode(array("a" => 10));
        // echo $_SERVER['PATH_INFO'];
        // $params = explode('/', $_SERVER['PATH_INFO']);
        // foreach($params as $key => $value) {
        // 	echo $key . " = " . $value . "\n";
        // }
		echo json_encode($foo->getMessagesByUserId(1));
    }
}

// function some(){
// 	$foo = new DBWrapper;
// 	// try { 
// 	// 	$foo->pushMessage(1, "rk", "hello again(new)!!!");
// 	// } catch (UserNameException $e){
// 	// 	$data = array('error' => , );
// 	// 	echo json_encode(

// 	// 	)
// 	// }
//     echo json_encode($foo->getMessagesByUserId(1)); // to get string
// 	//echo json_encode( $foo->onLogin("rnk") );
// }
// some();

?>
