<?php
$baseUrl = 'https://sandbox.momodeveloper.mtn.com';
$secondaryKey = '1820814545d34fcfb29eb7543f2ea4cf'; 

//UUID Version 4
function generate_uuid(){
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
     $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
     return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
$uuid = generate_uuid();


function generateShortUUID($length = 5) {
    $uuid = generate_uuid(); // Generate a full UUID
    return substr(preg_replace('/[^a-zA-Z0-9]/', '', $uuid), 0, $length);
}
$shortUuid = generateShortUUID();

