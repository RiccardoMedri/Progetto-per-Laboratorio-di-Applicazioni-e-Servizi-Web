<?php
    require_once "inc/require.php";

    $clientRole = false;
    $adminRole = false;

    switch ($_SESSION['user_role']) {
        case 'cliente':
            $clientRole = true;
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
        <h2 class="mb-4 text-center text-white">Apri un Nuovo Ticket</h2>

        <form action="opCRUD.php" method="POST" class="bg-light p-4 rounded shadow">
            <input type="hidden" name ="operation" value="insert">
            
            <div class="mb-3">
                <label for="tic_title" class="form-label">Titolo</label>
                <input type="text" class="form-control" id="tic_title" name="tic_title" required>
            </div>

            <div class="mb-3">
                <label for="tic_category" class="form-label">Categoria</label>
                <select class="form-select" id="tic_category" name="tic_category" required>
                    <option value="">Selezione Categoria </option>
                    <option value="Bug">Bug</option>
                    <option value="Richiesta Feature">Richiesta Feature</option>
                    <option value="Supporto">Supporto</option>
                    <option value="Rete">Rete</option>
                    <option value="Software">Software</option>
                    <option value="Hardware">Hardware</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="tic_priority" class="form-label">Priorità</label>
                <select class="form-select" id="tic_priority" name="tic_priority" required>
                    <option value="">Seleziona Priorità</option>
                    <option value="Bassa">Bassa</option>
                    <option value="Media">Media</option>
                    <option value="Alta">Alta</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="tic_description" class="form-label">Descrizione</label>
                <textarea class="form-control" id="tic_description" name="tic_description" rows="4" required></textarea>
            </div>

            <?php if ($adminRole): ?>
                <div class="mb-3">
                    <label for="tic_user_id" class="form-label">Assegna Cliente</label>
                    <select class="form-select" id="tic_user_id" name="tic_user_id" required>
                        <option value="">Seleziona Cliente</option>
                        <?php

                            // Si applica solo she l'utente loggato è un tecnico
                            // Permette al tecnico di selezionare il cliente a cui associare il nuovo biglietto 
                            $clienti = User::getUsers(['user_role' => 'cliente']);
                            foreach ($clienti as $cliente) {
                                echo "<option value='{$cliente['user_id']}'>{$cliente['user_name']}</option>";
                            }

                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="tic_tec_id" class="form-label">Assegna Tecnico</label>
                    <select class="form-select" id="tic_tec_id" name="tic_tec_id" required>
                        <option value="">Seleziona Tecnico</option>
                        <?php

                            // Permette al tecnico di selezionare il tecnico a cui associare il nuovo biglietto 
                            $tecnici = User::getUsers(['user_role' => 'tecnico']);
                            foreach ($tecnici as $tecnico) {
                                echo "<option value='{$tecnico['user_id']}'>{$tecnico['user_name']}</option>";
                            }
                            
                        ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">Create Ticket</button>
                <a href="list.php"><button type="button" class="btn btn-primary">Annulla</button></a>
            </div>
        </form>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2025 Ticketing System. All rights reserved.</p>
    </footer>
</body>
</html>
