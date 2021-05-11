<?php
session_start(); //sessionを使う時は一番上にこれを記載する必要がある。
require('../dbconnect.php'); //データベースを使用する時はdbconnect.phpに記載された、共通コードを呼び出す。
if (!isset($_SESSION['join'])) {  //isset()・・・変数に何か入っている時trueになる。この場合は、!isset()なので、セッションのjoinが空の時に、index.phpにページ遷移するということになる。
	header('Location: index.php');
	exit();
}

//上のifでは、セッションに情報が入っていない状態でチェック画面にアクセスがあった場合、
//つまり、何らかの不正アクセスや不慮のエラーのようなもので入った侵入者を強制的にログイン画面に戻す処理を書いている。

if(!empty($_POST)){
	$statement= $db ->prepare('INSERT INTO members SET name=?, email=?,password=?,picture=?, created=NOW()');
	$statement->execute(array(
		$_SESSION['join']['name'],
		$_SESSION['join']['email'],
		sha1($_SESSION['join']['password']),	//パスワードをそのまま記録してしまうとセキュリティ的に良くないので、sha1で暗号化する。（パスワード入力フォームにもsha1を入力する。）
		$_SESSION['join']['image']
	));
	unset($_SESSION['join']);	//セッションを使い終わったらunset()を使用する事。

	header('Location: thanks.php');	//データベースへの入力とセッションへの記憶が終わったら、thanks.phpにページ遷移する。
	exit();
}

//上のifでは、入力フォームに情報が入ってPOSTされた時、セッションに記憶しつつ、データベースの membersテーブルに各情報を送信している。


?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>会員登録</title>

	<link rel="stylesheet" href="../style.css" />
</head>

<body>
	<div id="wrap">
		<div id="head">
			<h1>会員登録</h1>
		</div>

		<div id="content">
			<p>記入した内容を確認して、「登録する」ボタンをクリックしてください</p>
			<form action="" method="post">
				<dl>
					<dt>ニックネーム</dt>
					<dd>
						<?php print(htmlspecialchars($_SESSION['join']['name'], ENT_QUOTES)); ?>
					</dd>
					<dt>メールアドレス</dt>
					<dd>
						<?php print(htmlspecialchars($_SESSION['join']['email'], ENT_QUOTES)); ?>
					</dd>
					<dt>パスワード</dt>
					<dd>
						【表示されません】
					</dd>
					<dt>写真など</dt>
					<dd>
						<?php if ($_SESSION['join']['image'] !== '') : ?>
							<img src="../member_picture/<?php print(htmlspecialchars($_SESSION['join']['image'], ENT_QUOTES)); ?>"width="100" height="100" alt="">
						<?php endif; ?>
					</dd>
				</dl>
				<div><a href="index.php?action=rewrite">&laquo;&nbsp;書き直す</a> | <input type="submit" name="action" value="登録する" /></div>	<!-- name="action"がないと、$_POST[]の中に何も値が入らないので、thanks.phpに遷移できない。 -->
			</form>		<!-- 上のaタグ内のrewriteは、ページ遷移後、URLの末尾に付く為、$GET['action']か、$_REQUEST['action']で取得可能（逆に） -->
		</div>

	</div>
</body>

</html>