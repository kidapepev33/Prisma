<?php
session_start();
session_destroy();
header('Location: /prisma/assets/view/auth/login.html');
exit;
?>