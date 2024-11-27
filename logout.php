<?php
session_start();
session_unset();
session_destroy();

header("Location: /AsistenciaVirtual/View/login.php");
exit();
?>
