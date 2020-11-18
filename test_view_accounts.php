<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
//we'll put this at the top so both php block have access to it
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
?>
<?php
//fetching
$result = [];
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT Accounts.id,account_number,user_id,account_type,opened_date,last_updated,balance, Users.username FROM Accounts as Accounts JOIN Users on Accounts.user_id = Users.id where Accounts.id = :id");
    $stmt2 = $db->prepare("SELECT id,act_src_id,act_dest_id,amount,action_type,expected_total,created FROM Transactions where act_src_id=:id limit 10");
    $r = $stmt->execute([":id" => $id]);
    $r2 = $stmt2->execute([":id" => $id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $result2=$stmt2->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        $e = $stmt->errorInfo();
        flash($e[2]);
    }




}
?>
<?php if (isset($result) && !empty($result)): ?>
    <div class="card">
        <div class="card-title">
            <?php safer_echo($result["account_number"]); ?>
        </div>
        <div class="card-body">
            <div>
                <p>Stats</p>
                <div> ID: <?php safer_echo($result["id"]); ?></div>
                <div>Account Number: <?php safer_echo($result["account_number"]); ?></div>
                <div>Account Type: <?php getState($result["account_type"]); ?></div>
                <div>Balance: <?php safer_echo($result["balance"]); ?></div>
                <div>Opened Date: <?php safer_echo($result["opened_date"]); ?></div>
                <div>Last Updated: <?php safer_echo($result["last_updated"]); ?></div>
                <div>Owned by: <?php safer_echo($result["username"]); ?></div>
            </div>
        </div>
    </div>
<?php else: ?>
<p>Error looking up id...</p>
<?php endif; ?>

<?php if (isset($result2) && !empty($result2)): ?>
    <br/>
    <div class ="card">
       <div class="card-title">
           <div> Transaction History: </div>
       </div>
              <div class="card-body">
                     <div>
                        <div> Transaction Type: <?php safer_echo($result2["action_type"]); ?></div>
                        <div> SourceID: <?php safer_echo($result2["act_src_id"]); ?></div>
                        <div> DestID: <?php safer_echo($result2["act_dest_id"]); ?></div>
                        <div> Created: <?php safer_echo($result2["created"]); ?></div>
                    </div>
              </div>
       </div>
    </div>
<?php else: ?>
<p>Error looking up id...</p>
<?php endif; ?>
