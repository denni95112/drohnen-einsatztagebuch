<?php
include('auth.php');
logout();
header('Location: index.php');
exit();
?>
