<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 100px;
        }
        .panel {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .panel-heading {
            background-color: #007bff;
            color: white;
            padding: 15px;
            border-radius: 15px 15px 0 0;
        }
        .panel-footer {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 0 0 15px 15px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .alert {
            display: none;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="#">Your Website</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="search_rooms.php">Search Rooms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="panel">
                    <div class="panel-heading text-center">
                        <h2>Login</h2>
                    </div>
                    <div class="panel-body p-4">
                        <!-- Alert divs for notifications -->
                        <div class="alert alert-danger" id="error-alert">Invalid email or password.</div>
                        <div class="alert alert-success" id="sull-alert">Registration successful!</div>

                        <form action="login_check.php" method="post">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required />
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required />
                                <input type="checkbox" id="showPassword" /> Show Password
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </form>
                    </div>
                    <div class="panel-footer text-center">
                        <small>&copy; Technical Babaji</small>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- JavaScript for showing password and alert -->
    <script>
        document.getElementById('showPassword').addEventListener('change', function () {
            var passwordField = document.getElementById('password');
            if (this.checked) {
                passwordField.type = 'text';
            } else {
                passwordField.type = 'password';
            }
        });

        // Check for session errors
        <?php
        session_start();
        if (isset($_SESSION['login_error'])) {
            echo "document.getElementById('error-alert').style.display = 'block';";
            unset($_SESSION['login_error']); // Clear error after displaying
        }
        if (isset($_SESSION['register_success'])) {
            echo "document.getElementById('sull-alert').style.display = 'block';";
            unset($_SESSION['register_success']); // Clear success after displaying
        }
        ?>
    </script>
</body>
</html>
