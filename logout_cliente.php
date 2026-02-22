<?php
// logout_cliente.php
session_start();
unset($_SESSION['cliente_id']);
unset($_SESSION['cliente_nombre']);
header("Location: login_cliente.php");
exit;
?>