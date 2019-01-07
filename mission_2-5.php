<!DOCTYPE html>
<html>
<head>
	<title>mission_2-5.php</title>
</head>
<body>
<?php 

$filename = "mission_2-5.txt";
//ファイルが存在しない場合
if (!(file_exists($filename))){
	$fp = fopen($filename , "a");
	fwrite($fp , "");
	fclose($fp);
	echo "<p>新しくテキストファイルが作成されました。</p>"; 
}
 // 変数定義

// 名前とテキストとパスワードを取得
$name = $_POST["name"];
$text = $_POST["text"];
$input_password = $_POST["input_password"]; // フォームから受け取ったパスワード
$correct_password = "mission"; //正しいパスワード

// 削除用変数、
$del_index = $_POST["del_index"];

// 編集用変数
$edit_index = $_POST["edit_index"]; // 編集用番号を取得
$edited_name = $_POST["edited_name"]; //編集された名前を取得
$edited_text = $_POST["edited_text"]; // 編集されたコメントを取得
$edit_comp = ""; //編集完了されてない場合の空白
$edit_message = ""; //編集画面でない場合の空白

// 未入力時のエラーメッセージ
$error_name = "※名前を入力してください<br>";
$error_text = "※コメントを入力してください<br>";
$error_both = "※名前とコメントを入力してください<br>";
$error_edit = "※編集せずに最初のページに戻りました<br>";

$hello = ""; //ウェルカムメッセージ（初訪問時のみ）

// フォームの属性nameの定義（これは投稿用、編集モード用はスイッチの中）
$form_title = "投稿フォーム";
$form_name = "name";
$form_text = "text";
$form_submit = "toukou_submit";
$form_value = "投稿する";

// $del_data = $_POST["del_data"]; //データ削除ボタン

// テキストファイルの投稿を1行ごとに配列にする
$toukou_datas = file($filename);

$indexs = array(); //空のダミー配列
foreach ( $toukou_datas as $toukou_data) {
	$index = explode("<>" , $toukou_data);
	array_push($indexs , $index[0]); //投稿番号を配列に追加
}

// submitの値を取得（bool）
$toukou_submit = $_POST["toukou_submit"];
$del_submit = $_POST["del_submit"];
$edit_submit = $_POST["edit_submit"];
$edited_submit = $_POST["edited_submit"];

$page_flag = "welcome"; //初期値

if (isset($input_password) && !$input_password == ""){ //パスワードが入力されていて
	if($input_password == $correct_password){//パスワードが一致したら
		if(isset($toukou_submit)){ //パスワードが正しい場合のみ、フォームに分岐
			$page_flag = 1; //投稿フォーム
		}elseif(isset($del_submit)){
			$page_flag = 2; //削除フォーム
		}elseif(isset($edit_submit)){
			$page_flag = 3; //編集フォーム
		}
	}
}else{//何かしら（正しくない）パスワードが入力されたら
		$page_flag = "incorrect_password";
	}
if (isset($edited_submit)){
	$page_flag = 4; // パスワード入力されていないが、編集完了フォーム（パスワードなし）
}elseif ($input_password == ""){ //空白の場合
	$page_flag = "empty_password";
}

// #################   case1  投稿   ##################
switch ($page_flag) { //$page_flagの値で分岐

case "welcome": //初めて訪れた場合
	$hello = "<h1>掲示板へようこそ！！！</h1>";
	break;

case "incorrect_password"://$error_passwordの値を変更してエラー表示する
	echo "<p>※パスワードが違います</p>";
break; 

case "empty_password":
	echo "<p>※パスワードを入力してください</p>";
break;

case 1: 
// 名前、コメント両方入力されている場合
if (!empty($name) && !empty($text)){
	
	if(count(file($filename)) == 0){
		$max_row = 1; //初回のみ、1と設定
	}else{
		$max_row = max($indexs);
		$max_row++; // ファイルの行数を取得、+1。インデックス番号
	}

	$submittime = date("Y/m/d H:i:s"); //投稿時刻
	// 投稿された文字列
	$toukou = $max_row."<>".$name."<>".$text."<>".$submittime.$password;

	// テキストファイルに書き込み
	$fp = fopen($filename , "a");
	fwrite($fp , $toukou);
	fclose($fp);

	// 入力完了のアナウンス
    echo "<h2>テキストファイルに<br>「";
    echo htmlspecialchars($toukou);
    echo "」<br>と入力されました</h2>";

}else{ //エラーメッセージ
	if(empty($name) && empty($text)){
		echo $error_both;
	}elseif(empty($name)){
		echo $error_name;
	}elseif(empty($text)){
		echo $error_text;
	}
}
break;

// #################   case2  削除　 ##########################

case 2:
	// 半角数字かつ投稿番号内に存在したら
if (is_numeric($del_index) && in_array($del_index , $indexs))
{
		// 一旦削除（空白を書き込み）
		$fp = fopen($filename , "w");
		fwrite($fp , ""); //空文字を書き込み
		fclose($fp);

  		foreach ($toukou_datas as $toukou_data){
  			// 行頭のインデックス番号を取得
			$index = explode("<>" , $toukou_data);
			$index = $index[0];			

  			// 投稿番号と削除番号が一致したら
  			if($index == $del_index){
  				echo "<h2>「".$toukou_data."」を削除しました</h2><br>";
  			}else{ // 追記モードで書き込み
  				file_put_contents($filename , $toukou_data , FILE_APPEND );
  			}
  		} // foreach終わり

// 半角数字だけど、削除できない番号の場合 
}elseif (is_numeric($del_index)){ 
	echo "<p>※削除番号が適切ではありません。投稿履歴の左端にある数字を入力してください。</p>";
}elseif (empty($del_index)){ 
	echo "<p>※削除する番号を入力してください<p>";//空欄だったら、エラーメッセージ
}else{echo "<p>※半角数字を入力してください</p>";} // 半角数字以外が入力されたら

break;

	// #####################   case3 編集  ##################
case 3:
	// 半角数字かつ投稿番号内に存在するなら
	if (is_numeric($edit_index) && in_array($edit_index , $indexs)){
		$edit_message = "<p>名前とコメントを編集してください</p><br>";

		// 編集フォームのform属性を変更
		$form_title = "編集画面";
		$form_name = "edited_name";
		$form_text = "edited_text";
		$form_submit = "edited_submit";
		$form_value = "編集する";

		foreach($toukou_datas as $no => $toukou_data){
			$array_toukou = explode("<>" , $toukou_data); // 左端の行番号を取得
			if($edit_index == $array_toukou[0]){ //編集番号と左端の番号が一致したら

				$edit_name = $array_toukou[1]; //編集する名前
				$edit_text = $array_toukou[2]; //編集するコメント
				break;
			}
		}	
	// 半角数字だけど、投稿番号内に存在しないなら 
	}elseif(is_numeric($edit_index)){
		echo "<p>※編集できる番号ではありません。左端の番号を入力してください</p>";
	}elseif(empty($edit_index)){ // 半角数字以外なら 
		echo "<p>※編集番号を入力してください</p>";
	}else{echo "<p>※半角数字を入力してください</p>";} //空欄の場合、エラーメッセージ

break;

	// ###################     case4 編集完了   ####################
case 4: //編集完了してボタンが押されたら
	if (!empty($edited_name) && !empty($edited_text)){ //名前、コメント共に入力されたら
		$edited_index = $_POST["edited_index"]; //hiddenから受け取った編集番号
		$edited_toukou = $edited_index."<>".$edited_name."<>".$edited_text."<>".date("Y/m/d H:i:s")."\n"; //編集された投稿内容
		file_put_contents($filename , ""); // 空にする

		foreach($toukou_datas as $toukou_data){
			$index = explode("<>" , $toukou_data); //左端の投稿番号を取得
			if($edited_index == $index[0]){
				$edit_comp =  "<h3>".$edited_index."番目の投稿<br>「".$toukou_data."」<br>を<br>「".$edited_toukou."」<br>に編集しました</h3>"; //編集完了のメッセージ
				file_put_contents($filename , $edited_toukou , FILE_APPEND); //編集データを書き込み
			}else{
				file_put_contents($filename , $toukou_data , FILE_APPEND); //上書き保存
			}
		}
	}elseif(empty($edited_name) && empty($edited_text)){ //名前、コメント共に空欄だったら
		echo $error_both;
		echo $error_edit;
	}elseif(empty($name)){
		echo $error_name;
		echo $error_edit;
	}elseif(empty($text)){
		echo $error_text;
		echo $error_edit;
	}
// file_put_contents は基本FILE_APPENDをつける

break;

// ####################    defalt エラー   ###################
default:
	echo "※エラーです。正常に処理できませんでした";
	break;
}

?>

<?php //特別なメッセージ
echo $edit_comp; //編集完了したらメッセージ、しなかったら空白
echo $hello; //case 0 （初訪問）でウェルカムメッセージ、0以外なら空白
?>

<!-- HTML表示部分 -->
<p>-------------------------------------------------------</p>
<!-- 投稿か編集かで属性、タイトルを変更 -->
 	<h3><?= $form_title ?></h3>
 	<?= $edit_message; ?>
	<form action="mission_2-5.php" method="post"> 
		<p>名前：<input type="text" name="<?= $form_name // 短縮echo?>" value="<?= $edit_name ?>" placeholder="名前"></p>
		<p>コメント：<input type="text" name="<?= $form_text // 短縮echo?>" value="<?= $edit_text ?>" placeholder="コメント"></p>
		<?php if ($page_flag != 3){//編集モードでない場合
			echo "<p>パスワード：<input type='password' name='input_password'></p>";
		} ?> <!-- 投稿フォームと編集画面でパスワードの表示・非表示　-->
	<input type="submit" name="<?= $form_submit //短縮echo?>" value="<?= $form_value //短縮echo?>">
	<!-- 編集番号をcaseでも使えるように隠して送る（hidden） -->
	<input type="hidden" name="edited_index" value="<?= $edit_index //短縮echo ?>"> 
	</form>
<p>-------------------------------------------------------</p>

	<h3>削除フォーム</h3>
	<p>※半角数字</p>

	<form action="mission_2-5.php" method="post">
		<p>削除数字：<input type="text" name="del_index" placeholder="削除したい数字"></p>
		<p>パスワード：<input type="password" name="input_password"></p>
		<input type="submit" name="del_submit" value="削除する">
	</form>
<p>-------------------------------------------------------</p>

	<h3>編集フォーム</h3>
	<p>※半角数字</p>

	<form action="mission_2-5.php" method="post">
		<p>編集数字：<input type="text" name="edit_index" placeholder="編集したい数字"></p>
		<p>パスワード：<input type="password" name="input_password"></p>
		<input type="submit" name="edit_submit" value="編集する">
	</form>
<p>-------------------------------------------------------</p>

	<!-- MySQLのテーブル（データを全削除する） -->
	<!-- <form action="mission_2-5.php" method="post">
		<input type="submit" name="del_data" value="データを削除">
	</form> -->

<!-- 履歴表示 -->
<h4>テキストファイルの確認は<a href="mission_2-5.txt">こちら</a>へ</h4>
<h3>投稿履歴</h3>
<?php //履歴表示

if (count(file($filename)) != 0){ //テキストファイルが０行でないなら
	$histrys = file($filename);

 	foreach ($histrys as $histry) {
  		echo str_replace("<>" , " " , $histry)."<br>"; //1行ずつ表示
	} 
}else{echo "<p>※まだテキストファイルに書き込まれていないため、履歴はありません</p>";}
?>
</body>
</html>