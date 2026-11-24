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
    <div class="wrapper">
        <?php require_once "blocks/header.php"; ?>
        <div class="container news">
            <h2>News with Random Play</h2>
            <div class="blocks">
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>New release!</h3>
                    <p>"Star Wars: Episode IX" is now available on our website! Buy it now and get free access to exclusive content.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>Discount of the week!</h3>
                    <p>Buy the movie "Avatar" with a 20% discount until the end of the week! This is your chance to purchase this blockbuster at a great price.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>New interview!</h3>
                    <p>We spoke with the director of Parasite about his creative process. Find out what inspired him to create this Oscar-winning film.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>Movie of the month!</h3>
                    <p>"Joker" is our movie of the month! Buy it now and get 15% off plus free access to exclusive content.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>News from the world of cinema!</h3>
                    <p>Warner Bros. announced a new part of the Harry Potter franchise! Expect new adventures in the world of magic.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>Special offer!</h3>
                    <p>Buy Game of Thrones and get free access to exclusive content, including interviews with the cast and creators of the series.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>New trailer!</h3>
                    <p>Watch the new trailer for Top Gun: Maverick right now! Find out what awaits you in this new blockbuster.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>Movie of the year!</h3>
                    <p>"1917" is our film of the year! Buy it now and get 20% off plus free access to exclusive content.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>News from the world of cinema!</h3>
                    <p>Disney has announced a new part of the Star Wars franchise! Expect new adventures in the galaxy.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>Collection discount!</h3>
                    <p>Buy the Lord of the Rings movie collection with a 30% discount! This is your chance to purchase the entire trilogy at a great price.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>New release!</h3>
                    <p>"Sonic at the Movies" is now available on our website! Buy it now and get free access to exclusive content.</p>
                </div>
                <div class="block">
                    <span class="badge white">News</span>
                    <h3>Movie of the week!</h3>
                    <p>"Pirates of the Caribbean" is our movie of the week! Buy it now and get 15% off plus free access to exclusive content.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="container weather">
        <h3>And news about weather</h3>
        <h3>Always good to know</h3>
        <h3>Weather in St. Petersburg</h3>
        <p id="weather-info">Loading weather...</p>
    </div>
    <?php require_once "blocks/footer.php";?>
    <script src="perehod.js"></script>
    <script src="weather.js"></script>
</body>
</html> 