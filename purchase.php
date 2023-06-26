<?php

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
$accessToken = 'EAAIbDURZAiW0BABaNlQGDUxrniMNDht0jgOwd1Y6QIGLgFDeLe7CPFLlCQNQrwqLJip9l2ZCZB7imMZC4D5uYNZBPXPQPcdBuZApWMvszqTyTtcRVdoragXNwFNT39swY1LQB4oPr3GZBl7ne99nYJp4Ar4qK958nozyAnniVWlMODM4toqaIA5';
$pixelId = '1857126894625043';


// Purchase details (replace with actual purchase data)
$currency = 'EUR';
$value = 123.45;
$orderId = 'ORDER12345';
$contentIds = ['PROD123'];
$contentName = 'Example Product';
$contentType = 'product';
$numItems = 1;
$contents = [
    [
        'id' => 'PROD123',
        'quantity' => 1,
        'item_price' => 123.45,
    ],
];


// User details (replace with actual user data)
$firstName = 'John';
$lastName = 'Doe';
$email = 'john.doe@example.com';
$fbp = isset($_COOKIE['_fbp']) ? $_COOKIE['_fbp'] : '';
//$fbc = isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '';
$fbc = !empty($fbc_from_fbclid) ? $fbc_from_fbclid : (isset($_COOKIE['_fbc']) ? $_COOKIE['_fbc'] : '');


// Event and external ID (replace with unique identifiers)
$eventId = 'EVENT12345';
$externalId = 'USER12345';

// Event source URL (replace with the actual URL)
$eventSourceUrl = 'https://example.com/checkout';

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
                'fn' => hash('sha256', $firstName),
                'ln' => hash('sha256', $lastName),
                'fbp' => $fbp,
                'fbc' => $fbc,
                'external_id' => $externalId,
            ],
            'custom_data' => [
                'currency' => $currency,
                'value' => $value,
                'order_id' => $orderId,
                'content_ids' => $contentIds,
                'content_name' => $contentName,
                'content_type' => $contentType,
                'contents' => $contents,
                'num_items' => $numItems,
            ],
            'event_source_url' => $eventSourceUrl,
            'action_source' => 'website',
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

?>
