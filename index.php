
<?php 
include("config.php");

// Get the _fbc value from the fbclid query parameter
function get_fbc_from_fbclid() {
  $fbclid = isset($_GET['fbclid']) ? $_GET['fbclid'] : '';
  if (!empty($fbclid)) {
    return 'fb.1.' . time() . '.' . $fbclid;
  }
  return '';
}

$fbc_from_fbclid = get_fbc_from_fbclid();
$site_url = url();
// Facebook Conversion API - InitiateCheckout Event with additional data using cURL

// Replace with your access token and pixel ID
$accessToken    = FB_ACCESS_TOKEN;
$pixelId        = PIXEL_ID;
$paypalClientId = PAYPAL_CLIENT_ID;
$stripe_publishable_key = STRIPE_PUBLISHABLE_KEY;
// Purchase details (replace with actual purchase data)
$currency    = 'EUR';
$contentIds  = ['PROD123'];
$contentName = 'FORMULA COURSE CREATOR';
$contentType = 'product';
$numItems    = 1;
$contents    = [
  [
      'id' => 'PROD123',
      'quantity' => 1,
      'item_price' => 100,
  ],
];

// User details (replace with actual user data)
$fbp = isset($_COOKIE['_fbp']) ? $_COOKIE['_fbp'] : '';
//$fbc = isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '';
$fbc = !empty($fbc_from_fbclid) ? $fbc_from_fbclid : (isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '');

// Event and external ID (replace with unique identifiers)
$eventId = 'EVENT12345';

// Event source URL (replace with the actual URL)
$eventSourceUrl = $site_url;

// Event details
$eventData = [
  'data' => [
      [
          'event_name' => 'InitiateCheckout',
          'event_time' => time(),
          'event_id'   => $eventId,
          'user_data'  => [
              'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
              'client_ip_address' => $_SERVER['REMOTE_ADDR'],
              'fbp'               => $fbp,
              'fbc'               => $fbc,
          ],
          'custom_data' => [
              'currency'     => $currency,
              'content_ids'  => $contentIds,
              'content_name' => $contentName,
              'content_type' => $contentType,
              'contents'     => $contents,
              'num_items'    => $numItems,
          ],
          'event_source_url' => $eventSourceUrl,
          'action_source'    => 'website',
      ],
  ],
];

// Send event data to Facebook Conversion API using cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/v12.0/$pixelId/events?access_token=$accessToken");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($eventData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  'Content-Type: application/json',
]);

$response = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

// Check for errors and display the result
if ($error) {
  echo "cURL Error: " . $error;
} else {
  echo "Response from Facebook Conversion API: " . $response;
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Checkout Page</title>
    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

  </head>
  <body>
    
    <div class="container mt-5">
   
      <div class="col-md-12">
    
        <div class="row">
        
          <div class="col-md-6">
            <!-- Stripe Form -->
            <img src="http://e.missionedigitale.com/image/book.png" style="width:300px;height:300px;"></img>
            <h1 class="checkout-content-title" kjb-settings-id="offer-title-section">FORMULA COURSE CREATOR</h1>
            <p class="product_price">€100 EUR</p>
          </div>
          <div class="col-md-6">
          <form  action="ssc.php"  id="paymentform"  method="post">
            
              <div class="form-group">
                <label for="email">Email</label>
                 <input type="hidden" name="product_name" value="FORMULA COURSE CREATOR">
                 <input type="hidden" name="product_price" value="100">
                <input type="email" class="form-control" id="email"  name="email" required>
              </div>
              <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name"  name="name" required>
              </div>
              <div class="form-group">
                <label for="surname">Surname</label>
                <input type="text" class="form-control" id="surname" name="surname" required>
              </div>
              <div class="form-group">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_type" id="flexRadioDefault1" value="stripe" checked>
                    <label class="form-check-label" for="flexRadioDefault1">
                      Credit Card
                    </label>
                  </div>
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="payment_type" id="flexRadioDefault2" value="paypal">
                    <label class="form-check-label" for="flexRadioDefault2">
                      PayPal
                    </label>
                  </div>
              </div>
              <div class="form-group">
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="terms" name="terms" required>
                 
                  <label class="form-check-label" for="terms">
                    Agree to terms and conditions
                  </label>
                </div>
              </div>
              <div class="form-group" id="stripe_card">
                <label for="card-element">Credit or debit card</label>
                <div id="card-element">
                  <!-- A Stripe Element will be inserted here. -->
                </div>
                <!-- Used to display form errors. -->
                <div id="card-errors" role="alert"></div>
              </div>
              <button type="submit" class="btn btn-primary stripe" name="stripe" value="stripe"  id="stripe">Complete the order</button>
              <!-- <button class="btn btn-warning stripe" id="paypal" value="payment"><img src="http://e.missionedigitale.com/image/paypal_image.svg"></img></button> -->
             <div class="col-md-6" id="paypal_button"> 
        <!-- PayPal Button -->
          <div id="paypal-button-container"></div>
        </div>
              <!-- <div class="col-md-6"> <div id="paypal-button-container" class="paypal"></div></div> -->
            </div>
            </form>
          </div>
          
      
        </div>
      </div>
    </div>
    <!-- Include Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>

    <!-- Include PayPal JS -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalClientId;?>&disable-funding=credit,card"></script>

    <!-- Initialize Stripe -->
    <script>


    $(document).ready(function(){
      
        $('#paypal_button').hide();

        $("input[type='radio']").click(function(){
            var radioValue = $("input[name='payment_type']:checked").val();
            if(radioValue == "stripe"){
              $('#stripe_card').show();
              $('#paypal_button').hide();
              $('#stripe').show();
            }
            if(radioValue == "paypal"){
              $('#stripe_card').hide();
              $('#paypal_button').show();
              $('#stripe').hide();
            }
        });
   
      var stripe   = Stripe('<?= $stripe_publishable_key ?>');
      var elements = stripe.elements();

      // Custom styling can be passed to options when creating an Element.
      var style = {
        base: {
          // Add your base input styles here. For example:
          fontSize : '16px',
          color    : '#32325d',
        },
      };

      // Create an instance of the card Element.
      var card = elements.create('card', {style: style});
     
      // Add an instance of the card Element into the `card-element` <div>.
      card.mount('#card-element');
        
      // Handle real-time validation errors from the card Element.
      card.on('change', function(event) {
        console.log(event,'eventevent')
        var displayError = document.getElementById('card-errors');
        if (event.error) {
          displayError.textContent = event.error.message;
        } else {
          displayError.textContent = '';
        }
      });
   // Handle form submission.
   var form = document.getElementById('paymentform');
      form.addEventListener('submit', function(event) {
        event.preventDefault();

        stripe.createToken(card).then(function(result) {
          if (result.error) {
            // Inform the user if there was an error.
            var errorElement = document.getElementById('card-errors');
            errorElement.textContent = result.error.message;
          } 
          else {
            // Send the token to your server.
            stripeTokenHandler(result.token);
          }
        });
      });

      // Submit the form with the token ID.
      function stripeTokenHandler(token) {
        // Insert the token ID into the form so it gets submitted to the server
        var form = document.getElementById('paymentform');
        var hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);

        console.log('token',token.id);

        // Submit the form
        form.submit();
       
      }

     // Handle form submission.
     
   
    paypal.Buttons({

       
        createOrder: function(data, actions) {
          // Set up the transaction
          return actions.order.create({
            purchase_units: [{
              amount: {
                currency: 'EUR',
                value: '0.01'
              },
              description: 'FORMULA COURSE CREATOR'
            }]
          });
        },
        onApprove: function(data, actions) {
          // Capture the funds from the transaction
          return actions.order.capture().then(function(details) {
            // Check if the payment was approved or canceled
            if (data.status === 'CANCELLED') {
              // Redirect the user to the cancel URL
              window.location.href = 'paypal_cancel.php';
            } else {
              
          
              // Show a success message to the buyer
              var status         = details.status;
              var order_id       = details.id;
              var currency_code  = details.purchase_units[0].amount.currency_code;
              var item_name      = details.purchase_units[0].description;
              var amount         = details.purchase_units[0].amount.value;
              var payPal_name    = details.payer.name.given_name;
              var payPal_surname = details.payer.name.surname;
              var payPal_email   = details.payer.email_address;
              var payment_type = 'paypal';
              var user_name =  $('#name').val();
              var user_surname =  $('#surname').val();
              var user_email =  $('#email').val();
              
              //console.log('user_email',user_email);
              
              $.ajax({
                      url: "ssc.php",
                      type: "POST",
                      data: {item_name:item_name,currency_code:currency_code,payPal_name : payPal_name,order_id:order_id,amount:amount, payPal_surname: payPal_surname, payPal_email: payPal_email,status: status,payment_type:payment_type,user_name:user_name,user_surname:user_surname,user_email:user_email},
                      success: function (response) {
                         console.log('response',response); 
                         window.location.href = 'success.php';
                      },
                      
              });
            }

          });
        },

        onError: function(err) {
          // Handle any errors that occur during the transaction
          console.log(err);
          $.ajax({
          url: "ssc.php",
          type: "POST",
          data: {item_name:item_name,currency_code:currency_code,payPal_name : payPal_name,order_id:order_id,amount:amount, payPal_surname: payPal_surname, payPal_email: payPal_email,status: status,payment_type:payment_type,user_name:user_name,user_surname:user_surname,user_email:user_email},
          success: function (response) {
          console.log('response',response);
          window.location.href = 'paypal_cancel.php';
          },

          });
        },
        onCancel: function(data) {
          // Handle the cancellation of the transaction
          console.log('Transaction cancelled', data);
          $.ajax({
          url: "ssc.php",
          type: "POST",
          data: {item_name:item_name,currency_code:currency_code,payPal_name : payPal_name,order_id:order_id,amount:amount, payPal_surname: payPal_surname, payPal_email: payPal_email,status: status,payment_type:payment_type,user_name:user_name,user_surname:user_surname,user_email:user_email},
          success: function (response) {
            console.log('response',response);
            window.location.href = 'paypal_cancel.php';
          },

          });
        }
      }).render('#paypal-button-container'); // Display PayPal button

     
  });

    </script>
  </body>
</html>
