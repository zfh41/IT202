<?php
session_start();//we can start our session here so we don't need to worry about it on other pages
require_once(__DIR__ . "/db.php");
//this file will contain any helpful functions we create
//I have provided two for you
function is_logged_in() {
    return isset($_SESSION["user"]);
}

function has_role($role) {
    if (is_logged_in() && isset($_SESSION["user"]["roles"])) {
        foreach ($_SESSION["user"]["roles"] as $r) {
            if ($r["name"] == $role) {
                return true;
            }
        }
    }
    return false;
}

function get_username() {
    if (is_logged_in() && isset($_SESSION["user"]["username"])) {
        return $_SESSION["user"]["username"];
    }
    return "";
}

function get_email() {
    if (is_logged_in() && isset($_SESSION["user"]["email"])) {
        return $_SESSION["user"]["email"];
    }
    return "";
}

function get_user_id() {
    if (is_logged_in() && isset($_SESSION["user"]["id"])) {
        return $_SESSION["user"]["id"];
    }
    return -1;
}

function safer_echo($var) {
    if (!isset($var)) {
        echo "";
        return;
    }
    echo htmlspecialchars($var, ENT_QUOTES, "UTF-8");
}

//for flash feature
function flash($msg) {
    if (isset($_SESSION['flash'])) {
        array_push($_SESSION['flash'], $msg);
    }
    else {
        $_SESSION['flash'] = array();
        array_push($_SESSION['flash'], $msg);
    }

}

function getMessages() {
    if (isset($_SESSION['flash'])) {
        $flashes = $_SESSION['flash'];
        $_SESSION['flash'] = array();
        return $flashes;
    }
    return array();
}

//end flash

function getState($n) {
    switch ($n) {
        case 0:
            echo "Checking";
            break;
        case 1:
            echo "Saving";
            break;
        case 2:
            echo "Loan";
            break;
        default:
            echo "Unsupported state: " . safer_echo($n);
            break;
    }
}

function do_bank_action($account1, $account2, $amountChange, $type, $memo){
        $db = getDB();
        $stmt = $db->prepare("select sum(amount) as ExpectedTotal from Transactions where act_src_id = :id");
        $stmt->execute([":id"=>$account1]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $a1total = (int)$result["ExpectedTotal"];
        $a1total -= $amountChange;

        $stmt = $db->prepare("select sum(amount) as ExpectedTotal from Transactions where act_src_id = :id");
        $stmt->execute([":id"=>$account2]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $a2total = (int)$result["ExpectedTotal"];
        $a2total += $amountChange;

        $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `memo`, `expected_total`)
        VALUES(:p1a1, :p1a2, :p1change, :type, :memo, :a1total),
                        (:p2a1, :p2a2, :p2change, :type, :memo, :a2total)";

        $stmt = $db->prepare($query);
        $stmt->bindValue(":p1a1", $account1);
        $stmt->bindValue(":p1a2", $account2);
        $stmt->bindValue(":p1change", $amountChange);
        $stmt->bindValue(":type", $type);
        $stmt->bindValue(":memo", $memo);
        $stmt->bindValue(":a1total", $a1total);
        //flip data for other half of transaction
        $stmt->bindValue(":p2a1", $account2);
        $stmt->bindValue(":p2a2", $account1);
        $stmt->bindValue(":p2change", ($amountChange*-1));
        $stmt->bindValue(":type", $type);
        $stmt->bindValue(":memo", $memo);
        $stmt->bindValue(":a2total", $a2total);
        $result = $stmt->execute();
        if($result){
                echo("Transaction created successfully!");
        }
        else{
                echo("Error creating transaction.");
        }
        return $result;
}

function getWorldAccount() {
     $db = getDB();
     $stmt = $db->prepare("SELECT id from Accounts where account_number='000000000000'");
     $r=$stmt->execute();
     if(!$r){
         echo var_export($stmt->errorInfo(), true);
         return -1;
     }
     $result=$stmt->fetch(PDO::FETCH_ASSOC);
     return (int)$result["id"];

}

function calcAPY(){
	$db = getDB();
	$numOfMonths = 1;//1 for monthly
	$stmt = $db->prepare("SELECT id, apy, balance FROM Accounts WHERE account_type = 'loan' AND IFNULL(nextAPY, TIMESTAMPADD(MONTH,:months,created)) <= current_timestamp"); 
	$r = $stmt->execute([":months"=>$numOfMonths]);
	if($r){
		$accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
		if($accounts){
			$stmt = $db->prepare("SELECT id FROM Accounts where account_number = '000000000000'");
			$r = $stmt->execute();
			if(!$r){
				flash(var_export($stmt->errorInfo(), true), "danger");
			}
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			$world_id = $result["id"];
			foreach($accounts as $account){
				$apy = $account["apy"];
				//if monthly divide accordingly
				$apy /= 12;
				$balance = (float)$account["balance"];
				$change = $balance * $apy;
				//see https://github.com/MattToegel/IT202/blob/Fall2019/Section16/sample_transactions.php
				//last column added supports $memo which my example in the link above doesn't support
				do_bank_action($world_id, $account["id"], ($change * -1), "interest", "APY Calc");
				
				$stmt = $db->prepare("UPDATE Accounts set balance = (SELECT IFNULL(SUM(amount_change),0) FROM Transactions WHERE act_src_id = :id), nextAPY = TIMESTAMPADD(MONTH,:months,current_timestamp) WHERE id = :id");
				$r = $stmt->execute([":id"=>$account["id"], ":months"=>$numOfMonths]);
				if(!$r){
					flash(var_export($stmt->errorInfo(), true), "danger");
				}
			}
		}
	}
	else{
		flash(var_export($stmt->errorInfo(), true), "danger");
	}
}




?>
