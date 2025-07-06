// injected dalla pagina ticketChat
const userId = USER_ID;
const ticketId = TICKET_ID;
let initialLoad = true;

function fetchMessages() {

    // $.get() è un abbreviativo di jQuery per una richiesta AJAX GET
    $.get('chat/chatEndpoint.php', { tic_id: ticketId }, function(data) {
        const chatBody = $('#chat-body');
        chatBody.html('');
        data.forEach(msg => {
            const align = msg.author_id === userId ? 'text-end' : 'text-start';
            const bgColor = msg.author_id === userId ? 'bg-primary text-white' : 'bg-secondary text-white';

            chatBody.append(`
                <div class="mb-2 ${align}">
                    <div class="chat-message d-inline-block px-3 py-2 rounded ${bgColor}">
                        ${msg.text.replace(/\n/g, "<br>")}
                        <div><small class='text-light'>${msg.date}</small></div>
                    </div>
                </div>
            `);
        });

        // Al caricamento della pagina, scorre in fondo alla chat
        if (initialLoad) {
            chatBody.scrollTop(chatBody[0].scrollHeight);
            initialLoad = false;
        }
    }, 'json');
}

// Esegue la funzione di recupero (e display) dei messaggi ogni 3 secondi
fetchMessages();
setInterval(fetchMessages, 3000);

// Gestisce l'invio di nuovi messaggi tramite AJAX
$('#message-form').on('submit', function(e) {

    // Evita il ricaricamento della pagina
    e.preventDefault();

    // Converte il contenuto del form in una stringa URL-encoded nel formato chiave=valore
    const $form = $(this);
    const formData = $form.serialize();

    // Metodo JQuery per eseguire richieste HTTP asincrone
    // Ottiene sia url della pagina che metodo della richiesta dal form
    // Dopo aver eseguito con successo la richiesta, esegue la fetch dei messaggi
    $.ajax({
        url: $form.attr('action'),
        method: $form.attr('method'),
        data: formData,
        success: function() {
            fetchMessages(); // Refresh chat
            $form.find('textarea').val(''); // Svuota input
        },
        error: function() {
            alert("Errore durante l'invio del messaggio.");
        }
    });
});
