<?php

include_once "conf/config.php";

//get page template
$htmlpage = file_get_contents("template/main.html");
$login = file_get_contents("template/login.html");
$rebuild = file_get_contents("template/rebuild.html");
$page = $rebuild;

/**
 * Check for user password
 *
 * @return void
 */
function VerifyLogin() {
    global $Password;

    $correct_password = $Password;
    $entered_password = $_POST['password'];
    
    if ($entered_password === $correct_password) {
        // Right password, user logged
        $_SESSION["authenticated"] = true;
    } else {
        // Wrong password
        $_SESSION['error'] = 'Wrong password !!';
    }
}

// reopen session
if(!isset($_SESSION)) { session_start(); }

// Check for password sent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    VerifyLogin();
}

// check for user log in
if ((!isset($_SESSION["authenticated"])) || ($_SESSION["authenticated"] !== true)) {
    $page = $login; 

    // Update error message
    $error_message = '';
    if (isset($_SESSION['error'])) {
        $error_message = '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
        unset($_SESSION['error']);
    }
    
    // Sostituisci placeholder nel login template
    $page = str_replace("<!-- ERROR-MESSAGE -->", $error_message, $page);
}

# Page content
$htmlpage = str_replace("<!-- PAGE-CONTENT -->", $page, $htmlpage);
$htmlpage = str_replace("<!-- APP-TITLE -->", APP_TITLE, $htmlpage);
$htmlpage = str_replace("<!-- VERSION -->", VERSION, $htmlpage);

// send generated code to web browser
echo $htmlpage;