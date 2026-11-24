<!DOCTYPE html>
<html lang="en">
<head>
    <title>RandomPlay</title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width-device-width, initial-scale=1.0">
    <link rel="stylesheet" type = "text/css" href="style.css?v=1.0.8">
</head>
<body>
    <?php require_once "blocks/header.php"; ?>
    <div class="container hero-contacts">
        <h1>Our offices</h1>
        <p>Our offices are located all over the world, so our support service is available 24/7</p>
        <img src="assets/images/snapedit_1728419691541.jpeg" alt="">
    </div>
    <div class="feedback">
        <div class="container">
            <h2>Say hello</h2>
            <p>We are always ready for complaints and suggestions</p>
            <form>
                <div class="inline">
                    <div>
                        <label>First Name</label>
                        <input type="">
                    </div>
                    <div>
                        <label>Last Name</label>
                        <input type="">
                    </div>
                </div>
                <label>Email Address</label>
                <input type="email" id="smsField" class="one-line">
                <label>Message</label>
                <textarea class="one-line"></textarea>
                <button type="button" onclick="checksms()">Get in touch</button>
            </form>
        </div>
    </div>
    <?php require_once "blocks/footer.php";?>
    <script>
        function checksms(){
            let email = document.querySelector('#smsField').value;
            if(!email.includes('@')) alert('Нет символа @'); 
            else if(!email.includes('.')) alert('Нет символа .'); 
            else alert('Thanks for your message')
        }
    </script>
    <script src="perehod.js"></script>
</body>
</html> 
        