<?php
//Connect to Line API
$access_token = 'SiZyVVTPIPP4Qn9VwKKKCI0YA3yjbfpk/mjb4Az4bbnrd275417q/2+JV0XGZca29KQ1F0S1Gh4Tx3DC8mLjQYGnbVLsJzmI2AA7kRlq+983S/bm6h0u4bsEu4Iyb6sl2E8PQnm7d0wguJ3kz6pEhwdB04t89/1O/w1cDnyilFU=';
//Connect to Database
$db = pg_connect("postgres://krdookwgbudwkq:337d29bb2b87f471b47f286fcb7fa1fb885b4b063f9ea5197805f4f679e7d9b8@ec2-54-221-255-153.compute-1.amazonaws.com:5432/dd6j72nr8uanuq");
//echo $resultsql;
//
//Get data from line api
$content = file_get_contents('php://input');
//Decode json to php
$events = json_decode($content, true);
//ถ้ามีการรับค่าจาก line api
if (!is_null($events['events'])) {
	//วนลูปการทำงาน
	foreach ($events['events'] as $event) {
		// ตรวจสอบเงื่อนไขว่าค่าที่รับมาเป็นข้อความหรือไม่
		if ($event['type'] == 'message' && $event['message']['type'] == 'text') {
			
			// เก็บข้อความที่รับมาในตัวแปร text
			$text = $event['message']['text'];
			// เก็บค่า Token [ข้อมูลยืนยันตนของ line] ในตัวแปร replytoken
			$replyToken = $event['replyToken'];
			
			//ถ้าข้อความที่ส่งมาคือ "คำสั่ง"
			if ($text == "คำสั่ง"){
				//เก็บข้อความไว้ในตัวแปร output
				$output = "========คำสั่งทั้งหมด=========\n|  weather  | เช็คสภาพอากาศปัจจุบัน\n|   history   | ดูประวัติการเช็คสภาพอากาศ\n|clearhistory| ล้างประวัติ\n========================";
			$messages = [
				'type' => 'text',
				'text' => $output
			];
			$data = [
				'replyToken' => $replyToken,
				'messages' => [$messages],
			];	
			}
			//ถ้าข้อความที่ส่งมาคือ "data"
			if ($text == "weather"){
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
				pg_close();
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
				$data = [
					'replyToken' => $replyToken,
					'messages' => [$messages, $image],
				];
			}
			if ($text == "history"){
			//นำคำสั่งที่จะใช้เก็บไว้ในตัวแปร query
			 $query = "SELECT * FROM weather_botline"; 
			//ทำการดึงข้อมูลจาก Database ใน table weather_botline_proxima	
       			 $result = pg_query($query); 
			 $output = "  -:-History Get Weather-:-\n=======================\n";
           		 //ทำการนำข้อมูลออกมา โดยเรียงจากแถว บนสุดลงล่าง
			while($myrow = pg_fetch_assoc($result)) { 
				//เก็บไว้ในตัวแปร output
              			$output = $output."Weather on : ".$myrow['date_c']."\nTemp is : ".$myrow['temp']."\nWeather is : ".$myrow['weather']."\nPressure is : ".$myrow['air_p']."\nHumidity is : ".$myrow['hum']."\n============================\n";
       			 } 
			$messages = [
				'type' => 'text',
				'text' => $output
			];
			$data = [
				'replyToken' => $replyToken,
				'messages' => [$messages],
			];	
			}
			if ($text == "clearhistory"){
			
			 $query = "DELETE FROM WEATHER_HUMIDITY"; 
			//ทำการเคลียข้อมูลทั้งในใน table weather_botline_proxima
       			 $result = pg_query($query);
				$output = "ทำการลบประวัติเรียบร้อยแล้ว";
			$messages = [
				'type' => 'text',
				'text' => $output
			];
			$data = [
				'replyToken' => $replyToken,
				'messages' => [$messages],
			];	
			}
			//เตรียมข้อความที่จะส่งกลับไว้ในตัวแปร อาเรย์ messege
			
			//ยกเลิกการ Connect Database
			pg_close();
			//เก็บค่า link ของไลน์bot
			$url = 'https://api.line.me/v2/bot/message/reply';
			//รวมข้อมูลทั้งหมดไว้ใน อาเรย์ data เตรียมเข้ารหัสเป็น Json
			//เข้ารหัสเป็น Json เพื่อเตรียมส่งกลับไปใน line
			$post = json_encode($data);
			//สร้าง Header ในการส่งสำหรับ line
			$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);
			// ทำการส่งค่าที่ทำการ เข้ารหัสเป็น Json ไปยังแชทไลน์
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$result = curl_exec($ch);
			curl_close($ch);
			//echo $result . "\r\n";
		}
	}
}
echo "OK";