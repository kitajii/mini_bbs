<?php
try{
    $db = new PDO('mysql:dbname=mini_bbs;host=localhost;charset=utf8', 'root', 'root');
}catch(PDOException $e){
    print('DB接続エラー：'.$e->getMessage());
}
?>

<!--
    例外処理（try catch構文）

    フェイタルエラーという強いエラーが発生した場合
    （ユーザー名を間違える、データベースの電源が入っていないなど）
    うまく作動せずに、そのままプログラムが終了してしまう。
    つまり、データベースを取り扱う時は、プログラムが正しくてもデータベース側に問題があるとエラーになる。

    例外処理は
    tryで試した結果にエラーが発生したら、
    catchの動作をするというもの。（ifに似ている）

    今回の場合、PDOExceptionという設定済みの動作を$eとし、
    そして、$eの中に設定されているエラーメッセージの内容を表示してくれるgetMessage関数を呼び出す。というもの。

    データベースを使う時は　丸暗記　＆　丸写し　でOK。
-->