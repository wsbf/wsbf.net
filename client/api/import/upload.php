<?php

/**
 * @file import/upload.php
 * @author jai agarwal
 */

if ($_FILES['file']) {
    $targetDirectory = "/home/compe/RIPPED_MUSIC/albums/";
    $targetFile = $targetDirectory . basename($_FILES["file"]["name"]);

    if (move_uploaded_file($_FILES["file"]["tmp_name"], $targetFile)) {
        echo "The file " . basename($_FILES["file"]["name"]) . " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}
?>