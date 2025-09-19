<?php

require __DIR__."/vendor/autoload.php"; //this is the file for all the dependencies classes

use Kreait\Firebase\Factory; // get the firebase class 

// ==> handle the arguments
if ($argc < 2) {
  echo "TRY: php seedOrders.php {number > 0}";
  exit(1);
}

$orderNumber = intval($argv[1]);
if ($argc == 2 && $orderNumber <= 0) {
    echo "Number must be positive";
    exit(1);
}

// ==> init the firebase
$firebase = (new Factory)->withServiceAccount(__DIR__. "/serviceAccount.json")->withDatabaseUri("https://orders-assignment-d48a4-default-rtdb.firebaseio.com/");

$RTDB = $firebase->createDatabase();

// begin the insertion loop 
for ($i = 1; $i <= $orderNumber; $i++) {
  
  $newOrderRef = $RTDB->getReference('orders')->push(); 
  $orderId = $newOrderRef->getKey();
  $totalPrice = rand(10, 1000);
  $placedAt = gmdate('Y-m-d\TH:i');

  $orderData = [ 
    'id' => $orderId,
    'total_price' => $totalPrice,
    'placed_at' => $placedAt,
    'status' => 'new',
    'delivered_at' => null,
    'cancelled_at' => null
  ];

  $RTDB->getReference('orders/'.$orderId)->set($orderData);
  echo "Inserted order with id: $orderId \n";
}

echo "Done the insertion of orders !\n";

