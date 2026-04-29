<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - KEBANA Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GtvK6y1nZGn9L9k1iKMdDoV6nupN9zL+ZSLR0sZOsY/hyx3D+0DGz1h/6URyhu2M" crossorigin="anonymous">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <h1>KEBANA</h1>
                <p>Create Your Account</p>
            </div>

            <div class="auth-body">
                <!-- Display error/success messages here if needed -->
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Registration Failed!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php">
                    <div class="form-group">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Choose a username" required>
                        <small class="form-text">3-50 characters, letters and numbers only</small>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                        <small class="form-text">We'll never share your email with anyone else.</small>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Create a strong password" required>
                        <small class="form-text">Must be at least 8 characters long.</small>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                    </div>

                    <div class="form-group">
                        <label for="role" class="form-label">Account Role</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Select your role</option>
                            <option value="Super Admin">Super Admin</option>
                            <option value="Secretary">Secretary</option>
                            <option value="Treasurer">Treasurer</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" style="color: #0d6efd; text-decoration: none;">Terms and Conditions</a>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-signup">Create Account</button>
                </form>
            </div>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Sign in here</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-1cmnv0tY8TnZ+Zp8uYjXY2EFd7qFIzyVgq7e0nxA0x0B8Hv5kD4YuoZsT7QCV5Lu" crossorigin="anonymous"></script>
</body>
</html>
