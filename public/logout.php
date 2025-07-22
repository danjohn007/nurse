<?php
session_start();
session_destroy();
header('Location: ../views/solicitud/form.php');
exit();
?>