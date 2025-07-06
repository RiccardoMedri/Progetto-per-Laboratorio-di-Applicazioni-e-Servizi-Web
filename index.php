<?php
    $form = $_GET['form'] ?? '';
    $showLogin = ($form === 'login');
    $showRegister = ($form === 'register');
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ticketing System</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="scss/custom.scss" rel="stylesheet">
    </head>
    <body class="bg-dark text-white">
        <nav class="navbar navbar-expand-lg navbar-dark bg-secondary shadow">
            <div class="container">
                <a class="navbar-brand fs-4 fw-bold" href="index.php">Ticketing System</a>
            </div>
        </nav>
        <section class="d-flex flex-column align-items-center justify-content-center text-center" id="hero-section">
            <h1 class="display-4 fw-bold">Welcome to Ticketing System</h1>
            <p class="lead">Manage your tickets efficiently and hassle-free.</p>
            <div class="mt-4">
                <a href="?form=login" class="btn btn-primary btn-lg me-2">Login</a>
                <a href="?form=register" class="btn btn-lg btn-info">Register</a>
            </div>
        </section>
        <section class="container my-4">
            <div class="row justify-content-center">
                <!-- LOGIN FORM -->
                <div class="col-md-6 form-container <?= $showLogin ? 'd-block' : 'd-none' ?>">
                    <h2 class="text-center">Login</h2>
                    <form action="opCRUD.php" method="POST">
                        <input type="hidden" name="operation" value="login">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="user_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="user_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Login</button>
                    </form>
                </div>

                <!-- REGISTER FORM -->
                <div class="col-md-6 form-container <?= $showRegister ? 'd-block' : 'd-none' ?>">
                    <h2 class="text-center">Register</h2>
                    <form action="opCRUD.php" method="POST">
                        <input type="hidden" name="operation" value="register">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="user_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="user_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="user_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Register</button>
                    </form>
                </div>
            </div>
        </section>
        <footer class="bg-dark text-white text-center py-3 mt-auto">
            <p class="mb-0">&copy; 2025 Ticketing System. All rights reserved.</p>
        </footer>
    </body>
</html>