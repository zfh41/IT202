<?php require_once(__DIR__ . "/partials/nav.php"); ?>

<?php
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
?>

<form method="POST">
        <label>AccountType</label>
        <select name="account_type">
                <option value="0">checking</option>
                <option value="1">saving</option>
                <option value="2">loan</option>
        </select>
        <?php if($_GET['type'] == 'saving') : ?> <br/>
           <label>Balance</label>
           <input type="number" min="5" name="balance"/>
           <input type="submit" name="save" value="Create"/>
        <?php else: ?> <br/>
             <label>Balance</label>
             <input type="number" min="1" name="balance"/>
             <input type="submit" name="save" value="Create"/>
        <?php endif; ?>
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
        if($_GET['type'] == 'saving'){
            do_bank_action("000000000000", $account_id, ($amount * -1), $type, "deposit from savings");   
        }
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
