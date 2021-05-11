<?php
session_start();
require('dbconnect.php');

if($_COOKIE['email'] !== ''){
  $email = $_COOKIE['email'];
}

if (!empty($_POST)) {     //最初に訪れたログイン画面か、エラーが出て戻ってきた画面か、を判断するために、$_POSTを使用して、フォームに入力して送信したかどうか判断する。（何か入力して送信btnを押すと$_POSTに値が入って、空の配列じゃなくなる事から。）
  if ($_POST['email'] !== '' && $_POST['password'] !== '') {  //以下、メールアドレスとパスワードがtextareaに何かが入力されて送信されている時のみの処理を行う
    $email = $_POST['email']; //メールアドレス間違いでエラーが出た際、再度入力ミスしたアドレスを表示させるため。これを書かないとCookie登録されたアドレスがまた表示される。

    $login = $db->prepare('SELECT * FROM members WHERE email=? AND password=?'); 
    $login->execute(array(
      $_POST['email'],
      sha1($_POST['password']) //sha1で暗号化したパスワードは、解凍することができない。ただし、同じ文字列を入力すると同じ文字列に暗号化されるので、ここでもsha1を使用する。
    ));
    $member = $login->fetch(); //SQL検索で、＄_POSTされたメールアドレスとパスワードの同じデータがあれば、$memberはtrueになるという仕組み。



    if ($member) {  //$memberがtrueならば、つまり、メールアドレスとパスワードが一致すれば、、
      $_SESSION['id'] = $member['id'];  //ログインした事をページ移動後も記憶させたいので、SESSIONを使う。['id']はDBのidカラムから。
      $_SESSION['time'] = time();

      if($_POST['save'] === 'on'){  //ログイン情報記憶チェックボタンがオンになっている場合、
        setcookie('email',$_POST['email'], time()+60*60*24*14); //メールアドレスをCookieに保存（クッキー名,保存する値,保存期間）
      }

      header('Location:index.php'); //index.phpに遷移できる
      exit();
    } else {  //$memberが空の時。ログインに失敗している時。
      $error['login'] = 'failed';  //$errorの配列内のloginの値をfailedにする。
    }

  }else{
    $error['login'] = 'blank'; //フォームに何も入力されずに送信された場合、loginの値にblankを代入する。
  }
}
//  $login：全email,passwordのデータ
//  $member：一人のemail,passwordのデータ fetch()によって取り出された。
//  $login,$memberには、 email[] と password[] などのカラムの配列が入っている。
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <link rel="stylesheet" type="text/css" href="style.css" />
  <title>ログインする</title>
</head>

<body>
  <div id="wrap">
    <div id="head">
      <h1>ログインする</h1>
    </div>
    <div id="content">
      <div id="lead">
        <p>メールアドレスとパスワードを記入してログインしてください。</p>
        <p>入会手続きがまだの方はこちらからどうぞ。</p>
        <p>&raquo;<a href="join/">入会手続きをする</a></p>
      </div>
      <form action="" method="post">
        <dl>
          <dt>メールアドレス</dt>
          <dd>
            <input type="text" name="email" size="35" maxlength="255" value="<?php echo htmlspecialchars($email,ENT_QUOTES); ?>" /> <!-- エラーが起こった時に再度入力した情報が表示されるように、valueに（$_COOKIE['email']）を入れておく -->
            <?php if($error['login'] ==='blank'): ?>
              <p class="error">* メールアドレスとパスワードをご記入ください。</p>
            <?php endif; ?>
            <?php if($error['login'] ==='failed'): ?>
              <p class="error">* ログインに失敗しました。正しくご記入ください。</p>
            <?php endif; ?>
          </dd>
          <dt>パスワード</dt>
          <dd>
            <input type="password" name="password" size="35" maxlength="255" value="<?php echo htmlspecialchars($_POST['password'],ENT_QUOTES); ?>" />
          </dd>
          <dt>ログイン情報の記録</dt>
          <dd>
            <input id="save" type="checkbox" name="save" value="on"> <!--  $_POSTでsaveというキーでonという値が入っていればチェックが入っているという事になる  --> 
            <label for="save">次回からは自動的にログインする</label>
          </dd>
        </dl>
        <div>
          <input type="submit" value="ログインする" />
        </div>
      </form>
    </div>
    <div id="foot">
      <p><img src="images/txt_copyright.png" width="136" height="15" alt="(C) H2O Space. MYCOM" /></p>
    </div>
  </div>
</body>

</html>