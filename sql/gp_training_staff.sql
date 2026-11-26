CREATE TABLE IF NOT EXISTS tmth_program_staff (
    user_id INT NOT NULL,
    program ENUM('gp','training') NOT NULL,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_tmth_program_staff_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tmth_program_status_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    request_id BIGINT UNSIGNED NOT NULL,
    changed_by INT NULL,
    previous_status ENUM('new','contacted','closed') NOT NULL,
    current_status ENUM('new','contacted','closed') NOT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY ix_tmth_program_history_request (request_id, id),
    CONSTRAINT fk_tmth_program_history_request FOREIGN KEY (request_id) REFERENCES tmth_program_requests (id) ON DELETE CASCADE,
    CONSTRAINT fk_tmth_program_history_user FOREIGN KEY (changed_by) REFERENCES users (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
