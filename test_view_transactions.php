<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$results = [];
if (isset($_GET["id"])) {
    $id = $_GET["id"];
}
if (isset($id)) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id,act_src_id,act_dest_id,amount,action_type,memo,expected_total,created from Transactions WHERE id=:q");
    $r = $stmt->execute([":q" => $id]);
    if ($r) {
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results");
    }
}
?>
<div class="results">
<?php if (isset($results)): ?>
    <div class="card">
    <?php foreach ($results as $r): ?>
            <div class="card-title">
                <p>Stats</p>
            </div>
                    <div class="card-body">
                        <div>
                            <div>Account Src ID:</div>
                            <div><?php safer_echo($r["act_src_id"]); ?></div>
                        </div>
                        <div>
                            <div>Account Dest ID:</div>
                            <div><?php safer_echo($r["act_dest_id"]); ?></div>
                        </div>
                        <div>
                            <div>Amount:</div>
                            <div><?php safer_echo($r["amount"]); ?></div>
                        </div>
                        <div>
                            <div>Action Type:</div>
                            <div><?php safer_echo($r["action_type"]); ?></div>
                        </div>
                        <div>
                        <div>Memo:</div>
                        <div><?php safer_echo($r["memo"]); ?></div>
                        </div>
                        <div>
                        <div>Expected Total:</div>
                        <div><?php safer_echo($r["expected_total"]); ?></div>
                        </div>
                        <div>
                            <div>Created:</div>
                            <div><?php safer_echo($r["created"]); ?></div>
                        </div>
                        <div>
                            <a type="button" href="test_edit_transactions.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                            <a type="button" href="test_view_transactions.php?id=<?php safer_echo($r['id']); ?>">View</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>No results</p>
        <?php endif; ?>
</div>
<?php require(__DIR__ . "/partials/flash.php");
