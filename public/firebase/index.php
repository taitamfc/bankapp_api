<?php

    require __DIR__.'/vendor/autoload.php';

    use Kreait\Firebase\Factory;

    $factory = (new Factory)
        ->withServiceAccount('serviceAccountKey.json')
        ->withDatabaseUri('https://bankapp-74d70-default-rtdb.asia-southeast1.firebasedatabase.app/');

    $database = $factory->createDatabase();

    $data = $_GET;
    $database->getReference('transaction_app')
    ->push($data);

?>