<?php
require_once "inc/require.php";

$msg = "";
if ($_REQUEST) {
    $operation = (!empty($_REQUEST['operation'])) ? $_REQUEST['operation'] : "";
    unset($_REQUEST['operation']);
    switch ($operation) {
        case 'insert':
            Ticket::insertTicket($_REQUEST);
            break;
        case 'modify':
            Ticket::modifyTicket($_REQUEST);
            break;
        case 'delete':
            Ticket::deleteTicket($_REQUEST);
            break;
        case 'login':
            Utils::login($_REQUEST);
            break;
        case 'register':
            Utils::register($_REQUEST);
            break;
        case 'logout':
            Utils::logout();
            break;
        case 'insertMessage':
            Message::addMessage($_REQUEST);
            break;
        case 'insertAttachment':
            Attachment::addAttachment($_REQUEST);
            break;
    }
}

header('Location: list.php?msg=' . $msg);