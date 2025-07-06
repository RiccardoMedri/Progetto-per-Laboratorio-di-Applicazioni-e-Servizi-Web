<?php
define('PROJECT_ROOT', dirname(__DIR__));
require_once (PROJECT_ROOT . '/inc/require.php');


class Utils
{

    static function loggato()
    {
        return (!empty($_SESSION['user_id']));
    }

    public static function login($data)
        {
        global $dbo;

        // Sanifica e recupera l'input dell'user
        $user_email = $data['user_email'];
        $user_password = hash('sha256', $data['user_password']);

        // Controlla se i campi richiesti siano vuoti
        if (empty($user_email) || empty($user_password)) {
            $_SESSION['login_error'] = 'I campi Email or Password non possono essere vuoti';
            header('Location: index.php?form=login');
            exit;
        }

        $user = User::getUser('user_email', $user_email);

        // Controlla se l'utente esiste e se la password corrisponde
        if ($user && $user['user_password']==$user_password) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_name'] = $user['user_name'];
            $_SESSION['user_email'] = $user['user_email'];
            $_SESSION['user_role'] = $user['user_role'];
        } else {
            $_SESSION['login_error'] = ' Email or Password non valide';
            header('Location: index.php?form=login');
            exit;
        }
    }

    static function logout()
    {
        $_SESSION['user_id'] = "";
    }

    public static function register($data) {
        global $dbo;

        $user_name = $data['user_name'];
        $user_email = $data['user_email'];
        $user_password = hash('sha256', $data['user_password']);

        // Controlla se user sta forzando manualmente il campo user_role
        // In questo form, solo "cliente" è ammesso
        if (isset($data['user_role']) && $data['user_role'] !== 'cliente') {
            $_SESSION['register_error'] = 'Invalid role specified.';
            header('Location: index.php?form=register');
            exit;
        }        

        if (empty($user_name) || empty($user_email) || empty($user_password)) {
            $_SESSION['register_error'] = 'All fields are required.';
            header('Location: index.php?form=register');
            exit;
        }

        // Controlla se utente sia già esistente 
        $existingUser = User::getUser('user_email', $user_email);
        if ($existingUser) {
            $_SESSION['register_error'] = 'Email is already registered.';
            header('Location: index.php?form=register');
            exit;
        }

        $params = [
            'user_name' => $user_name,
            'user_email' => $user_email,
            'user_password' => $user_password
        ];
        
        $dbo->insert('users', $params);

        // Effettua login automatico
        self::login($data);
    }

    public static function formatDateTime($datetimeStr) 
    {
        setlocale(LC_TIME, 'it_IT.UTF-8');
        $date = new DateTime($datetimeStr);
        return strftime('%e %B %Y, %H:%M', $date->getTimestamp());
    }
    

}

?>