CREATE TABLE IF NOT EXISTS tmth_gp_encounters (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED NOT NULL,
    doctor_id BIGINT UNSIGNED NOT NULL,
    doctor_name VARCHAR(150) NOT NULL,
    registration_number VARCHAR(60) NULL,
    patient_name VARCHAR(100) NOT NULL,
    birth_date DATE NULL,
    history TEXT NULL,
    assessment TEXT NOT NULL,
    recommendations TEXT NOT NULL,
    prescription TEXT NULL,
    follow_up DATE NULL,
    submission_key CHAR(64) CHARACTER SET ascii NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY ix_gp_encounters (request_id, id),
    FOREIGN KEY (request_id) REFERENCES tmth_program_requests(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tmth_training_applications (
    request_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    email VARCHAR(254) NULL,
    locality VARCHAR(120) NOT NULL,
    education VARCHAR(120) NOT NULL,
    experience VARCHAR(32) NOT NULL,
    availability VARCHAR(120) NOT NULL,
    motivation TEXT NOT NULL,
    FOREIGN KEY (request_id) REFERENCES tmth_program_requests(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tmth_training_reviews (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    request_id BIGINT UNSIGNED NOT NULL,
    reviewer_id BIGINT UNSIGNED NOT NULL,
    decision ENUM('under_review','waitlisted','accepted','declined','completed') NOT NULL,
    batch VARCHAR(120) NULL,
    notes TEXT NULL,
    submission_key CHAR(64) CHARACTER SET ascii NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY ix_training_reviews (request_id, id),
    FOREIGN KEY (request_id) REFERENCES tmth_program_requests(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
