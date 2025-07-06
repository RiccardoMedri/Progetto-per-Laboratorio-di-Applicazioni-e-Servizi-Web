<?php
    require_once "inc/require.php";

    if (!isset($_GET['tic_id'])) {
        die("Ticket non specificato");
    }
    
    $ticket = new Ticket($_GET['tic_id']);

    // Il seguente switch si assicura che un cliente non possa cambiare il ticket di qualcun altro
    $adminRole = false;

    switch ($_SESSION['user_role']) {                                           
        case 'cliente':
            if ($_SESSION['user_id'] != $ticket->data['tic_user_id']) {
                die("Utente loggato non è associato al ticket che si intende modificare");
            }
            break;
        case 'tecnico':
            $adminRole = true;
            break;
        default:
            die("Ruolo utente non riconosciuto");
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticketing System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="scss/custom.css" rel="stylesheet">
</head>
<body class="bg-dark">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="list.php">Ticketing System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="opCRUD.php?operation=logout">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2 class="mb-4 text-center text-white">Modifica Ticket</h2>
        <form action="opCRUD.php" method="POST" class="bg-light p-4 rounded shadow">
            <input type="hidden" name="operation" value="modify">
            <input type="hidden" name="tic_id" value="<?php echo $ticket->data['tic_id'];?>"> 
            <div class="mb-3">
                <label for="tic_title" class="form-label">Titolo</label>
                <input type="text" class="form-control" id="tic_title" name="tic_title" value="<?php echo $ticket->record['tic_title'];?>" required>
            </div>
            <div class="mb-3">
                <label for="tic_category" class="form-label">Categoria</label>
                <select class="form-select" id="tic_category" name="tic_category" required>
                    <option value="">Selezione Categoria </option>
                    <?php
                        //these and all others select must be valdiates in the backend i.e. the database otherwise any passed value will be accepted
                        //La differenza con le "select" delle altre pagine è che qui vengono preselezionati i valori associati con il biglietto da modificare
                        $categories = ["Bug", "Richiesta Feature", "Supporto", "Rete", "Software", "Hardware"];
                        foreach ($categories as $category) {
                            $selected = ($ticket->record['tic_category'] == $category) ? 'selected' : '';
                            echo "<option value='$category' $selected>$category</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="tic_priority" class="form-label">Priorità</label>
                <select class="form-select" id="tic_priority" name="tic_priority" required>
                    <option value="">Seleziona Priorità</option>
                    <?php
                        $priorities = ["Bassa", "Media", "Alta"];
                        foreach ($priorities as $priority) {
                            $selected = ($ticket->record['tic_priority'] == $priority) ? 'selected' : '';
                            echo "<option value='$priority' $selected>$priority</option>";
                        }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="tic_description" class="form-label">Descrizione</label>
                <textarea class="form-control" id="tic_description" name="tic_description" rows="4" required><?php echo $ticket->record['tic_description'];?></textarea>
            </div>

            <?php if ($adminRole): ?>
                <div class="mb-3">
                    <label for="tic_state" class="form-label">Cambia Stato</label>
                    <select class="form-select" id="tic_state" name="tic_state">
                        <?php
                            $states = ["Aperto", "In Lavorazione", "Chiuso"];
                            foreach ($states as $state) {
                                $selected = ($ticket->record['tic_state'] == $state) ? 'selected' : '';
                                echo "<option value='$state' $selected>$state</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tic_user_id" class="form-label">Cambia Cliente</label>
                    <select class="form-select" id="tic_user_id" name="tic_user_id">
                        <?php

                            // Si applica solo se l'utente loggato è tecnico
                            // Permette al tecnico di selezionare i clienti e/o tecnici a cui associare il biglietto                                                                                 
                            $clienti = User::getUsers(['user_role' => 'cliente']); 
                            foreach ($clienti as $cliente) {
                                $selected = ($ticket->record['tic_user_id'] == $cliente['user_id']) ? 'selected' : '';
                                echo "<option value='{$cliente['user_id']}' $selected>{$cliente['user_name']}</option>";
                            }                            
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tic_tec_id" class="form-label">Cambia Tecnico</label>
                    <select class="form-select" id="tic_tec_id" name="tic_tec_id">
                        <?php
                            $tecnici = User::getUsers(['user_role' => 'tecnico']);
                            foreach ($tecnici as $tecnico) {
                                $selected = ($ticket->record['tic_tec_id'] == $tecnico['user_id']) ? 'selected' : '';
                                echo "<option value='{$tecnico['user_id']}' $selected>{$tecnico['user_name']}</option>";
                            }                            
                        ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Modifica Ticket</button>
                <a href="list.php"><button type="button" class="btn btn-primary">Annulla</button></a>
            </div>
        </form>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2025 Ticketing System. All rights reserved.</p>
    </footer>
</body>
</html>
