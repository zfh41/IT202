<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<form method="POST">
	<label>AccountType</label>
    	<select name="account_type">
		<option value="0">checking</option>
		<option value="1">saving</option>
		<option value="2">loan</option>
	</select>
	<label>Balance</label>
	<input type="number" min="1" name="balance"/>
	<input type="submit" name="save" value="Create"/>
</form>

<?php
if(isset($_POST["save"])){
	//TODO add proper validation/checks
	$account_number = sprintf('%12d', rand(0,999999));
	$account_type= $_POST["account_type"];
	$balance = $_POST["balance"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Accounts (account_number, user_id, account_type, balance) VALUES(null, :user, :account_type, :balance)");
	$r = $stmt->execute([
		":user"=>$user,
		":account_type"=>$account_type,
		":balance"=>$balance,

	]);
        $account_id=$db->lastInsertId();
        $account_number = str_pad($account_id, 12, "0", STR_PAD_LEFT);
        $stmt = $db->prepare("UPDATE Accounts set account_number = :a where id =:id"); 
	$r = $stmt->execute([
               ":a"=>$account_number,
               ":id"=>$account_id,
        ]);
	if($r){
		echo("Created successfully with account number: " . $account_number);
	}
	else{
		$e = $stmt->errorInfo();
		echo("Error creating: " . var_export($e, true));
	}
    }
?>

<?php require(__DIR__ . "/partials/flash.php");
