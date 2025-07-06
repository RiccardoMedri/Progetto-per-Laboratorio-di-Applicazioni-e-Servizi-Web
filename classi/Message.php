<?php
define('PROJECT_ROOT', dirname(__DIR__));
require_once (PROJECT_ROOT . '/inc/require.php');


class Message {
    protected $mes_id;
    static protected $table = 'messages';
    public $record;
    public $data;

    function __construct($id)
    {
        $this->mes_id = $id;
        $this->setRecord();
        $this->setData();
    }

    private function setRecord()
    {
        global $dbo;
        $this->record = $dbo->find(self::$table, 'mes_id', $this->mes_id);
    }

    private function setData()
    {
        $this->data = $this->record;
        $this->data['mes_date'] = Utils::formatDateTime($this->record['mes_date']);
        
        $user = new User($this->record['mes_author_id']);
        $this->data = array_merge($this->data, $user->data);
    }

    static function getMessage($fields, $value)
    {
        global $dbo;
        return $dbo->find(self::$table, $field, $value);
    }

    static function getMessages($conditions = [], $orderBy = '', $columns = null)
    {
        global $dbo;
        return $dbo->findAll(self::$table, $conditions, null, $orderBy, $columns);
    }

    static function addMessage($data)
    {
        global $dbo;
        $ticket = new Ticket($data['mes_ticket_id']);

        // Ulteriore controllo sull'utente loggato per garantire
        if($_SESSION['user_role'] === "cliente" && $_SESSION['user_id'] != $ticket->record['tic_user_id']){
            die("Utente loggato non è proprietario del ticket associato a questa conversazione");
        }

        $data['mes_author_id'] = $_SESSION['user_id'];

        $added = $dbo->insert(self::$table, $data);
        if(!$added) {
            die("Messaggio Non Consegnato");
        }
        return $added;
    }

}