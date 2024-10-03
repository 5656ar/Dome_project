<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <title>Registration Page</title>
    <!-- Bootstrap CSS -->
    <link
      href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <style>
      body {
        background-color: #f8f9fa;
      }
      .container {
        margin-top: 50px;
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
    </style>
  </head>
  <body>
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="panel">
            <div class="panel-heading text-center">
              <h1>Registration Form</h1>
            </div>
            <div class="panel-body p-4">
              <form action="register_pull.php" method="post">
                <div class="form-group">
                  <label for="firstName">First Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="firstName"
                    name="firstName"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="lastName">Last Name</label>
                  <input
                    type="text"
                    class="form-control"
                    id="lastName"
                    name="lastName"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="gender">Gender</label>
                  <div>
                    <label for="male" class="radio-inline mr-3">
                      <input
                        type="radio"
                        name="gender"
                        value="m"
                        id="male"
                      />Male
                    </label>
                    <label for="female" class="radio-inline mr-3">
                      <input
                        type="radio"
                        name="gender"
                        value="f"
                        id="female"
                      />Female
                    </label>
                    <label for="others" class="radio-inline">
                      <input
                        type="radio"
                        name="gender"
                        value="o"
                        id="others"
                      />Others
                    </label>
                  </div>
                </div>
                <div class="form-group">
                  <label for="email">Email</label>
                  <input
                    type="email"
                    class="form-control"
                    id="email"
                    name="email"
                    required
                  />
                </div>
                <div class="form-group">
                  <label for="password">Password</label>
                  <input
                    type="password"
                    class="form-control"
                    id="password"
                    name="password"
                    required
                  />
                  <input type="checkbox" id="showPassword" /> Show Password
                </div>
                <div class="form-group">
                  <label for="number">Phone Number</label>
                  <input
                    type="tel"
                    class="form-control"
                    id="number"
                    name="number"
                    required
                  />
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                  Register
                </button>
              </form>
            </div>
            <div class="panel-footer text-center">
              <small>&copy; Technical Babaji</small>
            </div>
          </div>
          <div class="text-center mt-3">
            <p>If you have an account? <a href="login.html">Login here</a></p>
          </div>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
      document.getElementById('showPassword').addEventListener('change', function () {
        var passwordField = document.getElementById('password');
        if (this.checked) {
          passwordField.type = 'text';
        } else {
          passwordField.type = 'password';
        }
      });
    </script>
  </body>
</html>
