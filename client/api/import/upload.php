<?php

/**
 * @file import/upload.php
 * @author jai agarwal
 */

// allow from origin of wsbf.net
header('Access-Control-Allow-Origin: *'); // change this line
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function upload_files() {

    // destination directory of the uploaded files
    $uploadDir = '/home/jagarwa/'; 

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $response = [];
    // var_dump($_FILES);

    // loop through each file uploaded, extract data 
    foreach ($_FILES['files']['name'] as $key => $name) {
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $error = $_FILES['files']['error'][$key];
        $size = $_FILES['files']['size'][$key];
        
        // check for upload errors
        if ($error === UPLOAD_ERR_OK) {
            $destination = $uploadDir . basename($name);

            // move file to the destination directory
            if (move_uploaded_file($tmpName, $destination)) {
                $response[] = [
                    'name' => $name,
                    'status' => 'success',
                    'path' => $destination,
                ];
            } else {
                $response[] = [
                    'name' => $name,
                    'status' => 'error',
                    'message' => 'Failed to move uploaded file.',
                ];
            }
        } else {
            $response[] = [
                'name' => $name,
                'status' => 'error',
                'message' => 'Error uploading file: ' . $error,
            ];
        }
    }

    return json_encode($response);
}

if ( $_SERVER["REQUEST_METHOD"] == "POST" ) {
    // if (isset($_FILES['files'])) {

	$response = upload_files();

	header("Content-Type: application/json");
	exit($response);
}
?>