# PHP User Role Demo App

This is a simple PHP application that demonstrates:

- Dynamic user creation via a form
- Role assignment using a normalized database structure
- Secure password hashing and separation via foreign keys
- Error logging and validation
- Relational `JOIN` to display associated role data

## Features

- Optional form fields for username, role, and password (auto-filled if blank)
- Passwords stored using `password_hash()` for security
- Users linked to roles and passwords via foreign keys
- Results displayed in an HTML table
- Detailed error output and `/var/log/php_app.log` logging

## Installation (Rocky Linux)

1. **Install required packages:**

   ```bash
   sudo dnf install -y httpd php php-mysqlnd
   ```

2. **Start Apache:**

   ```bash
   sudo systemctl enable --now httpd
   ```

3. **Place application files:**

   ```bash
   sudo mkdir -p /var/www/html/php_app
   sudo cp index.php /var/www/html/php_app/
   sudo chown -R apache:apache /var/www/html/php_app
   ```

4. **Create writable log file:**

   ```bash
   sudo touch /var/log/php_app.log
   sudo chown apache:apache /var/log/php_app.log
   sudo chmod 644 /var/log/php_app.log
   ```

## Connecting to the Database

The database connection parameters are defined at the top of `index.php`:

```php
$host = 'your-rds-endpoint.amazonaws.com';
$db   = 'php_app';
$user = 'youruser';
$pass = 'yourpassword';
```

To connect to your own MySQL or Amazon RDS database:

- Replace `your-rds-endpoint.amazonaws.com` with your database host
- Set the correct `$user`, `$pass`, and `$db` values

If the connection fails, a detailed error message will be shown in the browser and logged to `/var/log/php_app.log`.

> ⚠️ Ensure that your database accepts remote connections and that security groups/firewalls permit traffic from your web server.

## Access the Application

Once deployed, open:

```
http://your-server/php_app/
```

Fill in the form and submit. If any fields are blank, the app will auto-generate a random username, role, or password.

## Notes

- This app uses raw PHP + PDO with no frameworks
- The schema may evolve, so it's not described in detail here
- Ensure Apache has read/write access to the log file
- SELinux and firewalld may block access — adjust as needed

## License

MIT — use freely and adapt as needed.

