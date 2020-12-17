<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>


<?php
function closeAccount($id, $balance)
{
        if ($balance == 0){
              $db = getDB();
              $stmt = $db->prepare("UPDATE Accounts SET active=0 WHERE id=:id");
              $stmt->execute([":id" => $id]);
              echo "Deactivated your account";
         }
         else{
              echo "empty your balance";
         }
}

function freezeAccount($id)
{
    $db = getDB();
    $stmt = $db->prepare("UPDATE Accounts SET frozen=1 WHERE id=:id");
    $stmt->execute([":id" => $id]);
    echo "Froze your account";
}

$query = "";
$results = [];
if (isset($_POST["query"])) {
    $query = $_POST["query"];
}
if (isset($_POST["search"]) && !empty($query)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id,account_number,user_id,account_type,opened_date,last_updated, balance, apy, frozen, active from Accounts WHERE account_number like :q LIMIT 10");
    $r = $stmt->execute([":q" => "%$query%"]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}

if(isset($_POST["close"])) {
     if(isset($_POST["id"]) && isset($_POST["balance"]))
     {
        $id = $_POST["id"];
        $balance = $_POST["balance"];
        closeAccount($id, $balance);
     }
}

if(isset($_POST["freeze"])) {
    if(isset($_POST["id"]))
     {
        $id = $_POST["id"];
        freezeAccount($id);
     }
    
}
?>
<form method="POST">
    <input name="query" placeholder="Search" value="<?php safer_echo($query); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form>
<div class="results">
    <?php if (count($results) > 0): ?>
        <div class="list-group">
            <?php foreach ($results as $r): ?>
                <?php if($r['active'] == '1') : ?> <br/>
                    <div class="list-group-item">
                        <div>
                            <div>Account Number:</div>
                            <div><?php safer_echo($r["account_number"]); ?></div>
                        </div>
                        <div>
                            <div>Account Type:</div>
                            <div><?php getState($r["account_type"]); ?></div>
                        </div>
                        <div>
                            <div>Balance:</div>
                            <div><?php safer_echo($r["balance"]); ?></div>
                        </div>
                        <div>
                            <div>APY:</div>
                            <div><?php safer_echo($r["apy"]); ?></div>
                        </div>
                        <?php if($r['frozen'] == '0') : ?> <br/>
                            <div>
                                <a type="button" href="test_edit_egg.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                                <a type="button" href="test_view_accounts.php?id=<?php safer_echo($r['id']); ?>">View</a>
                                <input type="button" name="close" value="Delete Account"/>
                                <a type="button" name="freeze"> Freeze </a>
                            </div>
                        <?php endif; ?>
                        <input type="hidden" name="id" value="<?php echo $r["id"];?>"/>
                        <input type="hidden" name="balance" value="<?php echo $r["balance"];?>"/>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No results</p>
    <?php endif; ?>
</div>
