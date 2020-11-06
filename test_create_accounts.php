<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<form method="POST">
	<label>AccountNumber</label>
	<input name="account_number" placeholder="AccountNumber"/>
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
	$account_number = $_POST["account_number"];
	$account_type= $_POST["account_type"];
	$balance = $_POST["balance"];
	$user = get_user_id();
	$db = getDB();
	$stmt = $db->prepare("INSERT INTO Accounts (account_number, user_id, account_type, balance) VALUES(:account_number, :user, :account_type, :balance)");
	$r = $stmt->execute([
		":account_number"=>$account_number,
		":user"=>$user,
		":account_type"=>$account_type,
		":balance"=>$balance,
		
	]);
	if($r){
		flash("Created successfully with id: " . $db->lastInsertId());
	}
	else{
		$e = $stmt->errorInfo();
		flash("Error creating: " . var_export($e, true));
	}
}
?>
<?php require(__DIR__ . "/partials/flash.php");
