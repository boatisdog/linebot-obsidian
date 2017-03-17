<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
//condb
//Variables
$access_token = 'SiZyVVTPIPP4Qn9VwKKKCI0YA3yjbfpk/mjb4Az4bbnrd275417q/2+JV0XGZca29KQ1F0S1Gh4Tx3DC8mLjQYGnbVLsJzmI2AA7kRlq+983S/bm6h0u4bsEu4Iyb6sl2E8PQnm7d0wguJ3kz6pEhwdB04t89/1O/w1cDnyilFU=';


$db = pg_connect("postgres://krdookwgbudwkq:337d29bb2b87f471b47f286fcb7fa1fb885b4b063f9ea5197805f4f679e7d9b8@ec2-54-221-255-153.compute-1.amazonaws.com:5432/dd6j72nr8uanuq");
$query = "SELECT * FROM WEATHER_HUMIDITY WHERE hum <= 300 ORDER BY pic DESC LIMIT 1"; 
$result = pg_query($query); 
if (!$result) { 
	echo "Problem with query " . $query . "<br/>"; 
	echo pg_last_error(); 
	exit(); 
} 
$messages = [
	'type' => 'text',
	'text' => "ALERT"
];
$data = [
	"to" => "Uffb752fc81a0f82fe74a413b16913d7b",
	'messages' => [$messages]
];
$url = 'https://api.line.me/v2/bot/message/push';

// Get POST body content
$content = file_get_contents('php://input');
// Parse JSON
$events = json_decode($content, true);
// Validate parsed JSON data
if (!is_null($events['events'])) {
	// Loop through each event
	foreach ($events['events'] as $event) {
		// Reply only when message sent is in 'text' format
		if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
			
			// Get text sent
			$text = $event['message']['text'];
			// Get replyToken
			$replyToken = $event['replyToken'];
			$messages = [
				'type' => 'text',
				'text' => "has not this command"
			];
			$data = [
				'replyToken' => $replyToken,
				'messages' => [$messages],
			];
			if ($text == "get current weather"){
				//select//
				$query = "SELECT * FROM WEATHER_HUMIDITY ORDER BY pic DESC LIMIT 1"; 
				$result = pg_query($query); 
				if (!$result) { 
					echo "Problem with query " . $query . "<br/>"; 
					echo pg_last_error(); 
					exit(); 
				} 
				while($myrow = pg_fetch_assoc($result)) { 
					$output = "Weather on : ".$myrow['date_c']."\nTemp is : ".$myrow['temp']."\nWeather is : ".$myrow['weather']."\nPressure is : ".$myrow['air_p']."\nHumidity is : ".$myrow['hum'];
					$imagename = $myrow['pic'];
				} 
				//////////
				// Build message to reply back
				$messages = [
					'type' => 'text',
					'text' => $output
				];
				$image = [
					'type' => 'image',
					"originalContentUrl" => "https://raw.githubusercontent.com/boatisdog/linebot-obsidian/master/pic/".$imagename.".jpg",
					"previewImageUrl" => "https://raw.githubusercontent.com/boatisdog/linebot-obsidian/master/pic/".$imagename.".jpg"
				];
				// Make a POST Request to Messaging API to reply to sender
				$data = [
					'replyToken' => $replyToken,
					'messages' => [$messages, $image],
				];
			}else if ($text == "get history"){
				//select//
				$query = "SELECT * FROM WEATHER_HUMIDITY"; 
				$result = pg_query($query); 
				if (!$result) { 
					echo "Problem with query " . $query . "<br/>"; 
					echo pg_last_error(); 
					exit(); 
				} 
				while($myrow = pg_fetch_assoc($result)) { 
					$output = $output."Weather on : ".$myrow['date_c']."\nTemp is : ".$myrow['temp']."\nWeather is : ".$myrow['weather']."\nPressure is : ".$myrow['air_p']."\nHumidity is : ".$myrow['hum']."\n============================";
				} 
				//////////
				// Build message to reply back
				$messages = [
					'type' => 'text',
					'text' => $output
				];
				// Make a POST Request to Messaging API to reply to sender
				$data = [
					'replyToken' => $replyToken,
					'messages' => [$messages]
				];
			}else if ($text == "who am i"){
				//////////
				// Build message to reply back
				$messages = [
					'type' => 'text',
					'text' => json_encode($event);
				];
				// Make a POST Request to Messaging API to reply to sender
				$data = [
					'replyToken' => $replyToken,
					'messages' => [$messages]
				];
			}
			$url = 'https://api.line.me/v2/bot/message/reply';
		}
	}
}
$post = json_encode($data);
$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$result = curl_exec($ch);
curl_close($ch);
echo $result . "\r\n";
pg_close();
echo "OK";