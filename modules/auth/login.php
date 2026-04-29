<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - KEBANA Digital Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GtvK6y1nZGn9L9k1iKMdDoV6nupN9zL+ZSLR0sZOsY/hyx3D+0DGz1h/6URyhu2M" crossorigin="anonymous">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>KEBANA</h1>
                <p>Digital Management System</p>
            </div>

            <div class="auth-body">
                <!-- Display error/success messages here if needed -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Login Failed!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="authenticate.php">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>

                    <button type="submit" class="btn btn-login">Sign In</button>
                </form>

                <div style="text-align: center; margin-top: 1.5rem;">
                    <a href="#" style="color: #6c757d; font-size: 0.9rem; text-decoration: none;">Forgot password?</a>
                </div>
            </div>

            <div class="auth-footer">
                <p>Don't have an account? <a href="sign_up.php">Create one here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-1cmnv0tY8TnZ+Zp8uYjXY2EFd7qFIzyVgq7e0nxA0x0B8Hv5kD4YuoZsT7QCV5Lu" crossorigin="anonymous"></script>
</body>
</html>
