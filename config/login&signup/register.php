<?php
session_start();
require_once __DIR__ . '/leadcontroller.php';

$lead = new LeadController();
$result = [];
$name = '';
$email = '';
$phone = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';

    $result = $lead->register($name, $email, $phone, $password);

    if (isset($result['success'])) {
        $_SESSION['auth_toast'] = [
            'type' => 'success',
            'message' => $result['success'],
        ];

        header('Location: login.php');
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Blog</title>

    <?php include __DIR__ . '/../../include/header.php'; ?>

</head>

<body class="register-page">

    <div class="container">
        <div class="card register-card border-0">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <h2 class="fw-bold">Create Account</h2>
                    <p class="text-muted">Join our blog community today</p>
                </div>

                <?php if (isset($result['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($result['error']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="registerForm">
                    <div class="mb-3">
                        <label>Name:</label>
                        <input type="text" class="form-control form-control-lg" id="name" name="name" value="<?= htmlspecialchars($name); ?>">
                        <span class="text-danger" id="nameError"><?= htmlspecialchars($result['name'] ?? ''); ?></span>
                    </div>

                    <div class="mb-3">
                        <label>Email:</label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email" value="<?= htmlspecialchars($email); ?>">
                        <span class="text-danger" id="emailError"><?= htmlspecialchars($result['email'] ?? ''); ?></span>
                    </div>

                    <div class="mb-3">
                        <label>Phone:</label>
                        <input type="text" class="form-control form-control-lg" id="phone" name="phone" value="<?= htmlspecialchars($phone); ?>">
                        <span class="text-danger" id="phoneError"><?= htmlspecialchars($result['phone'] ?? ''); ?></span>
                    </div>

                    <div class="mb-4">
                        <label>Password:</label>
                        <input type="password" class="form-control form-control-lg" id="password" name="password">
                        <span class="text-danger" id="passwordError"><?= htmlspecialchars($result['password'] ?? ''); ?></span>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary btn-lg">Register</button>
                    </div>
                </form>

                <div class="text-center mt-4">
                    <p class="mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-semibold">Log in</a></p>
                </div>
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../../include/footer.php'; ?>
</body>

</html>
