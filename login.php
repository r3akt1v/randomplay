<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width-device-width, initial-scale=1.0">
    <link rel="stylesheet" type = "text/css" href="style.css?v=1.0.8">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="loginpassword">
        <form method="post" action="/lib/login.php">
            <h1>Login</h1>
            <div class="input-box">
                <input type="text" name="login" placeholder="Login" required> 
                <i class='bx bx-user'></i>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
                <i class='bx bx-lock-alt' ></i> 
            </div>
            <div class="remember">
                <label for=""><input type="checkbox">Remember me</label>
                <a href="/index.php">Home</a>
            </div>
            <button type="submit" class="btn">Login</button>
            <div class="register-link">
                <p>Don't have an account? <a href="/register.php">Register</a></p>
            </div>
        </form>
    </div>
    <script src="perehod.js"></script>
</body>
</html>