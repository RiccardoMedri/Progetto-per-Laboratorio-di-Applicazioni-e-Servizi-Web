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
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <div>
                <a class="navbar-brand" href="#">Ticketing System</a>
            </div>
            <div id="navbarNav">
                <a class="nav-link" href="client">API Test</a>
            </div>
            <div id="navbarNav">
                <a class="nav-link" href="opCRUD.php?operation=logout">Logout</a>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <h2 class="mb-4 text-center text-white">Tickets List</h2>

        <form method="GET" class="mb-4 text-white">
            <div class="row g-2 justify-content-center">
                <div class="col-md-2">
                    <input type="text" name="title" class="form-control" placeholder="Title" value="<?= $_GET['title'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <input type="text" name="category" class="form-control" placeholder="Category" value="<?= $_GET['category'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <input type="text" name="priority" class="form-control" placeholder="Priority" value="<?= $_GET['priority'] ?? '' ?>">
                </div>
                <div class="col-md-2">
                    <input type="text" name="state" class="form-control" placeholder="State" value="<?= $_GET['state'] ?? '' ?>">
                </div>
                <?php if ($adminRole): ?>
                    <div class="col-md-2">
                        <input type="text" name="user_id" class="form-control" placeholder="Client ID" value="<?= $_GET['user_id'] ?? '' ?>">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="tec_id" class="form-control" placeholder="Technician ID" value="<?= $_GET['tec_id'] ?? '' ?>">
                    </div>
                <?php endif; ?>
                <div class="col-md-2 mx-auto">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </div>
        </form>
        <div class="row my-4 justify-content-center">
        <div class="col-auto">
            <a href="createTicket.php">
            <button class="btn btn-success">Crea Nuovo Ticket</button>
            </a>
        </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Priority</th>
                        <th>Created On</th>
                        <th>Description</th>
                        <th>State</th>
                        <?php 
                            if($clientRole){
                                echo "<th>Tecnico Assegnato</th>";
                            }

                            if($adminRole){
                                echo "<th>Client ID</th>";
                                echo "<th>Tec ID</th>";
                            }
                        ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php                     
                        $where = "1=1";
                        $params = [];


                        // Se l'utente loggato è un cliente, mostra solo i ticket a lui/lei associati
                        if ($clientRole) {
                            $where .= " AND tic_user_id = :user_id";                // Aggiunge una condizione per matchare il proprio tic_user_id
                            $params['user_id'] = $_SESSION['user_id'];              // Il valore del param user_Iid è presso dalla sessione corrente
                        }

                        // Applica i filtri
                        $filters = ['title' => 'tic_title', 'category' => 'tic_category', 'priority' => 'tic_priority', 'state' => 'tic_state'];
                        foreach ($filters as $getParam => $dbColumn) {
                            if (!empty($_GET[$getParam])) {                         // Controlla la presenza di un parametro di query nel URL
                                $where .= " AND $dbColumn LIKE :$getParam";         // Aggiunge una LIKE condition per il matching parziale
                                $params[$getParam] = '%' . $_GET[$getParam] . '%';
                            }
                        }

                        // Filtri addizionali per i tecnici
                        if ($adminRole) {
                            if (!empty($_GET['user_id'])) {
                                $where .= " AND tic_user_id = :user_id";
                                $params['user_id'] = $_GET['user_id'];
                            }
                            if (!empty($_GET['tec_id'])) {
                                $where .= " AND tic_tec_id = :tec_id";
                                $params['tec_id'] = $_GET['tec_id'];
                            }
                        }

                        // Ottiene i tickets
                        $tickets = Ticket::getTicketsByUsers("AND $where", $params);

                        // Gestisce il display dei tickets con, alcuni valori sono omessi per i non tecnici 
                        foreach ($tickets as $ticket) {
                            $o = new Ticket($ticket['tic_id']);
                            echo "<tr>";
                            echo "<td>" . $o->data['tic_id'] . "</td>";
                            echo "<td>" . $o->data['tic_title'] . "</td>";
                            echo "<td>" . $o->data['tic_category'] . "</td>";
                            echo "<td>" . $o->data['tic_priority'] . "</td>";
                            echo "<td>" . $o->data['tic_creation_date'] . "</td>";
                            echo "<td>" . $o->data['tic_description'] . "</td>";
                            echo "<td>" . $o->data['tic_state'] . "</td>";

                            if($clientRole){
                                $nomeTecnico = new User($o->data['tic_tec_id']);
                                echo "<td>" . $nomeTecnico->data['user_name'];
                            }

                            if($adminRole){
                                echo "<td>" . $o->data['tic_user_id'] . "</td>";
                                echo "<td>" . $o->data['tic_tec_id'] . "</td>";
                            }
                                echo "<td>";
                            ?>
                            
                            <a href="modifyTicket.php?tic_id=<?php echo $ticket['tic_id'];?>" title="Modifica">Modifica</a>
                            <a href="ticketChat.php?tic_id=<?php echo $ticket['tic_id'];?>" title="Modifica">Chat</a>
                            <?php if($adminRole): ?>
                                <a href="deleteTicket.php?tic_id=<?php echo $ticket['tic_id'];?>" title="Elimina">Elimina</a>
                            <?php endif; ?>

                            <?php
                                echo "</td>";
                                echo "</tr>";
                        }
                        ?>
                </tbody>
            </table>
        </div>
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p class="mb-0">&copy; 2025 Ticketing System. All rights reserved.</p>
    </footer>
</body>
</html>
