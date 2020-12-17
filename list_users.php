<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
if (!has_role("Admin")) {
    //this will redirect to login and kill the rest of this script (prevent it from executing)
    flash("You don't have permission to access this page");
    die(header("Location: login.php"));
}
?>
<?php
function deactivateUser($id)
{
         $db = getDB();
         $stmt = $db->prepare("UPDATE Users SET active=0 WHERE id=:id");
         $stmt->execute([":id" => $id]);
         echo "Deactivated";
}

$query = "";
$results = [];
$firstName=null;
$lastName=null;
$param=[];
if (isset($_POST["firstName"]) || isset($_POST["lastName"])) {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
}

$query="SELECT count(*) as total FROM Users WHERE 1=1";
$queryd="SELECT * FROM Users WHERE 1=1";

if(isset($firstName)){
    $query .=" AND fname=:fname";
    $queryd .=" AND fname=:fname";
    $param[":fname"]=$firstName;
    $param[":lname"]=$lastName;
}
if(isset($lastName)){
    $query .=" AND lname=:lname";
    $queryd .=" AND lname=:lname";
    $param[":lname"]=$lastName;
    $param[":lname"]=$lastName;
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

if(isset($_POST["deactivate"]))
{
    $id=$_POST["id"];
    deactivateUser($id);
}

?>


<form method="POST">
    <input name="fName" placeholder="firstName" value="<?php safer_echo($firstName); ?>"/>
    <input name="lName" placeholder="lastName" value="<?php safer_echo($lastName); ?>"/>
    <input type="submit" value="Search" name="search"/>
</form>
        <div class="container-fluid">
        <h3>Users</h3>
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
                                <div>User ID: <?php safer_echo($r["id"]); ?></div>
                                <div>Email: <?php safer_echo($r["email"]); ?></div>
                                <div>First Name: <?php safer_echo($r["fname"]); ?></div>
                                <div>Last Name: <?php safer_echo($r["lname"]); ?></div>
                                <div>Created: <?php safer_echo($r["created"]); ?></div>
                            </div>
                            <div>
                                <a type="button" href="" name="deactivate">Deactivate</a>
                                <input type="hidden" name="id" value="<?php echo $r["id"];?>"/>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endforeach; ?>
    <?php else: ?>
    <div class="col-auto">
        <div class="card">
            You don't have any users.
        </div>
    </div>
    <?php endif; ?>
        </div>
        </div>
            <nav aria-label="Users">
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
          

