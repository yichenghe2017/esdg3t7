<?php
// header("refresh: 0;");
// bot token

session_start();


$botToken = "741490553:AAFBTPDrVNMhwD367Y8IX-01lW4dBZHrWR8";

// url
$url = "https://api.telegram.org/bot".$botToken;

// getupdate json
$update = file_get_contents($url."/getupdates");
$updateArr = json_decode($update, TRUE);

// print "<pre>";
// print_r($updateArr);
// print "</pre>";


$totalArrayLength = sizeof($updateArr["result"]);
$latestArrIndex = $totalArrayLength - 1;

if($updateArr["result"]==[]){
    echo "no msg";
    return;
}

if(isset($updateArr["result"][$latestArrIndex]["message"])){
    $latestText = $updateArr["result"][$latestArrIndex]["message"]["text"];
    $latestUserID = $updateArr["result"][$latestArrIndex]["message"]["from"]["id"];
$latestUserFirstName = $updateArr["result"][$latestArrIndex]["message"]["from"]["first_name"];
}else{
    $latestText = $updateArr["result"][$latestArrIndex]["callback_query"]["data"];
    $latestUserID = $updateArr["result"][$latestArrIndex]["callback_query"]["from"]["id"];
    $latestUserFirstName = $updateArr["result"][$latestArrIndex]["callback_query"]["from"]["first_name"];
}

echo "<h1>ESD KTV Telegram Bot</h1>";
echo "<h2>Latest Message</h2>";
echo "Latest message from ".$latestUserFirstName.": ".$latestText."<br>";



run();

function run (){
    header("refresh: 0;");

    $botToken = "741490553:AAFBTPDrVNMhwD367Y8IX-01lW4dBZHrWR8";
    // url
    $url = "https://api.telegram.org/bot".$botToken;
    // getupdate json
    echo $_SESSION['offset'];
    echo $url."/getupdates?".($_SESSION['offset']+1);
    $update = file_get_contents($url."/getupdates?offset=".($_SESSION['offset']));


    $updateArr = json_decode($update, TRUE);
    $totalArrayLength = sizeof($updateArr["result"]);
    $latestArrIndex = $totalArrayLength - 1;

    if($updateArr["result"]==[]){
        echo "no msg";
        return;
    }
    

    if(isset($updateArr["result"][$latestArrIndex]["message"])){
        $latestText = $updateArr["result"][$latestArrIndex]["message"]["text"];
        $latestUserID = $updateArr["result"][$latestArrIndex]["message"]["from"]["id"];
    $latestUserFirstName = $updateArr["result"][$latestArrIndex]["message"]["from"]["first_name"];
    }else{
        $latestText = $updateArr["result"][$latestArrIndex]["callback_query"]["data"];
        $latestUserID = $updateArr["result"][$latestArrIndex]["callback_query"]["from"]["id"];
        $latestUserFirstName = $updateArr["result"][$latestArrIndex]["callback_query"]["from"]["first_name"];
    }
    

    $offset = $updateArr["result"][$latestArrIndex]["update_id"];

    if($latestText == "/start" && $offset != $_SESSION['offset']) {
        // start the program
        start($latestUserFirstName, $latestUserID);

        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }

    // send photo
    if($latestText == "/photo" && $offset != $_SESSION['offset']) {
        // echo "True";
        sendPhoto($latestUserID);

        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }

    // get books
    if($latestText == "/books" && $offset != $_SESSION['offset']) {
        // echo "True";
        // sendPhoto($latestUserID);
        sendBooks($latestUserID);

        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }

    // enter
    if($latestText == "/enter" && $offset != $_SESSION['offset']) {
        // echo "True";
        // sendPhoto($latestUserID);
        sendMessage($latestUserID, "May I know your user ID?");

        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }


    if($_SESSION['lastSentFromBot'] == "May I know your user ID?" && $offset != $_SESSION['offset']) {
        // save customer id to session before update tibco
        $_SESSION['user_id'] = $latestText;


        if(isset($_SESSION['userMap'])){
            $userMap = $_SESSION['userMap'];
            $lastSentUserID = $_SESSION['lastSentUserID'];
            $userMap[$lastSentUserID] = $latestText;
            $_SESSION['userMap'] = $userMap;
        }else{
            $userMap = [];
            $lastSentUserID = $_SESSION['lastSentUserID'];
            $userMap[$lastSentUserID] = $latestText;
            $_SESSION['userMap'] = $userMap;
        }
        echo "$_SESSION userMap: <br>";
        print_r($_SESSION['userMap']);

        

        // send msg to collect book id
        sendMessageWithKeyboardForRoom($latestUserID, "Select your room");

        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }


    if($_SESSION['lastSentFromBot'] == "Select your room" && $offset != $_SESSION['offset']) {
        // save book id to session before update tibco
        $_SESSION['room_id'] = $latestText;

        $user_id = $_SESSION['user_id'];
        $room_id = $_SESSION['room_id'];

        // send successful login
        sendMessage($latestUserID, "Gimme a sec...");

        enter($user_id, $room_id, $latestUserID);



        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }

    // choose
    if($latestText == "/choose" && $offset != $_SESSION['offset']) {

    //     sendMessage($latestUserID, "May I have your user ID?");

    //     $_SESSION['offset'] = $offset;
    //     $_SESSION['index'] = $latestArrIndex;
    // }

    // if($_SESSION['lastSentFromBot'] == "May I have your user ID?" && $offset != $_SESSION['offset']) {
    //     // save customer id to session before update tibco
    //     $_SESSION['user_id'] = $latestText;

    //     // send msg to collect book id
        sendMessage($latestUserID, "Reply with your song ID");

        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }

    if($_SESSION['lastSentFromBot'] == "Reply with your song ID" && $offset != $_SESSION['offset']) {
        // save book id to session before update tibco
        $_SESSION['song_id'] = $latestText;

        $user_id = $_SESSION['userMap'][$_SESSION['lastSentUserID']];
        $song_id = $_SESSION['song_id'];
        sendMessage($latestUserID, "Just a sec...");

        choose($user_id, $song_id);

        // send successful message!
        sendMessage($latestUserID, "Song has been selected successfully!");
        // sendMessage($latestUserID, "For testing: user_id = ".$user_id." song_id = ".$song_id);


        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }


    // payment

    // "/upgrade" command initiated
    if($latestText == "/upgrade" && $offset != $_SESSION['offset']) {

        if(!isset($_SESSION['userMap'][$latestUserID])){
            sendMessage($latestUserID, "Please /login first!");
        } else {
            $KTVUserID = $_SESSION['userMap'][$latestUserID];
            $paymentGetUrl = "https://ronaldlay2017-eval-test.apigee.net/create_payment/".$KTVUserID;
            
            $file_get_contents = file_get_contents($paymentGetUrl);
            $confirmURL = "https://www.google.com";
            sendMessageWithConfirmKeyboard($latestUserID, "You're going to upgrade to our premium package...", $confirmURL, urlencode($file_get_contents));
        }

        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }














    if($offset != $_SESSION['offset']) {
        // sendMessage($latestUserID, "Yo ".$latestUserFirstName.", SHOW ME THE REAL THING!!!");

        $_SESSION['offset'] = $offset;
        $_SESSION['index'] = $latestArrIndex;
    }

    // offset the thing!
    

}

function sendMessage($userID, $text) {
    $botToken = "741490553:AAFBTPDrVNMhwD367Y8IX-01lW4dBZHrWR8";
    $url = "https://api.telegram.org/bot".$botToken;
    $urlToSend = $url."/sendmessage?chat_id=".$userID."&text=".$text;
    $resultJSON = file_get_contents($urlToSend);
    $resultArr = json_decode($resultJSON, TRUE);
    $_SESSION['lastSentFromBot'] = $text;
    $_SESSION['lastSentUserID'] = $userID;
}

function sendMessageWithKeyboardForRoom($userID, $text) {
    $botToken = "741490553:AAFBTPDrVNMhwD367Y8IX-01lW4dBZHrWR8";
    $url = "https://api.telegram.org/bot".$botToken;

    $resp = array(
        'inline_keyboard' => array(
            array(
                array("text"=>"Room 1", "callback_data"=>"1"),
                array("text"=>"Room 2", "callback_data"=>"2"),
                array("text"=>"Room 3", "callback_data"=>"3")
            )
        )
        // "resize_keyboard" => true,
        // "one_time_keyboard" => true
    );
    $reply = json_encode($resp);

    $urlToSend = $url."/sendmessage?chat_id=".$userID."&text=".$text."&reply_markup=".$reply;

    file_get_contents($urlToSend);

    $_SESSION['lastSentFromBot'] = $text;
}

function sendMessageWithConfirmKeyboard($userID, $text, $confirmURL, $paymentURL) {
    $botToken = "741490553:AAFBTPDrVNMhwD367Y8IX-01lW4dBZHrWR8";
    $url = "https://api.telegram.org/bot".$botToken;

    $resp = array(
        'inline_keyboard' => array(
            array(
                array("text"=>"Click to pay (PayPal)", "url"=>$paymentURL)
            )
        )
        // "resize_keyboard" => true,
        // "one_time_keyboard" => true
    );
    $reply = json_encode($resp);

    $urlToSend = $url."/sendmessage?chat_id=".$userID."&text=".$text."&reply_markup=".$reply;

    file_get_contents($urlToSend);

    $_SESSION['lastSentFromBot'] = $text;

}



function start($userFirstName, $userID){
    $text = urlencode("Hi ".$userFirstName."! \nI'm your KTV ChatBot. \nSend /enter to login \nSend /choose to pick a song  \nENJOY!");
    sendMessage($userID, $text);
}

function sendPhoto($userID){
    $photoURL = "http://cdn.shopify.com/s/files/1/0986/5790/products/HelloDecal-PRINT_grande.png?v=1481472974";
    $botToken = "741490553:AAFBTPDrVNMhwD367Y8IX-01lW4dBZHrWR8";
    $url = "https://api.telegram.org/bot".$botToken;
    $urlToSend = $url."/sendphoto?chat_id=".$userID."&photo=".$photoURL;
    $resultJSON = file_get_contents($urlToSend);
    $resultArr = json_decode($resultJSON, TRUE);
}

function sendBooks($userID) {
    // change url accordingly
    $invokeURL = "";
    
    $jsonContent = file_get_contents($invokeURL);
    $jsonArr = json_decode($jsonContent, TRUE);
    print_r($jsonArr);

    for ($i=0; $i < count($jsonArr["Book"]); $i++) { 
        $title = $jsonArr["Book"][$i]["title"];
        $isbn13 = $jsonArr["Book"][$i]["isbn13"];
        $price = $jsonArr["Book"][$i]["price"];
        $availability = $jsonArr["Book"][$i]["availability"];
        $text = urlencode("Book title: ".$title." \nISBN13: ".$isbn13." \nPrice: $".$price." \nAvailability: ".$availability);
        sendMessage($userID, $text);
    }
}

function enter($user_id, $room_id, $latestUserID){

    //API URL PLS CHANGE B4 RUNNING
    $url = "https://ronaldlay2017-eval-test.apigee.net/login";

    //create a new cURL resource
    $ch = curl_init($url);

    //setup request to send json via POST
    $data = array(
            "user_id"=> (int)$user_id,
            "room_id"=> (int)$room_id
    );
    $jsonToPost = json_encode($data);

    //attach encoded JSON string to the POST fields
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonToPost);

    //set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Accept: application/json'));

    //return response instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //execute the POST request
    $result = curl_exec($ch);
    print_r($result);

    echo "<pre>";
    $resultArr = json_decode($result, TRUE);
    print_r($resultArr);


    echo "</pre>";
    $KTVUserName = $resultArr['name'];
    $users = "";
    for ($i=0; $i < count($resultArr['users']); $i++) { 
        $users = $users.$resultArr['users'][$i]['name']." \n";
    }
    $songs = "";
    for ($i=0; $i < count($resultArr['songs']); $i++) { 
        $song_book = array(
            1 => "This is What You Came For - Calvin Harris",
            2 => "Shake it Off - Taylor Swift",
            3 => "Baby Shark"
        );
        $song_index = $resultArr['songs'][$i]['song_id'];
        $song_n_artist = $resultArr['songs'][$i]['song_n_artist'];
        $songs = $songs."[".$song_index."] ".$song_n_artist." \n";
    }

    $sendToUser = urlencode("Hi ".$KTVUserName.", your room is ready!\n\nMembers in your room: \n".$users." \nSongs for you: \n".$songs."\nTo pick a song, click /choose");
    sendMessage($latestUserID, $sendToUser);
    // print_r($sendToUser);

    // $content = file_get_contents($result);

    //close cURL resource
    curl_close($ch);
}

function choose($user_id, $song_id) {

    //API URL PLS CHANGE B4 RUNNING
    $url = "https://ronaldlay2017-eval-test.apigee.net/choose";

    //create a new cURL resource
    $ch = curl_init($url);

    //setup request to send json via POST
    $data = array(
            "user_id" => (int)$user_id,
            "song_id" => (int)$song_id,
            "song_and_artist" => "Shake it Off - Taylor Swift"
    );
    $jsonToPost = json_encode($data);

    //attach encoded JSON string to the POST fields
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonToPost);

    //set the content type to application/json
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Accept: application/json'));

    //return response instead of outputting
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //execute the POST request
    $result = curl_exec($ch);

    //close cURL resource
    curl_close($ch);
}


?>