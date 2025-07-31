<?php
date_default_timezone_set('UTC');

// Config
$host = 'your-rds-endpoint.amazonaws.com';
$db   = 'php_app';
$user = 'youruser';
$pass = 'yourpassword';
$logFile = '/var/log/php_app.log';

function log_error($message) {
    global $logFile;
    $entry = "[" . date('Y-m-d H:i:s') . "] ERROR: $message\n";
    if (!is_writable($logFile)) {
        echo "<pre style='color:red;'>❌ Cannot write to log file: $logFile</pre>";
        echo "<pre style='color:red;'>$entry</pre>";
        return;
    }
    error_log($entry, 3, $logFile);
    echo "<pre style='color:red;'>$entry</pre>";
}

$roles = [];
$pdo = null;

// Connect to DB and load roles
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $roleStmt = $pdo->query("SELECT id, name FROM roles ORDER BY name ASC");
    $roles = $roleStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    log_error("❌ Failed to connect or load roles: " . $e->getMessage());
}

// Form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    if ($username === '') {
        $username = 'user_' . rand(1000, 9999);
    }

    $roleName = trim($_POST['role_name'] ?? '');
    if ($roleName === '') {
        $randomRole = $roles[array_rand($roles)];
        $roleName = $randomRole['name'];
    }

    $passwordPlain = trim($_POST['password'] ?? '');
    if ($passwordPlain === '') {
        $passwordPlain = 'pw_' . rand(1000, 9999);
    }

    // Find role ID
    $role_id = null;
    foreach ($roles as $r) {
        if (strcasecmp($r['name'], $roleName) === 0) {
            $role_id = $r['id'];
            break;
        }
    }

    if ($role_id === null) {
        log_error("❌ Unknown role: $roleName");
        echo "<pre style='color:red;'>❌ Role '$roleName' not found in roles table.</pre>";
    } else {
        try {
            $pdo->beginTransaction();

            $hash = password_hash($passwordPlain, PASSWORD_DEFAULT);
            $stmtPass = $pdo->prepare("INSERT INTO passwords (hash) VALUES (:hash)");
            $stmtPass->execute([':hash' => $hash]);
            $password_id = $pdo->lastInsertId();

            $stmtUser = $pdo->prepare("
                INSERT INTO users (username, created_at, role_id, password_id)
                VALUES (:username, :created_at, :role_id, :password_id)
            ");
            $stmtUser->execute([
                ':username' => $username,
                ':created_at' => date('Y-m-d H:i:s'),
                ':role_id' => $role_id,
                ':password_id' => $password_id
            ]);

            $pdo->commit();
            echo "<pre>✅ Inserted: <strong>$username</strong> | Role: <strong>$roleName</strong> | Password: <em>(stored securely)</em></pre>";
        } catch (PDOException $e) {
            $pdo->rollBack();
            log_error("❌ Insert failed: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP User App with Roles and Passwords</title>
</head>
<body>
    <h1>Create User</h1>
    <form method="post">
        <label for="username">Username (optional):</label><br>
        <input type="text" name="username" id="username" placeholder="e.g. alice"><br><br>

        <label for="role_name">Role (optional):</label><br>
        <input type="text" name="role_name" id="role_name" placeholder="e.g. admin"><br><br>

        <label for="password">Password (optional):</label><br>
        <input type="text" name="password" id="password" placeholder="e.g. mysecurepw"><br><br>

        <button type="submit">Create User</button>
    </form>

    <hr>
    <h2>Recent Users</h2>
    <?php
    if ($pdo) {
        try {
            $stmt = $pdo->query("
                SELECT u.id, u.username, u.created_at, r.name AS role
                FROM users u
                JOIN roles r ON u.role_id = r.id
                ORDER BY u.created_at DESC
                LIMIT 10
            ");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($users) {
                echo "<table border='1' cellpadding='5'>";
                echo "<tr><th>ID</th><th>Username</th><th>Created At</th><th>Role</th></tr>";
                foreach ($users as $user) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                    echo "<td>" . htmlspecialchars($user['role']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No users found.</p>";
            }
        } catch (PDOException $e) {
            log_error("❌ Failed to list users: " . $e->getMessage());
            echo "<pre style='color:red;'>Error loading users.</pre>";
        }
    }
    ?>
</body>
</html>

