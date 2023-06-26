<?php

  include 'config.php';

  $payment_type = $_POST['payment_type'];
  $site_url = url();

if($payment_type== 'stripe'){
    
    // Stripe API
   
    $stripe_secret_key = STRIPE_SECRET_KEY;

    $stripe_token    = $_POST['stripeToken'];
    $stripe_email    = $_POST['email'];
    $stripe_name     = $_POST['name'];
    $stripe_surname  = $_POST['surname'];
    $product_name    = $_POST['product_name'];
    $stripe_amount   = $_POST['product_price'];
    $stripe_currency = STRIPE_CURRENCY;
    
    mysqli_query($conn, "INSERT INTO users (name, surname, email , created_at, updated_at)VALUES ('$stripe_name', '$stripe_surname', '$stripe_email', NOW(), NOW())"); 
    $lastid = mysqli_insert_id($conn);

    if($lastid){

      mysqli_query($conn, "INSERT INTO `order` (order_id,user_id,payment_type, card_num, card_cvc,exp_month,exp_year,item_name,item_price,currency,status,created_at, updated_at) VALUES('','$lastid','$payment_type', '', '','','','$product_name','$stripe_amount','$stripe_currency','',NOW(), NOW())");
    }


    $stripe_url  = 'https://api.stripe.com/v1/charges';
    $stripe_data = array(
      'amount'      => $stripe_amount,
      'currency'    => $stripe_currency,
      'source'      => $stripe_token,
      'description' => $product_name
    );

    $stripe_headers = array(
      'Authorization: Bearer ' . $stripe_secret_key,
      'Content-Type: application/x-www-form-urlencoded'
    );

    $stripe_ch = curl_init($stripe_url);
    curl_setopt($stripe_ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($stripe_ch, CURLOPT_HTTPHEADER, $stripe_headers);
    curl_setopt($stripe_ch, CURLOPT_POST, 1);
    curl_setopt($stripe_ch, CURLOPT_POSTFIELDS, http_build_query($stripe_data));
    $stripe_response = curl_exec($stripe_ch);
    $stripe_result   = json_decode($stripe_response);

    // Handle Stripe Response
    $stripe_result = json_decode($stripe_response);
   
  

    if ($stripe_result->status == 'succeeded') {
      // Handle successful payment
        
          $status         = $stripe_result->status;
          $card_exp_year  = $stripe_result->source->exp_year;
          $card_exp_month = $stripe_result->source->exp_month;
          $card_number    = $stripe_result->source->last4;
          $amount         = $stripe_result->amount;
          $item_name      = $stripe_result->description;
          $currency       = $stripe_result->currency;
          $card_cvc       = $stripe_result->source->cvc_check;
          $order_id       = $stripe_result->id;
          $date = date('Y-m-d H:i:s');
          
          if($lastid){

            $sql = "UPDATE `order` set order_id='".$order_id."',card_num='".$card_number."',card_cvc='".$card_cvc."',exp_month='".$card_exp_month."',exp_year='".$card_exp_year."',status='".$status."',updated_at=NOW() WHERE user_id='".$lastid."'";
            if (mysqli_query($conn, $sql)) {
              // echo "Record updated successfully";
            } else {
              echo "Error updating record: " . mysqli_error($conn);
            }
          }

          //For Kajabi Code
          $url = 'https://checkout.kajabi.com/webhooks/offers/TLKwTEaWXoD5nrvD/2148730028/activate';
          $data = array('name' => $stripe_name.' '. $stripe_surname,'email' => $stripe_email);

          $ch = curl_init($url);
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
          curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
          curl_exec($ch);

          if(curl_errno($ch)) {
            echo 'Error: ' . curl_error($ch);
          } 
          else {
            echo 'Success: ' . $result;
          }

        //Facebook conversion API START

          // Get the _fbc value from the fbclid query parameter
          function get_fbc_from_fbclid() {
            $fbclid = isset($_GET['fbclid']) ? $_GET['fbclid'] : '';
            if (!empty($fbclid)) {
                return 'fb.1.' . time() . '.' . $fbclid;
            }
            return '';
          }

          $fbc_from_fbclid = get_fbc_from_fbclid();

          // Facebook Conversion API - Purchase Event with additional data using cURL

          // Replace with your access token and pixel ID
          $accessToken = FB_ACCESS_TOKEN;
          $pixelId = PIXEL_ID;


          // Purchase details (replace with actual purchase data)
          $currency    = $currency;
          $value       = $amount;
          $orderId     = $order_id;
          $contentIds  = ['PROD123'];
          $contentName = $item_name;
          $contentType = 'product';
          $numItems    = 1;
          $contents    = [
                [
                    'id'         => 'PROD123',
                    'quantity'   => 1,
                    'item_price' => $amount,
                ],
          ];


          // User details (replace with actual user data)
          
          $firstName = $stripe_name;
          $lastName  = $stripe_surname;
          $email     = $stripe_email;
          $fbp       = isset($_COOKIE['_fbp']) ? $_COOKIE['_fbp'] : '';
          //$fbc = isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '';
          $fbc = !empty($fbc_from_fbclid) ? $fbc_from_fbclid : (isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '');

          // Event and external ID (replace with unique identifiers)
          $eventId = 'EVENT12345';
          $externalId = $lastid;

          // Event source URL (replace with the actual URL)
          $eventSourceUrl = $site_url;

          // Event details
          $eventData = [
            'data' => [
                [
                    'event_name'  => 'Purchase',
                    'event_time'  => time(),
                    'event_id'    => $eventId,
                    'user_data'   => [
                        'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'client_ip_address' => $_SERVER['REMOTE_ADDR'],
                        'em'                => hash('sha256', strtolower($email)),
                        'fn'                => hash('sha256', $firstName),
                        'ln'                => hash('sha256', $lastName),
                        'fbp'               => $fbp,
                        'fbc'               => $fbc,
                        'external_id'       => $externalId,
                    ],
                    'custom_data'  => [
                        'currency'     => $currency,
                        'value'        => $value,
                        'order_id'     => $orderId,
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
          $error    = curl_error($ch);
          curl_close($ch);

          // Check for errors and display the result
          if ($error) {
            echo "cURL Error: " . $error;
          } else {
            echo  "Response from Facebook Conversion API: " . $response;
          }


        //Facebook Coversion API END
        curl_close($ch);
    } 
    else{
      // Handle payment failure
    }
  curl_close($stripe_ch);
}
if($payment_type== 'paypal'){
 
  $payment_type    =  "paypal";
  $item_name =  $_POST['item_name'];
  $amount    =  $_POST['amount'];
  $currency  =  $_POST['currency_code']; 
  $name      =  $_POST['payPal_name'];
  $surname   =  $_POST['payPal_surname'];
  $email         = $_POST['payPal_email'];
  $user_name     = $_POST['user_name'];
  $user_surname  = $_POST['user_surname'];
  $user_email    = $_POST['user_email'];
  $user_fullname = $user_name.' '.$user_surname;
  
  $insert_user = mysqli_query($conn, "INSERT INTO users (name, surname, email , created_at, updated_at)VALUES ('$user_name', '$user_surname', '$user_email', NOW(), NOW())"); 
  $lastid = mysqli_insert_id($conn); 
  if($lastid){
      mysqli_query($conn, "INSERT INTO `order` (order_id,user_id,payment_type, card_num, card_cvc,exp_month,exp_year,item_name,item_price,currency,status,created_at, updated_at)
      VALUES('','$lastid','$payment_type', '', '','','','$item_name','$amount','$currency','',NOW(), NOW())");
  }



    if($_POST['status'] == 'COMPLETED'){
    
      $status    =  $_POST['status'];
      $order_id  =  $_POST['order_id'];
      $full_name =  $_POST['payPal_name'].' '.$_POST['payPal_surname'];
      
      if($lastid){
        $sql = "UPDATE `order` set order_id='".$order_id."',status='".$status."',updated_at=NOW() WHERE user_id='".$lastid."'";
         if (mysqli_query($conn, $sql)) {
          // echo "Record updated successfully";
         } else {
           echo "Error updating record: " . mysqli_error($conn);
         }
      }
        

      /////// Connect to kajabi ///////
      $url = 'https://checkout.kajabi.com/webhooks/offers/TLKwTEaWXoD5nrvD/2148730028/activate';
      $data = array('name' => $user_fullname,'email' => $user_email);

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
      curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

      curl_exec($ch);

      if(curl_errno($ch)) {
          echo 'Error: ' . curl_error($ch);
        
          
      } else {
          echo 'Success: ' . $result;
        
      }
   
      /////// End ///////

    
      ///// Facebook Coversion API /////

        function get_fbc_from_fbclid() {
            $fbclid = isset($_GET['fbclid']) ? $_GET['fbclid'] : '';
            if (!empty($fbclid)) {
                return 'fb.1.' . time() . '.' . $fbclid;
            }
            return '';
          }

          $fbc_from_fbclid = get_fbc_from_fbclid();

          // Facebook Conversion API - Purchase Event with additional data using cURL

          // Replace with your access token and pixel ID
          $accessToken = FB_ACCESS_TOKEN;
          $pixelId     = PIXEL_ID;


          // Purchase details (replace with actual purchase data)
          $currency    = $currency;
          $value       = $amount;
          $orderId     = $order_id;
          $contentIds  = ['PROD123'];
          $contentName = $item_name;
          $contentType = 'product';
          $numItems    = 1;
          $contents    = [
            [
                'id' => 'PROD123',
                'quantity' => 1,
                'item_price' => $amount,
            ],
          ];


          // User details (replace with actual user data)
          
        
          $fbp = isset($_COOKIE['_fbp']) ? $_COOKIE['_fbp'] : '';
          $fbc = !empty($fbc_from_fbclid) ? $fbc_from_fbclid : (isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '');


          // Event and external ID (replace with unique identifiers)
          $eventId = 'EVENT12345';
          $externalId = $lastid;

          // Event source URL (replace with the actual URL)
          $eventSourceUrl = $site_url;

          // Event details
          $eventData = [
            'data' => [
                [
                    'event_name' => 'Purchase',
                    'event_time' => time(),
                    'event_id' => $eventId,
                    'user_data' => [
                        'client_user_agent' => $_SERVER['HTTP_USER_AGENT'],
                        'client_ip_address' => $_SERVER['REMOTE_ADDR'],
                        'em' => hash('sha256', strtolower($email)),
                        'fn' => hash('sha256', $name),
                        'ln' => hash('sha256', $surname),
                        'fbp' => $fbp,
                        'fbc' => $fbc,
                        'external_id' => $externalId,
                    ],
                    'custom_data'   => [
                        'currency'     => $currency,
                        'value'        => $value,
                        'order_id'     => $orderId,
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
            echo  "Response from Facebook Conversion API: " . $response;
          }


        //Facebook Coversion API END
        curl_close($ch);

        return "success";

    }
    else{
         return  "error";
    }
}

