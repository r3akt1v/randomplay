<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay - Register</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css?v=1.0.8">
</head>
<body>
    <div class="loginpassword">
        <form method="post" action="/lib/register.php">
            <h1>Register</h1>
            <div class="input-box">
                <input type="text" name="login" placeholder="Login" required> 
            </div>
            <div class="input-box">
                <input type="text" name="username" placeholder="Name" required>
            </div>
            <div class="input-box">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn">Register</button>
            <div class="register-link">
                <p>Already have an account? <a href="/login.php">Login</a>
                <a href="/index.php">Home</a></p>
            </div>
        </form>
    </div>
    <script src="perehod.js"></script>
    <?php if (isset($_GET['err']) && $_GET['err'] === 'reserved'): ?>
    <script>
        (function () {
            alert('Логин admin1 зарезервирован. Укажите другой логин.');
            if (history.replaceState) {
                history.replaceState(null, '', '/register.php');
            }
        })();
    </script>
    <?php endif; ?>
</body>
</html>