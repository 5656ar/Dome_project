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
        <a class="navbar-brand" href="login_admin.php">The Brick Place </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="search_rooms.php">Search Rooms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index2.php">Dashboard</a>
                </li>
                <?php if (isset($_SESSION['userId'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>

    <div class="modal fade" id="registerModal" tabindex="-1" role="dialog" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">Registration Form</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="register_pull.php" method="post">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="firstName" required />
                        </div>
                        <div class="form-group">
                            <label for="lastName">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="lastName" required />
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <div>
                                <label for="male" class="radio-inline mr-3">
                                    <input type="radio" name="gender" value="m" id="male" required /> Male
                                </label>
                                <label for="female" class="radio-inline mr-3">
                                    <input type="radio" name="gender" value="f" id="female" required /> Female
                                </label>
                                <label for="others" class="radio-inline">
                                    <input type="radio" name="gender" value="o" id="others" required /> Others
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required />
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required />
                            <input type="checkbox" id="showPassword" /> Show Password
                        </div>
                        <div class="form-group">
                            <label for="number">Phone Number</label>
                            <input type="tel" class="form-control" id="number" name="number" required />
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Register</button>
                    </form>
                </div>
               
            </div>
        </div>
    </div>

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
                                
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-block">Login</button>
                        </form>
                    </div>
                    <div class="panel-footer text-center">
                        <small>&copy; Bunnapon takumwan</small>
                    </div>
                </div>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="#" data-toggle="modal" data-target="#registerModal">Register</a></p>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script> -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <!-- JavaScript for showing password and alert -->
    <script>
        
        // PHP session check for errors
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
