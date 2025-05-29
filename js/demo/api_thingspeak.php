<?php
include 'db.php';

$set = $conn->query("SELECT * FROM settings WHERE id=1")->fetch_assoc();

$json = file_get_contents("https://api.thingspeak.com/channels/{$set['channel_id']}/feeds.json?api_key={$set['api_key']}&results=1");
$data = json_decode($json)->feeds[0] ?? null;

$response = [
    'waterLevel' => (isset($data->field1) && is_numeric($data->field1)) ? $data->field1 : 0,
    'ph'         => (isset($data->field2) && is_numeric($data->field2)) ? $data->field2 : 0,
    'tempC'      => (isset($data->field3) && is_numeric($data->field3)) ? $data->field3 : 0
];

header('Content-Type: application/json');
echo json_encode($response);
