<?php
// router.php
// php -S localhost:8000 router.php
if (preg_match('/\.(?:png|jpg|jpeg|gif)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // сервер возвращает файлы напрямую.
} else {

    if ('POST' == $_SERVER['REQUEST_METHOD']){
        echo json_encode(array("a" => 11));
    }
    if ('GET' == $_SERVER['REQUEST_METHOD']){
        echo json_encode(array("a" => 10));
    }
}
?>
