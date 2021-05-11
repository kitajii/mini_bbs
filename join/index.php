<?php
session_start();
require('../dbconnect.php');

// エラーがあったら、$errorという配列にエラーの種類を代入する。
// name email password に文字が入っていない->($error)配列内にある(name email password)配列にblankというエラーを代入
// 写真のアップロードファイルが jpg,gif,png 以外だった場合、image配列にtypeというエラーを代入

if (!empty($_POST)) {


	if ($_POST['name'] === '') {									//名前のフォームに何も入力がない場合、
		$error['name'] = 'blank';									//名前が入っていないエラー'blank'を、nameをキーとしてエラー配列に入れる。
	}


	if ($_POST['email'] === '') {
		$error['email'] = 'blank';
	}


	if (strlen($_POST['password']) < 4) {							//strlen は、入力した文字数を計って数字で返してくれる。
		$error['password'] = 'length';								// passwordは4文字以上出ない場合、lengthというエラーをpassword配列に代入

	}


	if ($_POST['password'] === '') {
		$error['password'] = 'blank';
	}



	$fileName = $_FILES['image']['name'];
	if (!empty($filename)) {										//ファイルがアップロードされていれば、
		$ext = substr($fileName, -3);								//substr($fileName, -3)で、ファイル名の後ろ3文字を返す関数。これでアップされたファイルの拡張子を取得。$extはextension（拡張子）より。
		if ($ext != 'jpg' && $ext != 'gif' && $ext != 'png') {		//jpg,gif,pngでなければ、
			$error['image'] = 'type';								//typeというエラーをエラー配列に代入。
		}
	}


	// アカウントの重複チェック
	// prepareでSQLを操作し、同じメールアドレスが何件あるかをCOUNT(*)を使用して確認

	if (empty($error)) {																//空白フォームがある状態がある状態でDBと接続してしまうとエラーが発生する恐れがあるので、チェックが終わった後に作動させる。
		$member = $db->prepare('SELECT COUNT(*) AS cnt FROM members WHERE email=?');	//入力されたアドレスと同じアドレスのデータを検索し、hitした件数をCOUNT(*)もとい、cntとする。
		$member->execute(array($_POST['email']));										//入力されたメールアドレスを「?」に代入
		$record = $member->fetch();														//
		if ($record['cnt'] > 0) {														//cntの数が1件以上あれば、
			$error['email'] = 'duplicate';												//エラー配列のemailキーにduplicate(複数)というエラー要素を代入する。
		}
	}



	if (empty($error)) {									 				// if(empty($error)) で「エラー配列が空の場合」->「エラーが出なかった場合」という意味になる。
		$image = date('YmdHis') . $_FILES['image']['name'];  				// $_FILESにはinput type="file"の情報が格納されたグローバル変数で、['image']の中の['name']にファイル名が保存されている。※['image']はinput name="image"より。file名は被らないように、アップロード時刻＋元のファイル名として$imageに格納。
		move_uploaded_file(									 				// move_uploaded_file() で写真をアップロード。（一時的に保存されているフォルダからちゃんとしたフォルダに移動させる作業）
			$_FILES['image']['tmp_name'],									// ['tmp_name']とは、今一時的に保存されている場所。この後消えてしまう恐れある。
			'../member_picture/' . $image									// 保存フォルダの指定と、名前の指定。$imageの名前で member_pictureフォルダ に保存させる。
		);

		$_SESSION['join'] = $_POST;											// $_SESSION['join']=$_POSTで、セッションの'join'キーに$_POSTの値がそれぞれ保存されるという仕組み。例えば、nameを抜き出したい時は「$_SESSION['join']['name']」とする。
		$_SESSION['join']['image'] = $image;									//session-join-name となる。これを二次元配列と呼ぶ。
		header('Location: check.php');
		exit();
	}
}

	if ($_REQUEST['action'] == 'rewrite' && isset($_SESSION['join'])) {		//check.phpの書き直すボタンを押した時、という意味。check.phpでname='action'とし、URL自体をURL末尾に?action=rewriteとしている為。）
		$_POST = $_SESSION['join'];											
	}


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
			<p>次のフォームに必要事項をご記入ください。</p>
			<form action="" method="post" enctype="multipart/form-data">
				<!-- actionを空にしておくと、自分のページを再表示する事となる。エラーのチェックを本ページでする為。 -->
				<dl>
					<dt>ニックネーム<span class="required">必須</span></dt>
					<dd>
						<input type="text" name="name" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['name'], ENT_QUOTES)); ?>" />
						<?php if ($error['name'] === 'blank') : ?>
							<p class="error">* ニックネームを入力してください</p>
						<?php endif; ?>
					</dd>


					<dt>メールアドレス<span class="required">必須</span></dt>
					<dd>
						<input type="text" name="email" size="35" maxlength="255" value="<?php print(htmlspecialchars($_POST['email'], ENT_QUOTES)); ?>" />
						<?php if ($error['email'] === 'blank') : ?>
							<p class="error">* メールアドレスを入力してください</p>
						<?php endif; ?>
						<?php if ($error['email'] === 'duplicate') : ?>
							<p class="error">* 指定されたメールアドレスは既に登録されています</p>
						<?php endif; ?>


					<dt>パスワード<span class="required">必須</span></dt>
					<dd>
						<input type="password" name="password" size="10" maxlength="20" value="<?php print(htmlspecialchars($_POST['password'], ENT_QUOTES)); ?>" />
						<?php if ($error['password'] === 'length') : ?>
							<p class="error">* パスワードは4文字以上で入力してください</p>
						<?php endif; ?>

						<?php if ($error['password'] === 'blank') : ?>
							<p class="error">* パスワードを入力してください</p>
						<?php endif; ?>
					</dd>


					<dt>写真など</dt>
					<dd>
						<input type="file" name="image" size="35" value="test" />
						<?php if ($error['image'] === 'type') : ?>
							<p class="error">* 「.gif」または「.jpg」「.png」の画像を指定してください </p>
						<?php endif; ?>

						<?php if (!empty($error)) : ?>
							<p class="error">* 恐れ入りますが、画像を再度選択してください。</p>
						<?php endif; ?>
					</dd>


				</dl>
				<div><input type="submit" value="入力内容を確認する" /></div>
			</form>
		</div>
</body>

</html>