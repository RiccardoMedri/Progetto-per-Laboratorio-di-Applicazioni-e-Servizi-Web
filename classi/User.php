<?php
define('PROJECT_ROOT', dirname(__DIR__));
require_once (PROJECT_ROOT . '/inc/require.php');


class User
{
    protected $user_id;
    static protected $table = 'users';
    public $record;
    public $data;

    function __construct($id)
    {
        $this->user_id = $id;
        $this->setRecord();
        $this->setData();
    }

    private function setRecord()
    {
        global $dbo;
        $this->record = $dbo->find(self::$table, 'user_id', $this->user_id);
    }
    private function setData()
    {
        $this->data = $this->record;
        $this->data['user_password'] = "***";
    }

    static function getUser($field, $value)
    {
        global $dbo;
        return $dbo->find(self::$table, $field, $value);
    }

    static function getUsers($conditions = [], $orderBy = '', $columns = null)
    {
        global $dbo;
        return $dbo->findAll(self::$table, $conditions, null, $orderBy, $columns);
    }
}