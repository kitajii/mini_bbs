<?php
session_start();
require('dbconnect.php');

//ログインしているかどうかの確認
if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) { //ログインしている条件として、login.phpの23,24行目の条件を用いる。
  $_SESSION['time'] = time(); //また、1時間経ったらログアウトする仕組みも作っている。

  $members = $db->prepare('SELECT * FROM members WHERE id=?'); //ログイン時にセッションに保存されたidとmembersのidデータを重ね合わせる作業
  $members->execute(array($_SESSION['id']));
  $member = $members->fetch();
} else {
  header('Location: login.php');  //ログインできていない状態でこの画面にアクセスされた時、ログイン画面にページ遷移する。
  exit();
}

//投稿機能
if (!empty($_POST)) { //投稿ボタンがクリックされた時に作動
  if ($_POST['message'] !== '') {  //textareaに文字が入っていたら作動
    $message = $db->prepare('INSERT INTO posts SET member_id=?, message=?, reply_message_id=?, created=NOW()');
    $message->execute(array(
      $member['id'],
      $_POST['message'], //DBに投稿した人、投稿内容、投稿時間を入力。$member['id']は$_SESSION['id']でも可だが、前者の方がより確実。
      $_POST['reply_post_id'] //誰に返信したかがわかるように。['reply_post_id']はhiddenのname属性値から。
    ));

    header('Location: index.php'); //投稿処理が終わった後、もう一度index.phpを呼び出す事で、$_POST['message']の値をリセットしている。（ページ更新で投稿を繰り返してしまう為）
    exit();
  }
}

//ページネーション

$page = $_REQUEST['page'];

if($page === ''){ //URLのページ指定値が空の時はページ数を1とする
  $page = 1;
}

$page = max($page,1); //URLのページ数に-1等のマイナス数を指定できないように、max関数を指定。$pageと1を比べて、1が大きい時は1を入れる。1以下にはならない。

//最終ページの設定
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');  //投稿数の取得 $cntが投稿数
$cnt = $counts->fetch();

$maxPage = ceil($cnt['cnt']/5);  //投稿件数を5で割ってceilで切り上げる。例えば、15件の時：3ページ　8件の時：2ページとなる。最終ページが何ページかわかる。
$page = min($page, $maxPage); //$maxPage以上のページ数を指定されても$maxPage以上にならないようにする。

$start = ($page -1) *5; 



//メッセージの一覧
//投稿を取得する
$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.created DESC LIMIT ?,5');

$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

//返信
if (isset($_REQUEST['res'])) { //URLのresがキーとなる為、'res'というキーにidの要素が入っている場合、つまりReボタンがクリックされた場合。
  //返信の処理 まずはDBにそのidが存在するかどうかを確認する
  $response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m,posts p WHERE m.id=p.member_id AND p.id=?');
  $response->execute(array($_REQUEST['res']));

  $table = $response->fetch();
  $message = '@' . $table['name'] . ' ' . $table['message'];
}

?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>ひとこと掲示板</title>

  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div id="wrap">
    <div id="head">
      <h1>ひとこと掲示板</h1>
    </div>
    <div id="content">
      <div style="text-align: right"><a href="logout.php">ログアウト</a></div>
      <form action="" method="post">
        <dl>
          <dt><?php print(htmlspecialchars($member['name'], ENT_QUOTES)); ?>さん、メッセージをどうぞ</dt>
          <dd>
            <textarea name="message" cols="50" rows="5"><?php print(htmlspecialchars($message, ENT_QUOTES)); ?></textarea>
            <input type="hidden" name="reply_post_id" value="<?php print(htmlspecialchars($_REQUEST['res'], ENT_QUOTES)); ?>" />
          </dd>
        </dl>
        <div>
          <p>
            <input type="submit" value="投稿する" />
          </p>
        </div>
      </form>
      <?php foreach ($posts as $post) : ?>
        <div class="msg">
          <img src="member_picture/<?php print(htmlspecialchars($post['picture'], ENT_QUOTES)); ?>" width="48" height="48" alt="<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>" />
          <p><?php print(htmlspecialchars($post['message'], ENT_QUOTES)); ?><span class="name">（<?php print(htmlspecialchars($post['name'], ENT_QUOTES)); ?>）</span>[<a href="index.php?res=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>">Re</a>]</p>
          <!--投稿自体のidをURLに表示する。-->
          <p class="day"><a href="view.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>"><?php print(htmlspecialchars($post['created'], ENT_QUOTES)); ?></a>
          <?php if($post['reply_message_id'] > 0): ?><!-- 返信の投稿じゃなければ、reply_message_idは0になるので、0以上の時のみリンクを表示する。 -->
            <a href="view.php?id=<?php print(htmlspecialchars($post['reply_message_id'], ENT_QUOTES)); ?>">
              返信元のメッセージ</a>
          <?php endif; ?>
          <?php if($_SESSION['id'] === $post['member_id']): ?>
            [<a href="delete.php?id=<?php print(htmlspecialchars($post['id'], ENT_QUOTES)); ?>" style="color: #F33;">削除</a>]
          <?php endif; ?>
          </p>
        </div>
      <?php endforeach; ?>

      <ul class="paging">
        <?php if($page > 1): ?>
        <li><a href="index.php?page=<?php print($page-1);?>">前のページへ</a></li>
        <?php else: ?>
        <li>前のページへ</li>
        <?php endif;?>
        <?php if($page < $maxPage): ?>
        <li><a href="index.php?page=<?php print($page+1);?>">次のページへ</a></li>
        <?php else: ?>
        <li>次のページへ</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</body>

</html>