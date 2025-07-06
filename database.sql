CREATE TABLE users (
    user_id INT NOT NULL AUTO_INCREMENT,
    user_name VARCHAR(100) NOT NULL,
    user_email VARCHAR(150) UNIQUE NOT NULL,
    user_password VARCHAR(255) NOT NULL,
    user_role ENUM('cliente', 'tecnico') NOT NULL DEFAULT 'cliente',
    user_creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id)
) ENGINE = InnoDB;

CREATE TABLE ticket (
    tic_id INT AUTO_INCREMENT PRIMARY KEY,
    tic_title VARCHAR(255) NOT NULL,
    tic_category TEXT NOT NULL,
    tic_priority ENUM('Bassa', 'Media', 'Alta') DEFAULT 'Bassa', 
    tic_creation_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tic_description TEXT NOT NULL,
    tic_state ENUM('Aperto', 'In lavorazione', 'Chiuso') DEFAULT 'Aperto',
    tic_user_id INT NOT NULL,
    tic_tec_id INT DEFAULT NULL,
    CONSTRAINT fk_ticket_client 
    	FOREIGN KEY (tic_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_ticket_technician
    	FOREIGN KEY (tic_tec_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE = InnoDB;

CREATE TABLE messages (
    mes_id INT AUTO_INCREMENT PRIMARY KEY,
    mes_ticket_id INT NOT NULL,
    mes_author_id INT NOT NULL,
    mes_text TEXT NOT NULL,
    mes_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mes_ticket_id) REFERENCES ticket(tic_id) ON DELETE CASCADE,
    FOREIGN KEY (mes_author_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE attachments (
    att_id INT AUTO_INCREMENT PRIMARY KEY,
    att_ticket_id INT NOT NULL,
    att_filename VARCHAR(255) NOT NULL,
    att_filepath VARCHAR(255) NOT NULL,
    att_upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (att_ticket_id) REFERENCES ticket(tic_id) ON DELETE CASCADE
) ENGINE = InnoDB;

CREATE TABLE user_tokens (
    token_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE = InnoDB;


INSERT INTO users (user_name, user_email, user_password, user_role) VALUES
('Alice Smith', 'alice@example.com', SHA2('password123', 256), 'cliente'),
('Bob Johnson', 'bob@example.com', SHA2('password456', 256), 'cliente'),
('Riccardo Medri', 'fake@email.com', SHA2('123456789', 256), 'tecnico');


INSERT INTO ticket (tic_title, tic_category, tic_priority, tic_description, tic_state, tic_user_id, tic_tec_id) VALUES
('Problema con il WiFi', 'Rete', 'Bassa', 'Il mio WiFi non funziona correttamente.', 'Aperto', 1, 3),
('Errore software', 'Software', 'Bassa', 'L\'applicazione crasha all\'avvio.', 'In lavorazione', 2, 3),
('Stampante non risponde', 'Hardware', 'Bassa', 'La stampante non si connette al PC.', 'Aperto', 1, 3);