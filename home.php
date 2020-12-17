<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$email = "";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["email"])) {
    $email = $_SESSION["user"]["email"];
    $db = getDB();
    $stmt = $db->prepare("select fname,lname from Users where email = :email"); 
    $stmt->execute([":email"=>$email]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $fname=$result["fname"];
    $lname=$result["lname"];
}
?>
<?php if (isset($fname) && isset($lname)): ?> 
    <p>Welcome, <?php echo $fname; ?> <?php echo $lname; ?></p>
<?php else: ?>
   <center> <img src="https://assets.prucenter.com/event-main/_639x639_crop_center-center_none/njit.jpg?mtime=20180424104248&focal=none" class="center" /></center>
<?php endif; ?>

