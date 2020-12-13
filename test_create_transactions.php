<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
function do_bank_action($account1, $account2, $amountChange, $type){
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

        $query = "INSERT INTO `Transactions` (`act_src_id`, `act_dest_id`, `amount`, `action_type`, `expected_total`) 
        VALUES(:p1a1, :p1a2, :p1change, :type, :a1total), 
                        (:p2a1, :p2a2, :p2change, :type, :a2total)";

        $stmt = $db->prepare($query);
        $stmt->bindValue(":p1a1", $account1);
        $stmt->bindValue(":p1a2", $account2);
        $stmt->bindValue(":p1change", $amountChange);
        $stmt->bindValue(":type", $type);
        $stmt->bindValue(":a1total", $a1total);
        //flip data for other half of transaction
        $stmt->bindValue(":p2a1", $account2);
        $stmt->bindValue(":p2a2", $account1);
        $stmt->bindValue(":p2change", ($amountChange*-1));
        $stmt->bindValue(":type", $type);
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
?>
<?php
    $result=[];
    $db=getDB();
    $stmt=$db->prepare("SELECT id,balance FROM Accounts LIMIT 10");
    $r = $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST">
        <label>Account</label>
        <select name="account1">
            <?php foreach ($result as $r): ?>
                <option value="<?php safer_echo($r["id"]); ?>">
                <?php safer_echo($r["id"]); ?></option>
            <?php endforeach; ?>
        </select>

        <select name="type">
                <option value="0">deposit</option>
                <option value="1">withdraw</option>
                <option value="2">transfer</option>
        </select>

        <?php if($_GET['type'] == 'transfer') : ?> <br/>
            <label>AccountDest</label>
 	    <select name="account2">
                 <?php foreach ($result as $r): ?>
                    <option value="<?php safer_echo($r["id"]); ?>">
                    <?php safer_echo($r["id"]); ?></option>
                 <?php endforeach; ?>
            </select>
        
        <?php endif; ?>

        <?php if ($_GET['type'] == 'withdraw'): ?>
            <input type="number" min="0" name="amount" placeholder="$0.00" max=$r["balance"]/>
        <?php else: ?>
            <input type="number" min="0" name="amount" placeholder="$0.00" />
        <?php endif; ?>


        <input type="hidden" name="type" value="<?php echo $_GET['type'];?>"/>
        <input type="text" name="memo" placeholder="memo"/>

        <!--Based on sample type change the submit button display-->
        <input type="submit" value="Move Money"/>
</form>

<?php
if(isset($_POST['type']) && isset($_POST['account1']) && isset($_POST['amount'])){
        $type = $_POST['type'];
        $amount = (int)$_POST['amount'];
        switch($type){
                case 'deposit':
                        do_bank_action("000000000000", $_POST['account1'], ($amount * -1), $type);
                        break;
                case 'withdraw':
                        do_bank_action($_POST['account1'], "000000000000", ($amount * -1), $type);
                        break;
                case 'transfer':
                        do_bank_action($_POST['account1'], $_POST['account2'], ($amount * -1), $type);
                        break;
        }

    
}
?>

