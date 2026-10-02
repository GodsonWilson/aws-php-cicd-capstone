
<?php
$dbServer = getenv('DB_SERVER') ?: 'mysql';
$dbUsername = getenv('DB_USERNAME') ?: 'appuser';
$dbPassword = getenv('DB_PASSWORD') ?: '';
$dbName = getenv('DB_NAME') ?: 'employee_db';

$conn = new mysqli($dbServer, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    http_response_code(500);
    exit('Database connection failed. Check the database service and configuration.');
}

$conn->query("
    CREATE TABLE IF NOT EXISTS employees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL
    )
");

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if ($name !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $conn->prepare(
            'INSERT INTO employees (name, email) VALUES (?, ?)'
        );
        $stmt->bind_param('ss', $name, $email);

        if ($stmt->execute()) {
            $message = 'Employee added successfully.';
        } else {
            $message = 'Could not add employee.';
        }

        $stmt->close();
    } else {
        $message = 'Enter a name and a valid email address.';
    }
}

$result = $conn->query('SELECT id, name, email FROM employees ORDER BY id DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Management</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 40px auto; padding: 0 16px; background: #f4f6f8; }
        h1 { color: #183153; }
        form, .panel { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        input, button { box-sizing: border-box; width: 100%; padding: 10px; margin: 8px 0; }
        button { background: #183153; color: white; border: 0; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; background: white; }
        th, td { padding: 10px; border-bottom: 1px solid #ddd; text-align: left; }
        .message { padding: 10px; background: #e8f5e9; }
    </style>
</head>
<body>
    <h1>Employee Management</h1>
    <p>PHP application running on Kubernetes with MySQL.</p>

    <?php if ($message !== ''): ?>
        <p class="message"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <form method="post">
        <h2>Add Employee</h2>
        <label for="name">Name</label>
        <input id="name" name="name" required maxlength="100">

        <label for="email">Email</label>
        <input id="email" name="email" type="email" required maxlength="150">

        <button type="submit">Add Employee</button>
    </form>

    <div class="panel">
        <h2>Employees</h2>
        <table>
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th></tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= (int) $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php $conn->close(); ?>
