<?php
session_start();
require_once __DIR__ . '/leadcontroller.php';

$controller = new LeadController();
$message = $controller->login();
$email = trim($_POST['email'] ?? '');
$toast = $_SESSION['auth_toast'] ?? null;
unset($_SESSION['auth_toast']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <?php include __DIR__ . '/../../include/header.php'; ?>

</head>
<body class="login-page d-flex align-items-center py-4 bg-body-tertiary">
    
    <main class="form-signin w-100 m-auto">
        <div class="card login-card border-0 p-4">
            <h2 class="text-center mb-4 text-primary">Login</h2>
            
            <?php if ($message !== '') : ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
           
            <form action="" method="POST" id="loginForm">
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    <span class="field-error" id="emailError"></span>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <span class="field-error" id="passwordError"></span>
                </div>

                <button class="btn btn-primary w-100 py-2 mb-3 rounded-pill" type="submit">Sign In</button>
                <div class="text-center">
                    <p>Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                </div>
            </form>
        </div>
    </main>

    <?php if ($toast && !empty($toast['message'])): ?>
        <div class="toast-container position-fixed top-0 end-0 p-3">
            <div
                id="authToast"
                class="toast text-bg-success border-0"
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
                data-bs-delay="2000">
                <div class="d-flex">
                    <div class="toast-body"><?php echo htmlspecialchars($toast['message']); ?></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    <?php endif; ?>

   
    <?php include __DIR__ . '/../../include/footer.php'; ?>
</body>
</html>
