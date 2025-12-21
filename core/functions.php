<?php
session_start();

function cleanInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../pages/login.php");
        exit();
    }
}

function uploadFile($file, $target_dir = "../assets/uploads/") {
    $target_file = $target_dir . basename($file["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        return false;
    }

    $new_name = uniqid() . "." . $imageFileType;
    if (move_uploaded_file($file["tmp_name"], $target_dir . $new_name)) {
        return $new_name;
    }
    return false;
}
?>