-- Create the user with password
CREATE USER 'newuser'@'%' IDENTIFIED BY 'StrongPassword123!';

-- Grant all privileges only on a specific database (e.g., 'my_database')
GRANT ALL PRIVILEGES ON my_database.* TO 'newuser'@'%';

-- Apply the changes (optional but good practice)
FLUSH PRIVILEGES;
