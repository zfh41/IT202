<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>

<?php
    $result=[];
    $db=getDB();
    $stmt=$db->prepare("SELECT id,balance FROM Accounts");
    $r = $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<form method="POST">
        <label>AccountSrc</label>
        <select name="account1">
            <?php foreach ($result as $r): ?>
                <option value="<?php safer_echo($r["id"]); ?>">
                <?php safer_echo($r["id"]); ?></option>
            <?php endforeach; ?>
        </select>

        <select name="type">
                <option value="0">Deposit</option>
                <option value="1">Withdraw</option>
                <option value="2">transfer</option>
                <option value="3">ext-transfer</option>
        </select>

        <?php if($_GET['type'] == 'transfer') : ?> <br/>
            <label>AccountDest</label>
            <select name="account2">
                 <?php foreach ($result as $r): ?>
                    <option value="<?php safer_echo($r["id"]); ?>">
                    <?php safer_echo($r["id"]); ?></option>
                 <?php endforeach; ?>
            </select>
        <?php elseif($_GET['type'] == 'ext-transfer') : ?> <br/>
            <input type="text" name="lastName" placeholder="lastName" />
            <input type="text" name="account2" placeholder="last4digitsofAccount" /> <br/> <br/>
        <?php endif; ?>

        <?php if ($_GET['type'] == 'withdraw'): ?>
            <input type="number" min="0" name="amount" placeholder="$0.00" max=$r["balance"]/>
        <?php elseif($_GET['type'] == 'ext-transfer') : ?> <br/>
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
        $memo = $_POST['memo'];
        $amount = (int)$_POST['amount'];
        switch($type){
                case 'deposit':
                        do_bank_action("000000000000", $_POST['account1'], ($amount * -1), $type, $memo);
                        break;
                case 'withdraw':
                        do_bank_action($_POST['account1'], "000000000000", ($amount * -1), $type, $memo);
                        break;
                case 'transfer':
                        do_bank_action($_POST['account1'], $_POST['account2'], ($amount * -1), $type, $memo);
                        break;
                case 'ext-transfer':
                        $lname = $_POST['lastName'];
                        $accountnum = $_POST['account2'];
                        $stmt=$db->prepare("SELECT id FROM Accounts JOIN Users on Accounts.user_id = Users.id where Users.lname = :last_name and RIGHT(account_number,4)=:account_number LIMIT 1");
                        $stmt->execute([":account_number"=>$accountnum, ":last_name"=>$lname]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        $account2 = $result['id'];
                        do_bank_action($_POST['account1'], $account2, ($amount * -1), $type, $memo);
                        break;
        }

    
}
?>
