<?php

    require __DIR__.'/vendor/autoload.php';

    use Kreait\Firebase\Factory;
    use Kreait\Firebase\Messaging\CloudMessage;
    use Google\Client;
    use Google\Service\Exception as GoogleServiceException;
    use GuzzleHttp\Promise\Promise;


    // $factory = (new Factory)
    //     ->withServiceAccount('serviceAccountKey.json')
    //     ->withDatabaseUri('https://bankapp-74d70-default-rtdb.asia-southeast1.firebasedatabase.app/');

    // $messaging = $factory->messaging();
    // $device_token = $messaging->requestPermissi();

    function getGoogleAccessToken(){

        $credentialsFilePath = 'serviceAccountKey.json'; //replace this with your actual path and file name
        $client = new \Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->refreshTokenWithAssertion();
        $token = $client->getAccessToken();
        return $token['access_token'];
   }

    // print_r($accessToken);

    function sendMessage($accessToken,$device_token){


        $apiurl = 'https://fcm.googleapis.com/v1/projects/bankapp-74d70/messages:send';   //replace "your-project-id" with...your project ID
       
        $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json'
        ];
       
        $notification_tray = [
                'title'             => "Hoàng Long",
                'body'              => "Hoàng Long",
            ];
       
        $in_app_module = [
                "title"          => "Some data title (optional)",
                "body"           => "Some data body (optional)",
            ];
        //The $in_app_module array above can be empty - I use this to send variables in to my app when it is opened, so the user sees a popup module with the message additional to the generic task tray notification.
       
         $message = [
               'message' => [
                    'token'            => $device_token,
                    'notification'     => $notification_tray,
                    'data'             => $in_app_module,
                ],
         ];
        
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $apiurl);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
       
         $result = curl_exec($ch);
        
         if ($result === FALSE) {
             //Failed
             die('Curl failed: ' . curl_error($ch));
            }
            
            curl_close($ch);
       }

       
       $device_token = $_GET;
        $accessToken = getGoogleAccessToken();
       $mess = sendMessage($accessToken,$device_token);
        print_r($mess);
       
?>



