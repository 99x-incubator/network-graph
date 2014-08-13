<?php
    session_start();
    unset($_SESSION['auth_success']);
    echo $_SESSION['auth_success'];
    session_destroy();
?>
