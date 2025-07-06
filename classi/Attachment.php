<?php
define('PROJECT_ROOT', dirname(__DIR__));
require_once (PROJECT_ROOT . '/inc/require.php');


class Attachment {
    protected $att_id;
    static protected $table = 'attachments';
    public $record;
    public $data;

    function __construct($id)
    {
        $this->att_id = $id;
        $this->setRecord();
        $this->setData();
    }

    private function setRecord()
    {
        global $dbo;
        $this->record = $dbo->find(self::$table, 'att_id', $this->att_id);
    }

    private function setData()
    {
        $this->data = $this->record;
        $this->data['att_upload_date'] = Utils::formatDateTime($this->record['att_upload_date']);
    }

    static function getAttachment($fields, $value)
    {
        global $dbo;
        return $dbo->find(self::$table, $field, $value);
    }

    static function getAttachments($conditions = [], $orderBy = '', $columns = null)
    {
        global $dbo;
        return $dbo->findAll(self::$table, $conditions, null, $orderBy, $columns);
    }

    static function addAttachment($data)
    {
        global $dbo;
        $ticket = new Ticket($data['att_ticket_id']);

        if($_SESSION['user_role']==='cliente'){
            if ($_SESSION['user_id'] != $ticket->data['tic_user_id']){
                die("Utente loggato non è proprietario del ticket associato a questa conversazione");
            }
        }

        // Valida presenza di file e assenza di messaggi di erroer
        if (!isset($_FILES['attachment']) || $_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Errore durante il caricamento del file"); //invece di lanciare un errore forse dovremmo annnullare l'azione?
        }

        // Costruisce il percorso di salvataggio del file
        $filename = basename($_FILES['attachment']['name']);
        $target_dir = __DIR__ . "/../uploads/";
        $web_path = "uploads/" . $filename;
        $target_path = $target_dir . $filename;
        
        // Se lo spostamento del file va a buon fine, prosegue con l'aggiunta a DB delle informazioni
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {

                $data['att_filename'] = $filename;
                $data['att_filepath'] = $web_path;
                $added = $dbo->insert(self::$table, $data);

                if (!$added) {
                    throw new Exception("Impossibile inserire il record in DB");
                }

                return [
                'success'     => true,
                'att_id'      => $added,
                'att_filename'=> $filename,
                'att_filepath'=> $web_path
                ];
        }
        else {
                error_log("Failed move_uploaded_file from {$file['tmp_name']} to {$target_path}");
                throw new Exception("Impossibile spostare il file. Controlla i permessi e il path.");
            }
    }

    // Non viene fatto controllo sull'user loggato per garantire statelessness dell'API
    public function deleteAttachment() {
        global $dbo;
        return $dbo->delete(self::$table, 'att_id', $this->att_id);
    }
}