<?php
$dsn = 'データベース';//data source name
$user = 'ユーザー名';
$password = 'パスワード';
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

$sql = "CREATE TABLE IF NOT EXISTS tbtest"//IF NOT EXISTSを入れないと２回目以降に既に存在するテーブルを作成しようとした際に発生するエラーがでる
	." ("
	. "id INT AUTO_INCREMENT PRIMARY KEY,"//投稿番号が自動的に増える
	. "name char(32),"
	. "comment TEXT,"
	. "date datetime,"
	. "sub_pass varchar(15)"
	.");";
	$stmt = $pdo->query($sql);

	/*** テーブル削除用***/
// $sql = "DROP TABLE tbtest";
// $stmt = $pdo->query($sql);

	$name=isset($_POST["name"]) ? $_POST["name"] : null;
	$comment=isset($_POST["comment"]) ? $_POST["comment"] : null;
	$submit=isset($_POST["submit_button"]) ? $_POST["submit_button"] : null;
	$delete_num=isset($_POST["delete"]) ? $_POST["delete"] : null;
	$delete = isset($_POST["delete_button"]) ? $_POST["delete_button"] : null;
	$date = date("Y/m/d H:i:s");
	$now_edit=isset($_POST["now_edit"]) ? $_POST["now_edit"] : null;

//編集フォームの編集番号を格納
$edit_num = isset($_POST["edit"]) ? $_POST["edit"] : null;
$edit= isset ($_POST["edit_button"]) ? $_POST["edit_button"] : null;
//*編集番号が入る

//tbtestに存在するidを取得
$sql='SELECT id FROM tbtest';
$stmt=$pdo->query($sql);
$exist_id=$stmt->fetchAll(PDO::FETCH_COLUMN);

//パスワード認証
$sub_pass = isset($_POST["sub_pass"]) ? $_POST["sub_pass"] : null;
$del_pass= isset($_POST["del_pass"]) ? $_POST["del_pass"] : null;
$edit_pass=isset($_POST["edit_pass"]) ? $_POST["edit_pass"] : null;

//前のフェーズで編集番号フォームを入力した場合
//edit_flag 
//-> TRUE  > valueに表示。now_edit_numをPOSTして編集実行。
//-> FALSE > valueを格納しない。now_edit_numがPOSTされず、編集されない。

$edit_flag=FALSE;
if(isset($edit_num) and isset($edit)){
	//パスワード照合
	$sql = 'SELECT sub_pass FROM tbtest WHERE id=:edit_num AND sub_pass=:edit_pass';
	$stmt = $pdo->prepare($sql);
	$stmt -> bindParam(':edit_num', $edit_num, PDO::PARAM_STR);
    $stmt -> bindParam(':edit_pass', $edit_pass, PDO::PARAM_STR);
    $stmt -> execute();
    $results = $stmt->fetch(PDO::FETCH_ASSOC);

	
	if(!empty($results)){
		//編集フォームでのvalueを設定
		$sql='SELECT name, comment FROM tbtest WHERE id=:id';
		$stmt=$pdo->prepare($sql);
		$stmt -> bindParam(':id', $edit_num, PDO::PARAM_STR);
		$stmt -> execute();
		$results = $stmt->fetch(PDO::FETCH_ASSOC);
		print_r($results);
		$edit_flag=TRUE;
	}elseif(!in_array($edit_num, $exist_id)){
		echo "指定された編集指定番号は存在していません。<br>";
	}else{
		echo "パスワードが違います。<br>";
	}
}

//新規作成
$test=isset($submit) && empty($now_edit);
//var_dump($test);
if(isset($submit) && empty($now_edit)){
	$sql=$pdo->prepare("INSERT INTO tbtest(name,comment,date,sub_pass) VALUES(:name,:comment,:date,:sub_pass)");
	//↑SQLの準備、テーブル名のそれぞれに対してVALUESのように:nameと:valueというパラメータを与える
	//個々の値が変わっても難解でもこのSQLを使えるようになっている
	$sql -> bindParam(':name', $name, PDO::PARAM_STR);//:nameのパラメータに値を入れる
	// 1.パラメータを''内(bindParamのみ)に指定　2.入れる変数の指定※直接数値を入れない　３．型を指定。PDO::PARAM_STRは文字列だよ
	//ちなみに数値だったらbindValueでPDO::PARAM_INT 
	$sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
	$sql -> bindParam(':date', $date, PDO::PARAM_STR);
	$sql -> bindParam(':sub_pass', $sub_pass, PDO::PARAM_STR);
	$sql -> execute();//命令を実行する　prepareで用意したsqlをデータベースにINSERTしてる。これないと実行されない。prepareとexecuteはセット。
	
}

//編集投稿
elseif(isset($submit) and isset($now_edit)){
	//パスワード照合
	$sql = 'SELECT sub_pass FROM tbtest WHERE id=:now_edit AND sub_pass=:sub_pass';
	$stmt=$pdo->prepare($sql);
	$stmt -> bindParam(':now_edit', $now_edit, PDO::PARAM_STR);
	$stmt -> bindParam(':sub_pass',$sub_pass, PDO::PARAM_STR);
	$stmt -> execute();
	$results = $stmt->fetch(PDO::FETCH_ASSOC);//PDO::FETCH_ASSOC＝sqlの中を連想配列で取得するよ

	if(!empty($results)){
		$sql = 'UPDATE tbtest SET name=:name, comment=:comment, date=:date WHERE id=:id';
		$stmt = $pdo->prepare($sql);
		$stmt -> bindParam(':name', $name, PDO::PARAM_STR);
		$stmt -> bindParam(':comment', $comment, PDO::PARAM_STR);
		$stmt -> bindParam(':date', $date, PDO::PARAM_STR);
		$stmt -> bindParam(':id', $now_edit, PDO::PARAM_INT);
		$stmt -> execute();
		echo "編集が実行されました。<br>";	
	}else{
		echo "パスワードが違います。<br>";
	}
}
//投稿削除
if(isset($delete) and isset($delete_num)){
	$sql = 'SELECT sub_pass FROM tbtest WHERE id=:delete_num AND sub_pass=:del_pass';
	$stmt = $pdo->prepare($sql);
	$stmt -> bindParam(':delete_num', $delete_num, PDO::PARAM_STR);
	$stmt -> bindParam(':del_pass', $del_pass, PDO::PARAM_STR);
	$stmt -> execute();
	$results = $stmt->fetch(PDO::FETCH_ASSOC);

	if(!empty($results)){
		echo "削除が実行されます。<br>";
		$sql = 'DELETE FROM tbtest WHERE id=:id';
		$stmt = $pdo->prepare($sql);
		$stmt -> bindParam(':id',$delete_num, PDO::PARAM_INT);
		$stmt -> execute();
	}elseif(!in_array($delete_num, $exist_id)){
		echo "指定された削除対象番号は存在しません。<br>";
	}else{
		echo "パスワードが違います。<br>";
	}
}

?>

<html>
<head>
	<title>mission5</title>
	<meta charset="utf-8">
</head>
<body>
	<h2>投稿フォーム</h2>
	<form action="mission_5-1.php" method="post" >
	<p>名前<br/> 
	<input type="text" name="name" <?php if($edit_flag==TRUE){ echo "value='". $results['name'] ."'"; }?>>
	<p>コメント<br/>
	<input type="text" name="comment" <?php if($edit_flag==TRUE){ echo "value='". $results['comment']."'"; }?>>
	<p>パスワード<br/>
	<input type="password" name="sub_pass">
	<input type="hidden" name="now_edit" value="<?php if($edit_flag==TRUE){ echo $edit_num; }?>">
	<input type="submit" name="submit_button"value="送信">
	<hr>

	<h2>削除フォーム</h2>
	<p>投稿を削除<br/>
	<input type="text" name="delete" value="">
	<p>パスワード<br/>
	<input type="password" name="del_pass">
	<input type="submit" name="delete_button" value="削除">
	
	<hr>

	<h2>編集</h2> 
	<p>投稿を編集<br/>
	<input type="text" name="edit" value="">
	<p>パスワード<br/>
	<input type="password" name="edit_pass">
	<input type="submit" name="edit_button" value="編集"><br/>
	<input type="hidden" name="post_flag" value="1">
	</form>

<?php
//テーブル表示
$sql= 'SELECT * FROM tbtest';
$stmt = $pdo->query($sql);
$results = $stmt->fetchAll();
foreach($results as $row){
	echo $row['id'].',';
	echo $row['name'].',';
	echo $row['comment'].',';
	echo $row['date'].'<br>';
	echo ','.$row['sub_pass'].'<br>';
}
?>

</body>
</html>
