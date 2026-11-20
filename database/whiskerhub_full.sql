CREATE DATABASE IF NOT EXISTS whiskerhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE whiskerhub;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
    id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    icon VARCHAR(10) NOT NULL DEFAULT '🐾'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS products (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id  INT UNSIGNED NOT NULL,
    name         VARCHAR(150) NOT NULL,
    description  TEXT NOT NULL,
    price        DECIMAL(10,2) NOT NULL,
    stock        INT UNSIGNED NOT NULL DEFAULT 0,
    image_url    VARCHAR(255) NULL,
    is_featured  TINYINT(1) NOT NULL DEFAULT 0,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

CREATE TABLE IF NOT EXISTS order_items (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id     INT UNSIGNED NOT NULL,
    product_id   INT UNSIGNED NULL,
    product_name VARCHAR(150) NOT NULL,
    unit_price   DECIMAL(10,2) NOT NULL,
    quantity     INT UNSIGNED NOT NULL,
    CONSTRAINT fk_items_order   FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cache (
    cache_key  VARCHAR(120) NOT NULL PRIMARY KEY,
    payload    MEDIUMTEXT NOT NULL,
    expires_at INT UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO users (id, name, email, password_hash, role) VALUES
(1, 'Shop Admin',    'admin@whiskerhub.test',    '$2y$10$WIyu/hmkztwhAzAmjPpi4ezYgyuMVmQ.IhTjrFmk9v9/5qruiurDi', 'admin'),
(2, 'Demo Customer', 'customer@whiskerhub.test', '$2y$10$buxTBLHgvkqk5OiTBgMzG.Vt.AdiOaUIR.7kGOJ6lDtvmqoxga.8G', 'customer');

INSERT INTO categories (id, name, icon) VALUES
(1, 'Cat Food',        '🍗'),
(2, 'Toys',            '🧸'),
(3, 'Beds & Furniture','🛏️'),
(4, 'Litter',          '🚽'),
(5, 'Grooming',        '🧴'),
(6, 'Accessories',     '🎀');

INSERT INTO products (category_id, name, description, price, stock, is_featured) VALUES
(1, 'Salmon Delight Dry Food 2kg',      'Crunchy kibble made with real salmon. Supports a shiny coat and healthy digestion.', 899.00, 40, 1),
(1, 'Tuna Pate Cans (12-pack)',         'Soft tuna pate in easy-open cans. A favourite for picky eaters.', 720.00, 25, 0),
(1, 'Kitten Growth Formula 1.5kg',      'High-protein recipe with extra calcium for growing kittens up to 12 months.', 780.00, 18, 0),
(1, 'Grain-Free Chicken Recipe 3kg',    'Grain-free dry food with chicken and sweet potato for sensitive tummies.', 1350.00, 12, 1),
(2, 'Feather Wand Teaser',              'Extendable wand with colourful feathers. Perfect for daily play sessions.', 149.00, 60, 1),
(2, 'Catnip Mouse Set (6 pcs)',         'Six soft mice filled with organic catnip. Great for solo play.', 189.00, 35, 0),
(2, 'Crinkle Play Tunnel',              'Foldable 120 cm tunnel with a crinkly sound that cats love to hide and pounce in.', 349.00, 14, 0),
(2, 'Laser Pointer Pen',                'Safe low-power laser pointer to keep your cat active and entertained.', 99.00, 50, 0),
(2, 'Track Ball Tower',                 'Three-level ball track toy that keeps curious paws busy.', 279.00, 0, 0),
(3, 'Cozy Cloud Cat Bed',               'Ultra-soft round bed with raised edges for a secure, warm nap.', 699.00, 20, 1),
(3, 'Window Perch Hammock',             'Strong suction cups attach it to any window so your cat can watch the world.', 549.00, 9, 0),
(3, 'Cardboard Scratcher Lounge',       'Recycled cardboard scratcher that doubles as a lounge. Protects your sofa!', 329.00, 22, 0),
(4, 'Clumping Clay Litter 10kg',        'Dust-free, fast-clumping litter with odour control.', 399.00, 45, 0),
(4, 'Hooded Litter Box',                'Large hooded box with charcoal filter and easy-clean tray.', 849.00, 8, 0),
(4, 'Stainless Steel Litter Scoop',     'Sturdy steel scoop with a comfortable non-slip handle.', 89.00, 3, 0),
(5, 'Slicker Grooming Brush',           'Removes loose fur and tangles. Self-cleaning button.', 229.00, 30, 0),
(5, 'Safety Nail Clipper',              'Curved blade with safety guard for stress-free nail trims.', 169.00, 28, 0),
(5, 'Gentle Oatmeal Cat Shampoo',       'Soap-free shampoo with oatmeal for soft, itch-free skin.', 249.00, 16, 0),
(6, 'Breakaway Collar with Bell',       'Safety-release collar in assorted colours, adjustable 20-30 cm.', 129.00, 55, 0),
(6, 'Travel Carrier',                   'Airline-friendly carrier with top and front doors and a soft washable pad.', 999.00, 7, 1),
(6, 'Cat Harness & Leash Set',          'Escape-proof vest harness with reflective strip and 1.5 m leash.', 359.00, 15, 0),
(6, 'Ceramic Food Bowl',                'Heavy, anti-slip ceramic bowl. Dishwasher safe.', 199.00, 33, 0);

INSERT INTO pets (name, breed_id, breed_name, age_months, gender, color, description, adoption_fee, status) VALUES
('Mochi',    'siam', 'Siamese',          14, 'female', 'Cream with seal points', 'Chatty and affectionate. Mochi follows you from room to room and loves to curl up on laps.', 1500.00, 'available'),
('Leo',      'mcoo', 'Maine Coon',       30, 'male',   'Brown tabby',            'A gentle giant who gets along with kids and dogs. Loves water games and gentle brushing.', 2000.00, 'available'),
('Cleo',     'pers', 'Persian',          48, 'female', 'White',                  'Calm and quiet lady who enjoys sunny windows. Needs regular brushing.', 1800.00, 'available'),
('Simba',    'beng', 'Bengal',           18, 'male',   'Golden spotted',         'Very energetic and clever. Simba needs lots of climbing space and play time.', 2500.00, 'available'),
('Willow',   'ragd', 'Ragdoll',          10, 'female', 'Blue bicolor',           'A sweet, floppy kitten who goes limp in your arms. Perfect for first-time owners.', 2000.00, 'available'),
('Biscuit',  'sfol', 'Scottish Fold',     8, 'male',   'Cream',                  'Playful kitten with tiny folded ears. Already litter trained and vaccinated.', 2200.00, 'available'),
('Smokey',   'rblu', 'Russian Blue',     36, 'male',   'Silver-gray',            'Shy at first but incredibly loyal. Does best in a quiet home.', 1500.00, 'available'),
('Nala',     'bsho', 'British Shorthair',24, 'female', 'Blue-gray',              'Easy-going cat with a round face and plush coat. Application currently being reviewed.', 1800.00, 'pending'),
('Tiger',    'sibe', 'Siberian',          6, 'male',   'Orange tabby',           'Fluffy kitten full of curiosity. Siberians are often better for people with mild allergies.', 2000.00, 'available'),
('Pharaoh',  'sphy', 'Sphynx',           20, 'male',   'Pink',                   'Warm-loving, hairless cuddler. Needs a weekly bath and a sweater in winter.', 3000.00, 'available'),
('Ginger',   'abys', 'Abyssinian',       12, 'female', 'Ruddy',                  'Athletic and adventurous. Ginger loves to climb to the highest shelf.', 1800.00, 'available'),
('Snowball', 'birm', 'Birman',           60, 'female', 'Seal point',             'Gentle senior lady who found her forever home last month.', 1500.00, 'adopted');

INSERT INTO inquiries (pet_id, user_id, name, email, phone, message, status) VALUES
(8, 2, 'Demo Customer', 'customer@whiskerhub.test', '555-0101', 'Hi! I live in an apartment with a balcony. Is Nala good with other cats?', 'contacted');
