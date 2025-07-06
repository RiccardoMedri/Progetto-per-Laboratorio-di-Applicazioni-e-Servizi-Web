<?php
define('PROJECT_ROOT', realpath(__DIR__. '/..'));
require_once (PROJECT_ROOT . '/inc/require.php');

// Controlla che sia presente un ticket id nella richiesta 
if (!isset($_GET['tic_id'])) {
    http_response_code(400);
    echo json_encode(["error" => "ID Ticket mancante"]);
    exit;
}

// Crea il ticket usando l'id della get e controlla che l'user ne sia proprietario
$ticket = new Ticket($_GET['tic_id']);
if($_SESSION['user_role'] === "cliente" && $_SESSION['user_id'] != $ticket->record['tic_user_id']){
    die("Utente loggato non è proprietario del ticket associato a questa conversazione");
}

// Recupera i messaggi legati al ticket
$tic_id = $_GET['tic_id'];
$messages = Message::getMessages(['mes_ticket_id' => $tic_id]);

// Forma la risposta in un array json di messaggi
$response = [];

foreach ($messages as $msg) {
    $m = new Message($msg['mes_id']);
    $response[] = [
        'id' => $m->data['mes_id'],
        'author_id' => $m->data['mes_author_id'],
        'text' => htmlspecialchars($m->data['mes_text']),
        'date' => $m->data['mes_date'],
    ];
}

// Restituisce una risposta in formato JSON
header('Content-Type: application/json');
echo json_encode($response);
?>