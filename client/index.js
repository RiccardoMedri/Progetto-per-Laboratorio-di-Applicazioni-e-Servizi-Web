const APIKEY = "qwertyuiopasdfghjklzxcvbnm1234567890!?";
const endpoint = "https://progetto.cesenahome.com:24577/server/api/";
//const endpoint = "http://localhost/server/api/";

// Ad ogni caricamento svuota i campi del form
$('input[type="text"]').val('');
$('#uploadForm')[0].reset();

// Funzioni di Utility per le API
async function apiGet(url) {
  const response = await fetch(url, {
    method: "GET",
    headers: {
      "Content-Type": "application/json",
      APIKEY: APIKEY,
    },
  });
  return response.json();
}

async function apiPost(url, formData) {
  const response = await fetch(url, {
    method: "POST",
    headers: {
      APIKEY: APIKEY, // Esplcitiamo solo la API key, lasciamo definire il content type al browser
    },
    body: formData,
  });

  return response.text(); // Restituisce testo così posso gestire anche risposte "plain"
}

// Predisposta ma non usata
async function apiPut(url, data = {}) {
  const response = await fetch(url, {
    method: "PUT",
    headers: {
      "Content-Type": "application/json",
      APIKEY: APIKEY,
    },
    body: JSON.stringify(data),
  });
  return response.json();
}

async function apiDelete(url) {
  const response = await fetch(url, {
    method: "DELETE",
    headers: { 
      "Content-Type": "application/json",
      APIKEY: APIKEY 
    }
  });
  return response.json();
}


// Binding degli eventi
$(function () {

  // Get per informazioni su un biglietto
  $("#btnLoadTicket").click(async function (e) {
    e.preventDefault();

    $("#ticketError, #ticketSuccess").addClass("d-none").text(""); 
    
    // Trim per rimuovere spazi vuoti
    const ticketId = $("#ticketId").val().trim();

    // Se il campo è vuoto non esegue alcuna query 
    if (!ticketId) {                              
      $("#ticketError").removeClass("d-none").text("Inserisci un Ticket ID.");
      
      // svuota i campi di informazionni per query errate
      $("#ticketTitle, #ticketDesc").text("");
      return;
    }

    try {
      const data = await apiGet(endpoint + "Ticket/" + ticketId);
      if (data.errore) {
        $("#ticketError").removeClass("d-none").text(data.errore);
        $("#ticketId").val('');
        $("#ticketTitle, #ticketDesc").text("");
        return;
      }
      $("#ticketTitle").text("Titolo: " + data.tic_title);
      $("#ticketDesc").text("Descrizione: " + data.tic_description);
      $("#ticketSuccess").removeClass("d-none").text("Ticket caricato con successo.");
    } catch (err) {
        $("#ticketError").removeClass("d-none").text("Errore nella chiamata API.");
    }
  });


  // POST per caricare nuovi allegati
  $("#uploadForm").submit(async function (e) {
    e.preventDefault();

    $("#uploadError, #uploadSuccess").addClass("d-none").text("");

    // FormData è una classe JS fornita dal DOM Living Standard
    // permette di costruire facilmente un set di coppie chiave/valore
    // da inviare usando fetch() o XMLHttpRequest, facilità specialmente il submit di form che includono file
    const formData = new FormData(this); 
    const ticketId = formData.get("att_ticket_id");

    try {
      const text = await apiPost(endpoint + "Ticket/" + ticketId, formData);
      let result;
      try {
        result = JSON.parse(text);
      } catch {
        throw new Error(text);
      }
      if (result.errore) {
        $("#uploadError").removeClass("d-none").text(result.errore);
        $('#uploadForm')[0].reset();
      } else if (result.success) {
        $("#uploadSuccess").removeClass("d-none").text(`Upload riuscito: ${result.att_filename}`);
        $('#uploadForm')[0].reset();
      } else {
        $("#uploadError").removeClass("d-none").text("Risposta non prevista: " + text);
      }
    } catch (err) {
      $("#uploadError").removeClass("d-none").text("Errore durante l'upload");
    }
  });
  

  // Carica gli allegati di un ticket
  $("#btnLoadAttachments").click(async function (e) {
    e.preventDefault();
    const ticketId = $("#manageTicketId").val();
    $("#manageError, #manageSuccess").addClass("d-none").text("");

    if (!ticketId) { 
      $("#manageError").removeClass("d-none").text("Inserisci un Ticket ID.");
      $("#attachmentList").empty();
      return;
    }

    try {
      const attachments = await apiGet(endpoint + "Ticket/" + ticketId + "/attachments");
      const list = $("#attachmentList");
      list.empty();
      if (!attachments.length) {
        $("#manageError").removeClass("d-none").text("Nessun allegato trovato.");
        $("#manageTicketId").val('');
        $("#attachmentList").empty();
        return;
      }
      attachments.forEach(att => {
        var url = `../urlAccess.php?file=${encodeURIComponent(att.att_filename)}&tic_id=${ticketId}`;
        list.append(  
          `<li class="list-group-item d-flex align-items-center">
             <input type="checkbox" class="form-check-input me-2" value="${att.att_id}">
             <a href="${url}" target="_blank">${att.att_filename}</a>
             <span class="ms-auto text-muted">${att.att_upload_date}</span>
           </li>`
        );
      });
      $("#manageSuccess").removeClass("d-none").text("Allegati caricati con successo.");
    } catch (err) {
      $("#manageError").removeClass("d-none").text("Errore caricamento allegati");
      $("#attachmentList").empty();
    }
  });

    
  // Cancella gli allegati selezionati
  $("#btnDeleteSelected").click(async function () {
    $("#manageError, #manageSuccess").addClass("d-none").text("");
    const selected = $("#attachmentList input:checked").map(function () { return this.value; }).get();
    if (!selected.length) {
      $("#manageError").removeClass("d-none").text("Seleziona almeno un allegato da eliminare.");
      return;
    }
    if (!confirm("Sei sicuro di eliminare gli allegati selezionati?")) return;
    try {
      for (const id of selected) {
        await apiDelete(endpoint + "Attachment/" + id);
      }
      $("#manageSuccess").removeClass("d-none").text("Allegati eliminati con successo.");
      $("#btnLoadAttachments").click();
    } catch (err) {
      $("#manageError").removeClass("d-none").text("Errore durante l'eliminazione");
      $("#attachmentList").empty();
    }
  });

});