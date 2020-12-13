<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
$query = "";
$results = [];
$startDate=null;
$endDate=null;
$actionType=null;
$total = 0;
$param=[];
if (isset($_POST["startDate"]) && isset($_POST["endDate"])) {
    $startDate = $_POST["startDate"];
    $startD = date('Y-m-d H:i:s', strtotime($startDate));
    $endDate = $_POST["endDate"];
    $endD = date('Y-m-d H:i:s', strtotime($endDate));
}

if (isset($_POST["actionType"])) {
    $actionType=$_POST["actionType"];
}

$query="SELECT count(*) as total FROM Transactions WHERE 1=1";
$queryd="SELECT * FROM Transactions WHERE 1=1";

if(isset($startD) && isset($endD)){
    $query .=" AND created BETWEEN :s and :d";
    $queryd .=" AND created BETWEEN :s and :d";
    $param[":s"]=$startDate;
    $param[":d"]=$endDate;
}

if(isset($actionType)){
    $query .=" AND action_type = :a";
    $queryd .=" AND action_type = :a";
    $param[":a"]=$actionType;
}

$db = getDB();
$stmt = $db->prepare($query);

$r = $stmt->execute($param);

if ($r) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    else {
        flash("There was a problem fetching the results " . var_export($stmt->errorInfo,true));
    }

$total = 0;

if($result){
    $total = (int)$result["total"];
}

$page=1;
$per_page=10;

if(isset($_GET["page"])){
    try {
        $page = (int)$_GET["page"];
    }
    catch(Exception $e){

    }
}
$total_pages = ceil($total / $per_page);
$offset = ($page-1) * $per_page;

$queryd.=" LIMIT :offset, :count";

$param[":offset"] = $offset;
$param[":count"] = $per_page;

$stmt = $db->prepare($queryd);

foreach($param as $key=>$val){
    if($key == ":offset" || $key == ":count"){
        $stmt->bindValue($key, $val, PDO::PARAM_INT);
    }
    else{
        $stmt->bindValue($key, $val);
    }
}

$stmt->execute();
$e = $stmt->errorInfo();
if($e[0] != "00000"){
    flash(var_export($e, true), "alert");
}
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<form method="POST">
    <input name="startDate" placeholder="startDate" value="<?php safer_echo($startDate); ?>"/>
    <input name="endDate" placeholder="endDate" value="<?php safer_echo($endDate); ?>"/>
    <input name="actionType" placeholder="actionType" value="<?php safer_echo($actionType); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form>
        <div class="container-fluid">
        <h3>My Transactions</h3>
        <div class="row">
        <div class="card-group">
    <?php if (count($results) > 0): ?>
            <?php foreach ($results as $r): ?>
                <div class="col-auto mb-3">
                    <div class="card" style="width: 18rem;">
                        <div class="card-body">
                            <div class="card-title">
                            <div>Transaction ID: <?php safer_echo($r["id"]); ?></div>
                            </div>
                            <div class="card-text">
                                <div>Account Source ID: <?php safer_echo($r["act_src_id"]); ?></div>
                                <div>Account Dest ID: <?php safer_echo($r["act_dest_id"]); ?></div>
                                <div>Amount: <?php safer_echo($r["amount"]); ?></div>
                                <div>Action Type: <?php safer_echo($r["action_type"]); ?></div>
                                <div>Memo: <?php safer_echo($r["memo"]); ?></div>
                                <div>Expected Total: <?php safer_echo($r["expected_total"]); ?></div>
                                <div>Created: <?php safer_echo($r["created"]); ?></div>
                                <div>
                                    <a type="button" href="test_edit_transactions.php?id=<?php safer_echo($r['id']); ?>">Edit</a>
                                    <a type="button" href="test_view_transactions.php?id=<?php safer_echo($r['id']); ?>">View</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
    <?php else: ?>
    <div class="col-auto">
        <div class="card">
            You don't have any transactions.
        </div>
    </div>
    <?php endif; ?>
        </div>
        </div>
            <nav aria-label="My Transactions">
            <ul class="pagination justify-content-center">
                <li class="page-item <?php echo ($page-1) < 1?"disabled":"";?>">
                    <a class="page-link" href="?page=<?php echo $page-1;?>" tabindex="-1">Previous</a>
                </li>
                <?php for($i = 0; $i < $total_pages; $i++):?>
                <li class="page-item <?php echo ($page-1) == $i?"active":"";?>"><a class="page-link" href="?page=<?php echo ($i+1);?>"><?php echo ($i+1);?></a></li>
                <?php endfor; ?>
                <li class="page-item <?php echo ($page) >= $total_pages?"disabled":"";?>">
                    <a class="page-link" href="?page=<?php echo $page+1;?>">Next</a>
                </li>
            </ul>
        </nav>
    </div>
          

<?php require(__DIR__ . "/partials/flash.php");

