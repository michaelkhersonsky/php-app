CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    created_at DATETIME NOT NULL,
    role_id INT NOT NULL,
    password_id INT NOT NULL,
    FOREIGN KEY (role_id) REFERENCES roles(id),
    FOREIGN KEY (password_id) REFERENCES passwords(id)
);

