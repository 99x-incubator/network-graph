<?php
session_start();

$username = null;
$password = null;

// mod_php
if (isset($_SERVER['PHP_AUTH_USER'])) {
    $username = $_SERVER['PHP_AUTH_USER'];
    $password = $_SERVER['PHP_AUTH_PW'];
} elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    if (strpos(strtolower($_SERVER['HTTP_AUTHORIZATION']),'basic')===0) {
      list($username,$password) = explode(':',base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    }
}

if ($username === 'admin' && $password === '') {
    $_SESSION['auth_success1'] = true;
    require_once 'common.php';
    header("Location: graph.php$dataset_qs");

} else {    
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Text to send if user hits Cancel button';

    die();
}

?>

