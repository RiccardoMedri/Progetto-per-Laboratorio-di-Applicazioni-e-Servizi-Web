<?php
    require_once "inc/require.php";

    // Di seguito controlli su:
    // - presenza di ticket id nella richiesta
    // - autorizzazione per accesso a ticket e allegati

    if (!isset($_GET['tic_id'])) {
        die("Ticket non specificato");
    }

    $ticket = new Ticket($_GET['tic_id']);
    
    if($_SESSION['user_role'] === "cliente" && $_SESSION['user_id'] != $ticket->record['tic_user_id']){
        die("Utente loggato non è proprietario del ticket associato a questa conversazione");
    }

    $attachments = Attachment::getAttachments(['att_ticket_id' => $ticket->record['tic_id']], "ORDER BY att_upload_date DESC");
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
            <h2 class="mb-4 text-center text-white">Chat del Ticket</h2>
            <div id="chat-body" class="bg-light p-3 rounded shadow">

            </div>

            <form id="message-form" action="opCRUD.php" method="POST" class="mt-3">
                <input type="hidden" name="operation" value="insertMessage">
                <input type="hidden" name="mes_ticket_id" value="<?php echo $ticket->record['tic_id']; ?>">
                <div class="input-group">
                    <textarea name="mes_text" class="form-control" placeholder="Scrivi un messaggio..." required></textarea>
                    <button type="submit" class="btn btn-primary">Invia</button>
                </div>
            </form>

            <div class="row mt-4 align-items-start">
                <div class="col-md-6">
                    <form action="opCRUD.php" method="POST" class="mt-3" enctype="multipart/form-data">
                        <input type="hidden" name="operation" value="insertAttachment">
                        <input type="hidden" name="att_ticket_id" value="<?php echo $ticket->record['tic_id']; ?>">
                        <div class="input-group">
                            <input type="file" name="attachment" id="attachment" class="form-control" required>
                            <button type="submit" class="btn btn-warning">Carica</button>
                        </div>
                    </form>
                </div>

                <div class="col-md-6">
                    <h6 class="text-white">Allegati:</h6>
                    <ul class="list-group">
                        <?php foreach ($attachments as $att): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="urlAccess.php?file=<?php echo urlencode($att['att_filename']);?>&tic_id=<?php echo $ticket->record['tic_id'];?>" target="_blank">
                                    <?php echo htmlspecialchars($att['att_filename']); ?>
                                </a>
                                <small class="text-muted"><?php echo date("d/m/Y H:i", strtotime($att['att_upload_date'])); ?></small>
                            </li>
                        <?php endforeach; ?>
                        <?php if (count($attachments) === 0): ?>
                            <li class="list-group-item text-muted">Nessun allegato</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
        <footer class="bg-dark text-white text-center py-3 mt-5">
            <p class="mb-0">&copy; 2025 Ticketing System. All rights reserved.</p>
        </footer>
    </body>

    <!-- Viene importata la libreria JQuery per facilitare la gestione dei messaggi -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        const USER_ID = <?php echo json_encode($_SESSION['user_id']); ?>;
        const TICKET_ID = <?php echo json_encode($ticket->record['tic_id']); ?>;
    </script>
    <script src="chat/chat.js"></script> 
</html>