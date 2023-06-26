<?php 
$servername = "34.22.232.228";
$username = "sapna";
$password = "Fitness30";
$dbname = "missionedigitale";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


define("FB_ACCESS_TOKEN", "EAAIbDURZAiW0BABaNlQGDUxrniMNDht0jgOwd1Y6QIGLgFDeLe7CPFLlCQNQrwqLJip9l2ZCZB7imMZC4D5uYNZBPXPQPcdBuZApWMvszqTyTtcRVdoragXNwFNT39swY1LQB4oPr3GZBl7ne99nYJp4Ar4qK958nozyAnniVWlMODM4toqaIA5");
define("PIXEL_ID", "1857126894625043");
define("STRIPE_SECRET_KEY", "sk_test_51LpY0ICUtnPLbipZRoBuTyTCCM9pnXHplnWiWvKq2kUlAomzaaDRH4sjz10j00pDjym1gV5gyYzbKto2zCNOc2lc00Aq3JjJwa");
define("STRIPE_PUBLISHABLE_KEY","pk_test_51LpY0ICUtnPLbipZTCYz7UmRDkmwrfSfXNNOc8Gh1KXvpbeaOFyeYT6x9804CnZOUSG4baTarWgUw6DAdqXMVKqU009wC0AzCC");
define("PIXEL_ID", "1857126894625043");
define("STRIPE_CURRENCY", "EUR");
define("PAYPAL_CLIENT_ID", "AXs1mHhuAaa5qFu7b_XrwVJOPlaUUeReaS3MuE6j7ZeTsfZNWUbk6siLbi6Juz994E_bzei6DoWZ9r87");

function url(){
    return sprintf(
      "%s://%s%s",
      isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
      $_SERVER['SERVER_NAME'],
      $_SERVER['REQUEST_URI']
    );
}
  
 