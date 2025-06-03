USE example_db;

CREATE TABLE IF NOT EXISTS usuario(
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

INSERT INTO usuario (username, password) VALUES (
    'alex',
    '$2y$10$1fZVv4Eo6O6SbUjO2ko3je0hP5gYt1XgAH6k9M6AEdA6k2Myj7d7C'
);