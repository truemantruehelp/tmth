CREATE TABLE IF NOT EXISTS tmth_program_requests (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    program ENUM('gp','training') NOT NULL,
    applicant_name VARCHAR(100) NOT NULL,
    phone VARCHAR(16) NOT NULL,
    status ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
    submission_key CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_program_submission (submission_key),
    KEY ix_program_queue (program, status, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
