<?php
define('PROJECT_ROOT', dirname(__DIR__));
require_once (PROJECT_ROOT . '/inc/require.php');


class Ticket
{
    protected $tic_id;
    static protected $table = 'ticket';
    public $record;
    public $data;

    function __construct($id)
    {
        $this->tic_id = $id;
        $this->setRecord();
        $this->setData();
    }

    private function setRecord()
    {
        global $dbo;
        $this->record = $dbo->find(self::$table, 'tic_id', $this->tic_id);
    }

    private function setData()
    {
        $this->data = $this->record;
        $this->data['tic_creation_date'] = Utils::formatDateTime($this->record['tic_creation_date']);
        
        $user = new User($this->record['tic_user_id']);
        $this->data = array_merge($this->data, $user->data);
    }

    static function getTicket($fields, $value)
    {
        global $dbo;
        return $dbo->find(self::$table, $field, $value);
    }

    static function getTickets($conditions = [], $orderBy = '', $columns = null)
    {
        global $dbo;
        return $dbo->findAll(self::$table, $conditions, null, $orderBy, $columns);
    }

    static function getTicketsByUsers($where = "", $bind = [])
    {
        global $dbo;
        $query = "SELECT * FROM " . self::$table .
            " JOIN users ON tic_user_id = user_id " .
            " WHERE 1 $where ";

        $dbo->query($query);
        $dbo->execute($bind);
        $rows = $dbo->fetchAll();
        return $rows;
    }

    static function insertTicket($data)
    {
        global $dbo;
        
        // Questa logica era inizialmente posizionata in createTicket.php ma è stata poi riposizionata qui per evitare che
        // user non autorizzati manomettano i dati e facciano spoofing delle richieste POST
        if($_SESSION['user_role'] === 'cliente'){    
            
            // Garantisce che "clienti" non possano creare ticket per altri clienti e assegnarli a tecnici di loro sccelta
            $data['tic_user_id'] = $_SESSION['user_id'];

            // Recupera tutti i tecnici a DB
            $admins = User::getUsers(['user_role' => 'tecnico']);                           
            $minTickets = PHP_INT_MAX;                                                      
            $leastBusyList = [];
            
            // Per ogni tecnico calcola il numero di biglietti a loro assegnati e
            // registra quale tecnico ha meno ticket associati in quel momento
            foreach ($admins as $admin) {                                                   
                $count = count(Ticket::getTickets(['tic_tec_id' => $admin['user_id']]));
                if ($count < $minTickets) {
                    $minTickets = $count;
                    $leastBusyList = [$admin];
                } elseif ($count === $minTickets) {
                    $leastBusyList[] = $admin;
                }
            }

            // Sceglie un admin randomicamente in caso di tie
            $leastBusy = $leastBusyList[array_rand($leastBusyList)];
            $data['tic_tec_id'] = $leastBusy['user_id'];
        }

        $id = $dbo->insert(self::$table, $data);
        if(!$id) {
            die("Ticket Non Inserito");
        }
        return $id;
    }

    static function modifyTicket($data)
    {
        global $dbo;
        $ticket = new Ticket($data['tic_id']);

        // Controlla che l'utente loggato, se cliente, sia proprietario del ticket e 
        // annulla l'operazione se il cliente prova a passare tic_user_id o tic_tec_id
        if($_SESSION['user_role']==='cliente'){
            if ($_SESSION['user_id'] != $ticket->data['tic_user_id']){
                die("Utente loggato non è associato al ticket che si intende modificare");
            }
            if (isset($_POST['tic_user_id']) || isset($_POST['tic_tec_id'])) {              
                die("Non autorizzato: l'utente non può modificare il cliente o il tecnico associati");
            }
        }

        $modify = $dbo->update(self::$table, "tic_id", $data['tic_id'], $data);
        if(!$modify) {
            die("Ticket Non Modificato");
        }
        return $modify;
    }

    static function deleteTicket($data)
    {
        global $dbo;
        
        if($_SESSION['user_role']==='cliente'){
            die("Non possiedi l'autorizzazione per accedere a questa area");
        }
        $delete = $dbo->delete(self::$table, "tic_id", $data['tic_id']);
        if(!$delete) {
            die("Ticket Non Eliminato");
        }
        return $delete;
    }

    public function getDatiApi()   
    {
        return $this->record;
    }

    public function addAttachment($data)
    {

        // Metodo creato ad hoc per favorire url più coerenti nell'API (endpoint + "Ticket" invece di endpoint + "Attachment") e
        // per evitare il controllo di sessione dell'omonimo metodo addAttachment nella classe Attachment.
        // Unica operazione inedita è la specifica di att_ticket_id
        global $dbo;
        $data['att_ticket_id'] = $this->tic_id;
        $filename = basename($_FILES['attachment']['name']);
        $target_dir = __DIR__ . "/../uploads/";
        $web_path = "uploads/" . $filename;
        $target_path = $target_dir . $filename;

        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_path)) {

            $data['att_filename'] = $filename;
            $data['att_filepath'] = $web_path;
            $added = $dbo->insert('attachments', $data);
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

}