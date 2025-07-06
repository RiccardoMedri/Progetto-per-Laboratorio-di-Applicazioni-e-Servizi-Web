<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>API Test Client</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">
  <div class="container mt-5">
    <a class="navbar-brand" href="../list.php">Back to Homepage</a>
    <h1 class="mb-4">API Test Client</h1>

    <!-- Get Ticket -->
    <div class="card mb-3">
      <div class="card-header">Get Ticket Details</div>
      <div class="card-body">
        <div id="ticketMessage" class="mb-3">
          <div id="ticketError" class="text-danger d-none"></div>
          <div id="ticketSuccess" class="d-none"></div>
        </div>
        <div class="mb-3">
          <label for="ticketId" class="form-label">Ticket ID</label>
          <input type="text" id="ticketId" class="form-control">
        </div>
        <button id="btnLoadTicket" class="btn btn-primary">Load Ticket</button>
        <div class="mt-3" id="ticketDetails">
          <h5 id="ticketTitle"></h5>
          <p id="ticketDesc"></p>
        </div>
      </div>
    </div>

    <!-- Upload Attachment -->
    <div class="card mb-3">
      <div class="card-header">Upload Attachment</div>
      <div class="card-body">
        <div id="ticketMessage" class="mb-3">
          <div id="uploadError"   class="text-danger d-none"></div>
          <div id="uploadSuccess" class="d-none"></div>
        </div>
        <form id="uploadForm">
          <div class="mb-3">
            <label for="att_ticket_id" class="form-label">Ticket ID</label>
            <input type="text" name="att_ticket_id" class="form-control" required>
          </div>
          <div class="mb-3">
            <label for="attachment" class="form-label">Select File</label>
            <input type="file" name="attachment" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-warning">Upload</button>
        </form>
      </div>
    </div>

    <!-- Download Attachment -->
    <div class="card mb-3">
      <div class="card-header">Manage Attachments</div>
      <div class="card-body">
        <div id="ticketMessage" class="mb-3">
          <div id="manageError"   class="text-danger d-none"></div>
          <div id="manageSuccess" class="d-none"></div>
        </div>
        <div class="mb-3">
          <label for="manageTicketId" class="form-label">Ticket ID</label>
          <input type="text" id="manageTicketId" class="form-control">
        </div>
        <button id="btnLoadAttachments" class="btn btn-primary mb-3">Load Attachments</button>
        <ul class="list-group" id="attachmentList"></ul>
        <button id="btnDeleteSelected" class="btn btn-danger mt-3">Delete Selected</button>
      </div>
    </div>

  </div>
  <script src="index.js"></script>
</body>
</html>
