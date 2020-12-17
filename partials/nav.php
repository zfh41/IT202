<?php
//we'll be including this on most/all pages so it's a good place to include anything else we want on those pages
require_once(__DIR__ . "/../lib/helpers.php");
?>
<!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
      integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
<!-- jQuery and JS bundle w/ Popper.js -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>


<nav class="navbar navbar-default navbar-light bg-light">
    <a href="home.php">Home</a>
    <?php if (!is_logged_in()): ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
    <?php if (has_role("Admin")): ?>
            <a href="create_accounts.php?type=checking">Create Account</a>
            <a href="list_accounts.php">Accounts</a>
            <a href="create_transactions.php?type=deposit">Create Transactions</a>
            <a href="list_transactions.php">Transactions</a>
            <a href="list_users.php">Users</a>
    <?php endif; ?>
    <?php if (is_logged_in()): ?>
        <a href="profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    <?php endif; ?>
</nav>
