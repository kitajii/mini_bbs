<?php
session_start();
require('dbconnect.php');

//削除するかどうかの検査

if(isset($_SESSION['id'])){ //ログインしているかどうかの検査
    $id = $_REQUEST['id'];

    $messages = $db->prepare('SELECT * FROM posts WHERE id=?'); //ログイン者idと投稿者idが一致しているか検査
    $messages->execute(array($id));
    $message = $messages->fetch();

    if($message['member_id'] === $_SESSION['id']){
        $del = $db->prepare('DELETE FROM posts WHERE id=?');
        $del->execute(array($id)); //$idは$_REQUEST['id]（URLに入っている投稿のid）
    }
}

header('Location: index.php');
exit();
?>