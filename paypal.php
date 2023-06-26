<?php 

    
  include 'config.php';

  $payment_type    =  "paypal";
  $item_name =  $_POST['item_name'];
  $amount    =  $_POST['amount'];
  $currency  =  $_POST['currency_code']; 
  $name      =  $_POST['payPal_name'];
  $surname   =  $_POST['payPal_surname'];
  $email     =  $_POST['payPal_email'];

  $insert_user =  mysqli_query($conn, "INSERT INTO users (name, surname, email , created_at, updated_at)VALUES ('$name', '$surname', '$email', NOW(), NOW())"); 
      
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
      $data = array('name' => $full_name,'email' => 'paypal1@gmail.com');

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
          //$fbc = isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '';
          $fbc = !empty($fbc_from_fbclid) ? $fbc_from_fbclid : (isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '');


          // Event and external ID (replace with unique identifiers)
          $eventId = 'EVENT12345';
          $externalId = $lastid;

          // Event source URL (replace with the actual URL)
          $eventSourceUrl = 'http://e.missionedigitale.com/e.missionedigitale.com/';

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

?>