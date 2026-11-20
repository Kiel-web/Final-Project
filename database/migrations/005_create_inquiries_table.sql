CREATE TABLE IF NOT EXISTS inquiries (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pet_id     INT UNSIGNED NOT NULL,
    user_id    INT UNSIGNED NULL,
    name       VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    phone      VARCHAR(30) NULL,
    message    TEXT NOT NULL,
    status     ENUM('new', 'contacted', 'closed') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_inquiries_pet  FOREIGN KEY (pet_id)  REFERENCES pets(id)  ON DELETE CASCADE,
    CONSTRAINT fk_inquiries_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
