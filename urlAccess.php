<?php
require_once "inc/require.php";

// Controlla che siano presenti i campi file e tic_id nella richiesta
if (!isset($_GET['file']) || !isset($_GET['tic_id'])) {
    http_response_code(400);
    exit("Richiesta non valida.");
}

$ticket = new Ticket($_GET['tic_id']);

// Controlla che l'user sia autorizzato all'accesso
if ($_SESSION['user_role'] === "cliente" && $_SESSION['user_id'] != $ticket->record['tic_user_id']) {
    http_response_code(403);
    exit("Non autorizzato.");
}

// Controlla esistenza del percorso del file
$filename = basename($_GET['file']); // Sanitize

// Controlla esistenza dell'allegato a DB e che appartenga al ticket passato nella GET
$attachment = Attachment::getAttachment('att_filename', $filename);
if (!$attachment || $attachment['att_ticket_id'] != $ticket->data['tic_id']) {
    http_response_code(404);
    exit("File non trovato o non associato al ticket.");
}

// Controllo fisico del file 
$filepath = __DIR__ . '/uploads/' . $filename;
if (!file_exists($filepath)) {
    http_response_code(404);
    exit("File non trovato.");
}

// Ottiene l'estensione del file e la converte a carattere lowercase
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

// Crea un array associativo che mappa l'estensione dei file conosciute al loro MIME type ufficiale
$mimeTypes = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'gif' => 'image/gif',
    'txt' => 'text/plain',
    'doc' => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
];

// Mappa l'estensione del file richiesto con il MIME Type corrispondente
$contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

// Specifica al browser quale sia l'estensione del file richiesto, così che sappia come gestire la visualizzazione
// Certi browser non hanno modo di mostrare alcuni formati, per cui forzano il download locale
header('Content-Type: ' . $contentType);
header('Content-Length: ' . filesize($filepath));
header('Content-Disposition: inline; filename="' . $filename . '"');

// Legge il contenuto binario del file da disco e lo manda al browser
readfile($filepath);
exit;
