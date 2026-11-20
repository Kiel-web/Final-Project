CREATE TABLE IF NOT EXISTS orders (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL,
    total            DECIMAL(10,2) NOT NULL,
    status           ENUM('pending', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_method   ENUM('cod', 'pickup') NOT NULL DEFAULT 'cod',
    shipping_name    VARCHAR(100) NOT NULL,
    shipping_address VARCHAR(255) NOT NULL,
    phone            VARCHAR(30) NOT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
