<?php

//get page template
$htmlpage = file_get_contents("templates/main.html");
$login = file_get_contents("templates/login.html");
$rebuild = file_get_contents("templates/rebuild.html");
$page = $rebuild;

// reopen session
if(!isset($_SESSION)) { session_start(); }

// check for user log in
if ((!isset($_SESSION["username"])) || (empty($_SESSION["username"]))) {
    $page = $login; 
}

# Page content
$htmlpage = str_replace("<!-- PAGE-CONTENT -->", $page, $htmlpage);

// send generated code to web browser
echo $htmlpage;