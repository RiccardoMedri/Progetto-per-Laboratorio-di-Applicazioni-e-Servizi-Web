<?php
    require_once "inc/require.php";

    if($_SESSION['user_role']==="cliente"){
        die("Non possiedi l'autorizzazione per accedere a questa area");
    }

    if (!isset($_GET['tic_id'])) {
        die("Ticket non specificato");
    }

    $ticket = new Ticket($_GET['tic_id']);
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
        <h2 class="mb-4 text-center text-white">Elimina Ticket</h2>
        <div class="card bg-secondary text-white mb-4 shadow">
            <div class="card-header text-center">
                <h5 class="mb-0">Dettagli Ticket</h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">ID:</dt>
                    <dd class="col-sm-8"><?php echo $ticket->record['tic_id']; ?></dd>

                    <dt class="col-sm-4">Titolo:</dt>
                    <dd class="col-sm-8"><?php echo $ticket->record['tic_title']; ?></dd>

                    <dt class="col-sm-4">Categoria:</dt>
                    <dd class="col-sm-8"><?php echo $ticket->record['tic_category']; ?></dd>

                    <dt class="col-sm-4">Priorità:</dt>
                    <dd class="col-sm-8"><?php echo $ticket->record['tic_priority']; ?></dd>

                    <dt class="col-sm-4">Data Creazione:</dt>
                    <dd class="col-sm-8"><?php echo $ticket->record['tic_creation_date']; ?></dd>

                    <dt class="col-sm-4">Descrizione:</dt>
                    <dd class="col-sm-8"><?php echo $ticket->record['tic_description']; ?></dd>

                    <dt class="col-sm-4">Stato:</dt>
                    <dd class="col-sm-8"><?php echo $ticket->record['tic_state']; ?></dd>
                </dl>
            </div>
        </div>
        <form action="opCRUD.php" method="POST" class="text-center">
            <input type="hidden" name="operation" value="delete">
            <input type="hidden" name="tic_id" value="<?php echo $ticket->record['tic_id']; ?>">
            <button type="submit" class="btn btn-danger">Elimina</button>
            <a href="list.php" class="btn btn-secondary">Annulla</a>
        </form>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2025 Ticketing System. All rights reserved.</p>
    </footer>
</body>
</html>