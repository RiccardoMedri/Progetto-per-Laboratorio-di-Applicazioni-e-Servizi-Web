<?php
error_reporting(E_ALL ^ E_DEPRECATED ^ E_NOTICE ^ E_WARNING);
ini_set('display_errors', 'On');
define('PROJECT_ROOT', realpath(__DIR__. '/../..'));

require_once (PROJECT_ROOT . '/inc/Database.php');
$dbo = new Database();

define('APIKEY', 'qwertyuiopasdfghjklzxcvbnm1234567890!?');

function autentication() {
    $key = $_SERVER['HTTP_APIKEY'] 
         ?? $_SERVER['HTTP_Apikey'] 
         ?? $_SERVER['HTTP_apikey'] 
         ?? null;
    if ($key !== APIKEY) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'errore'=>'Accesso negato. APIKEY mancante o errata',
            'received'=>$key
        ]);
        return false;
    }
    return true;
}

try {
    if (!autentication()) {
        return;
    }

    // Ottiene il metod della richiesta
    $requestMethod = $_SERVER["REQUEST_METHOD"];

    // Segmenta l'URI
    $uri = $_REQUEST['uri'] ?? '';
    $uriParts = explode('/', trim($uri, '/'));

    // Lancia un errore se il formato di url non è corretto
    if (count($uriParts) < 1) {
        http_response_code(400);
        echo json_encode(['errore' => 'Formato URI non valido']);
        return;
    }

    // Gestione dinamicamente le classi
    $className = ucfirst($uriParts[0]);
    $id = $uriParts[1] ?? null;         

    // Validate che l'id, quando presente, sia un integer
    if ($id !== null && !ctype_digit($id)) {
        http_response_code(400);
        echo json_encode(['errore' => "ID non valido"]);
        return;
    }
    
    // Ottiene il percorso della classe
    $classPath = PROJECT_ROOT . "/classi/{$className}.php";

    // Lancia errore se non trova il percorso della classe
    if (!file_exists($classPath)) {
        http_response_code(404);
        echo json_encode(['errore' => "Classe $className non trovata $uri"]);
        return;
    }

    // Importa il file della classe e crea un oggetto
    require_once($classPath);
    $obj = $id !== null ? new $className($id) : new $className();

    // Lancia errore se non c'è ID e l'oggetto della classe non viene creato
    if ($id !== null && empty($obj->record)) {
        http_response_code(404);
        echo json_encode(['errore' => "{$className} #{$id} non trovato"]);
        return;
    }

    // Legge il corpo della richiesta
    $jsonBody = file_get_contents('php://input');
    $datiBody = json_decode($jsonBody, true);

    // Lancia errore se il corpo della richiesta non è valido
    if ($requestMethod === 'PUT' && $datiBody === null) {
        http_response_code(400);
        echo json_encode(['errore' => 'Corpo della richiesta non valido o mancante']);
        return;
    }

    // Esegue il routing basato su method + uri
    switch ($requestMethod) {
        case 'GET':
            if (method_exists($obj, 'getDatiApi')) {
                if (!isset($uriParts[2])) {
                    $ret = $obj->getDatiApi();
                }
                else{
                    $ret = Attachment::getAttachments(['att_ticket_id' => $id], "ORDER BY att_upload_date DESC");
                }
            } else {
                $ret = $obj->record ?? $obj; // fallback
            }
            break;

        case 'POST':
            if (!method_exists($obj,'addAttachment')) {
                http_response_code(405);
                echo json_encode(['errore'=>"Metodo POST non supportato per $className"]);
                return;
            }
            // Se non ritorna un array lancia errore
            $ret = $obj->addAttachment($_POST);
            break;

        case 'PUT':
            if (!method_exists($obj, 'update')) {
                throw new Exception("Metodo update non definito");
            }
            $ret = $obj->update($datiBody);
            break;

        case 'DELETE':
            if (method_exists($obj, 'deleteAttachment')) {
                $ret = $obj->deleteAttachment();
                if (!$ret) {
                    http_response_code(500);
                    echo json_encode(['errore' => 'Impossibile eliminare l\'allegato']);
                    return;
                }
            } else {
                http_response_code(405);
                echo json_encode([
                    'errore' => "Metodo DELETE non supportato per $className"
                ]);
                return;
            }
            break;

        default:
            http_response_code(405);
            echo json_encode(['errore' => 'Metodo non supportato']);
            return;
    }

    // Risposta finale
    header('Content-Type: application/json');
    echo json_encode($ret);
} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'errore' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
} finally {
    if ($dbo) {
        $dbo->close();
    }
}
