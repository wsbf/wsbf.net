<?php

require_once("../auth/auth.php");
require_once("../connect.php");
require_once("config.php");

define('ALLOWED_EXTENSIONS', serialize(["mp3"]));
define('ALLOWED_FOLDERS', serialize(["albums", "carts"]));
/**
 * Sends a JSON response with the specified status code and data.
 *
 * @param int $status The HTTP status code.
 * @param array $data The data to include in the JSON response.
 */
function sendJsonResponse($status, $data)
{
    header("Content-Type: application/json");
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$REPLACE_PAIRS = array(
    'Š' => 'S', 'š' => 's', 'Ð' => 'Dj', 'Ž' => 'Z', 'ž' => 'z',
    'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A', 'Æ' => 'A',
    'Ç' => 'C', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
    'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
    'Ñ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O', 'Ø' => 'O',
    'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
    'Ý' => 'Y', 'Þ' => 'B', 'ß' => 'Ss',
    'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a',
    'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
    'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ð' => 'o',
    'ñ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o',
    'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'Ť' => 'T', 'ý' => 'y', 'þ' => 'b', 'ÿ' => 'y',
    'ƒ' => 'f', 'Č' => 'C', 'č' => 'c', 'Ć' => 'C', 'ć' => 'c', 'Ŕ' => 'R', 'ŕ' => 'r',
    '$' => 'S', 'Ů' => 'U', 'ĺ' => 'l', 'ľ' => 'l', 'ř' => 'r', 'ť' => 't', 'ň' => 'n',
    'ď' => 'd', 'ů' => 'u', 'Ď' => 'D', 'Ĺ' => 'L', 'Ľ' => 'L', 'Ň' => 'N', 'Ř' => 'R'
);


/**
 * Sanitizes a filename.
 *
 * @param string $filename The filename to sanitize.
 * @return string The sanitized filename.
 */
function sanitizeFilename($filename)
{
    global $REPLACE_PAIRS;

    $pathinfo = pathinfo($filename);
    $filename = $pathinfo['filename'];
    $extension = isset($pathinfo['extension']) ? '.' . $pathinfo['extension'] : '';

    $filename = str_replace("&", "and", $filename);
    $filename = str_replace("!", "chk", $filename);
    $filename = strtr($filename, $REPLACE_PAIRS);

    $filename = trim(preg_replace("/[^\w ]/i", "", $filename));

    if (empty($filename)) {
        $filename = 'file';
    }

    return $filename . $extension;
}


/**
 * Handles file uploads depending on POST file and folder parameters.
 */
function handleFileUpload()
{
    if (!isset($_FILES["file"])) {
        sendJsonResponse(400, ["error" => "No file uploaded"]);
    }

    $file = $_FILES["file"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        sendJsonResponse(500, ["error" => "File upload error: " . $file['error']]);
    }

    $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $allowedExtensions = unserialize(ALLOWED_EXTENSIONS);
    if (!in_array($extension, $allowedExtensions)) {
        sendJsonResponse(400, ["error" => "Invalid file type. Only .mp3 allowed."]);
    }

    $allowedFolders = unserialize(ALLOWED_FOLDERS);
    if (!isset($_POST["folder"]) || !in_array($_POST["folder"], $allowedFolders)) {
        sendJsonResponse(400, ["error" => "Invalid folder"]);
    }

    $destinationDir = IMPORT_SRC . $_POST["folder"] . "/";

    if (!is_dir($destinationDir) && !mkdir($destinationDir, 0775, true)) {
        sendJsonResponse(500, ["error" => "Could not create destination directory"]);
    }

    $filename = sanitizeFilename($file["name"]);
    $targetPath = $destinationDir . $filename;

    if (!move_uploaded_file($file["tmp_name"], $targetPath)) {
        sendJsonResponse(500, ["error" => "Could not move uploaded file"]);
    }

    sendJsonResponse(200, [
        "message" => "File uploaded successfully",
        "filename" => $filename,
        "folder" => $_POST["folder"]
    ]);
}

/**
 * Handles file deletions based on DELETE filename and folder parameters.
 */
function handleFileDelete()
{
    $allowedFolders = unserialize(ALLOWED_FOLDERS);
    if (!isset($_GET["filename"]) || !isset($_GET["folder"]) || !in_array($_GET["folder"], $allowedFolders)) {
        sendJsonResponse(400, ["error" => "Invalid parameters"]);
    }

    $filename = $_GET["filename"];
    $folder = $_GET["folder"];
    $targetPath = IMPORT_SRC . $folder . "/" . $filename;

    if (!in_array($folder, $allowedFolders)) {
        sendJsonResponse(400, ["error" => "Invalid folder"]);
    }

    if (!file_exists($targetPath)) {
        sendJsonResponse(404, ["error" => "File does not exist"]);
    }

    if (!unlink($targetPath)) {
        sendJsonResponse(500, ["error" => "Could not delete file"]);
    }

    sendJsonResponse(200, ["message" => "File deleted successfully", "filename" => $filename]);
}

authenticate();

$mysqli = construct_connection();
if (!auth_senior_staff($mysqli)) {
    sendJsonResponse(403, ["error" => "Not authorized"]);
}

$method = $_SERVER["REQUEST_METHOD"];

switch ($method) {
    case "POST":
        handleFileUpload();
        break;
    case "DELETE":
        handleFileDelete();
        break;
    default:
        sendJsonResponse(405, ["error" => "Method not allowed"]);
}

$mysqli->close();

