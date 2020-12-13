<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
//we use this to safely get the email to display
$fname = "";
$lname="";
if (isset($_SESSION["user"]) && isset($_SESSION["user"]["fname"]) && isset($_SESSION["user"]["lname"])) {
    $fname = $_SESSION["user"]["fname"];
    $lname = $_SESSION["user"]["lname"];
    $user = $_SESSION["user"];
}
?>
<p>Welcome, </p> <?php echo $fname?> <?php echo $lname?>
