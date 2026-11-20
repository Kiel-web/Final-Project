CREATE TABLE IF NOT EXISTS pets (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name         VARCHAR(80) NOT NULL,
    breed_id     VARCHAR(10) NOT NULL,
    breed_name   VARCHAR(100) NOT NULL,
    age_months   SMALLINT UNSIGNED NOT NULL,
    gender       ENUM('male', 'female') NOT NULL,
    color        VARCHAR(50) NOT NULL,
    description  TEXT NOT NULL,
    adoption_fee DECIMAL(8,2) NOT NULL DEFAULT 0,
    status       ENUM('available', 'pending', 'adopted') NOT NULL DEFAULT 'available',
    image_url    VARCHAR(255) NULL,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
