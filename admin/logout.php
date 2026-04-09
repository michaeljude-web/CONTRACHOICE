<?php
require_once '../includes/admin/auth.php';
adminLogout();
header('Location: login.php');
exit;