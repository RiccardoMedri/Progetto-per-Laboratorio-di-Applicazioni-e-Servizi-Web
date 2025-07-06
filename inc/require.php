<?php
session_start();
error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', true);
define('PROJECT_ROOT', dirname(__DIR__));

require_once __DIR__ . '/Database.php';
require_once (PROJECT_ROOT. '/classi/Utils.php');
require_once (PROJECT_ROOT. '/classi/User.php');
require_once (PROJECT_ROOT. '/classi/Ticket.php');
require_once (PROJECT_ROOT. '/classi/Message.php');
require_once (PROJECT_ROOT. '/classi/Attachment.php');


$dbo = new Database();

if (!Utils::loggato()) {
    $msg = "Effettuare il login";
    header("Location: index.php?msg=$msg");
}