<?php
// php -S localhost:8000

class UserNameException extends Exception { }
class PushMessageException extends Exception { }
class GetMessageException extends Exception { }
class UserIdException extends Exception { }

class DBWrapper{
	private $myPDO;

	function __construct() {
       $this->myPDO = new PDO('sqlite:/Users/kovrin/Documents/bunq/bunq.db');
   	}

    function checkUser($userId){
        if ($this->myPDO->query("select count(1) from users where user_id='{$userId}'")->fetch()["count(1)"] == "0"){
            throw new UserIdException("No such user!", 1);
        }
    }

    function getMessagesByUserId($userId){

        $this->checkUser($userId);
        try{
        	$result = $this->myPDO->query(
                "SELECT messages.message_id, messages.message, users.user_name FROM messages
                 LEFT JOIN message_recipients ON message_recipients.message_id = messages.message_id
                 LEFT JOIN users ON users.user_id = messages.sender_id
                 WHERE message_recipients.recipient_id='{$userId}' AND messages.read_status=0
                 ORDER BY date(messages.time_stamp)"
        	);
        } catch (Exception $e){
            throw new GetMessageException("Can't get messages from DB", 1);
        }
    	
        $data = $result->fetchAll();
    	foreach($data as $value){
            try{
    		  $this->myPDO->query("UPDATE messages set read_status = 1 WHERE message_id = '{$value["message_id"]}'"); // to avoid getting these messages next time
            } catch(Exception $e) {
                // LOG SOME MESSAGE WAS NOT UPDATED AS READ
            }
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

        $this->checkUser($userId);

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
        try{
        	$result = $this->myPDO->query(
        		"SELECT user_id from users WHERE user_name='{$userName}'"
        	)->fetch();
        } catch(Exception $e){
            throw new Exception("Can't make sql request.", 1);
        }
    	
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

$method = $_SERVER['REQUEST_METHOD'];
if (!isset($_SERVER['PATH_INFO'])){
    die;
}
switch ($method) {
    case 'GET':
            $endpoint = explode('/', trim($_SERVER['PATH_INFO'],'/'));
            if (isset($endpoint)){
                
                $userId   = $endpoint[1];
                $endpoint = $endpoint[0];

                if ("user" == $endpoint && isset($userId)){
                    try{
                        $result = $foo->getMessagesByUserId($userId);
                        echo json_encode($result);
                    } catch (UserIdException $e){
                        echo json_encode(array('error_message' => $e->getMessage()));
                    } catch (GetMessageException $e){
                        echo json_encode(array('error_message' => $e->getMessage()));
                    }
                }
            }
        break;
    case 'POST':
        $endpoint = explode('/', trim($_SERVER['PATH_INFO'],'/'));
        if (isset($endpoint)){
            if (isset($endpoint[1])){
                $userId   = $endpoint[1];
                $endpoint = $endpoint[0];
                try{
                    $foo->pushMessage($userId, $_POST["userName"], $_POST["msg"]);
                    echo json_encode(array('error_message'=> ''));
                } catch(PushMessageException $e){
                    echo json_encode(array('error_message' => $e->getMessage()));
                } catch(UserNameException $e){
                    echo json_encode(array('error_message' => $e->getMessage()));
                }
            } else {
                $endpoint = $endpoint[0];
                try{
                    $userId = $foo->onLogin($_POST["userName"]);
                    echo json_encode(array('user_id' => $userId, 'error_message' => ''));
                } catch(Exception $e){
                    echo json_encode(array('error_message' => 'Some problem occured. Try later.'));    
                }
            }
        }
        break;
    default:
        break;
}
?>
