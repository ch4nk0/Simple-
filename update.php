<?php
date_default_timezone_set('Asia/Tokyo');
$time = new DateTime("now");
$now = $time->format('Y-m-d')."T".$time->format('H:i');
$errors = array();
$update_success="";//成功,失敗コメント用
if(isset($_POST['submit']) && $_POST['submit'] === "更新"){
  $id = htmlspecialchars($_POST['id'], ENT_QUOTES);
  $taskname = htmlspecialchars($_POST['taskname'], ENT_QUOTES);
  $priority = htmlspecialchars($_POST['priority'], ENT_QUOTES);
  $deadline = htmlspecialchars($_POST['deadline'], ENT_QUOTES);
  $memo = htmlspecialchars($_POST['memo'], ENT_QUOTES);

  if($taskname === ""){
    $errors['taskname'] = "タスク名が入力されていません。";
  }
  if($priority === ""){
    $errors['priority'] = "優先度が選択されていません。";
  }
  if($deadline === ""){
    $errors['deadline'] = "〆切が入力されていません。";
  }
  else   if(!preg_match("/^(20)[0-9]{2}\-[0-9]{1,2}\-[0-9]{1,2}[T](0[0-9]|1[0-9]|2[0-3]):(0[0-9]|1[0-9]|2[0-9]|3[0-9]|4[0-9]|5[0-9])$/", $deadline)){//dateteime-local未対応ブラウザ用の正規表現
    $errors['deadline'] = "〆切が正しく入力されていません。(お手数ですが時間をフォーマット通りに入力するか、HTML5対応ブラウザ(Chrome最新版など)でお使いください。";
  }
  else if(date("Y-m-d H:i:s") > $deadline){
    //dateteime-local未対応ブラウザ用の対策:〆切は現在時刻の後のみ
    $errors['deadline'] = "〆切が現在時刻よりも前に設定されています。";
  }
  if(count($errors) === 0){
    $pdo=new PDO('mysql:host=DB_HOST;dbname=DB_NAME;charset=utf8','DB_USERNAME','DB_PASSWORD');
    $sql=$pdo->prepare('update todo set taskname=?, memo=?, deadline=?,priority=? where id=?');
    if($sql->execute([$taskname,$memo,$deadline,$priority,$id])){
      $update_success="<i class='fa fa-check-circle-o green' aria-hidden='true'></i> 編集に成功しました。<a href='all.php'>(更新)</a>";
    }else{
      $update_success="編集に失敗しました。";
    }
  }
}
?>
<h5><?php echo $update_success; ?></h5>
<div class="ichiran">
  <h3>編集</h3>
  <button type="button" class="btn btn-primary" id="editopen">+ 開く</button>
  <button type="button" class="btn btn-primary" id="editclose">- 閉じる</button>
  <tr style="padding-top:10px;">
    <?php
    echo "<ul class='message'>";
    foreach($errors as $message){
      echo "<li>";
      echo "<i class='fa fa-exclamation-triangle' aria-hidden='true'></i> ";
      echo $message;
      echo "</li>";
    }
    echo "</ul>";
    ?>
  </tr>
  <div id="edit">
    <table class="all-table">
      <tr>
        <th>タスク名</th>
        <th>優先度</th>
        <th>〆切</th>
        <th width="250">メモ</th>
        <th>更新</th>
      </tr>
      <?php
      $pdo=new PDO('mysql:host=localhost;dbname=myapp;charset=utf8','staff','rofdnyyb');
      foreach($pdo -> query('select * from todo')as $row){//登録順
        //タスク名
        echo '<tr>';
        echo '<form action="all.php" method="post">';
        echo '<td>';
        echo '<input type="text" name="taskname" value="',$row['taskname'],'">';
        echo '</td>';
        //優先度
        $check=array("","","");
        if($row['priority']=="3")$check[0]="checked";
        else if($row['priority']=="2")$check[1]="checked";
        else if($row['priority']=="1")$check[2]="checked";
        echo '<td>';
        echo '<span><input type="radio" name="priority" value="3" '.$check[0].'>高</span>';
        echo '<span><input type="radio" name="priority" value="2" '.$check[1].'>中</span>';
        echo '<span><input type="radio" name="priority" value="1" '.$check[2].'>低</span>';
        echo '</td>';
        //〆切
        $deadline = substr($row['deadline'], 0, -9)."T".substr($row['deadline'], 11, -3);//値をフォームに入れるために日と時間の間にTを挿入
        echo '<td>';
        echo '<input type="datetime-local" name="deadline" min="',$now,'" value="',$deadline,'">','</td>';
        //メモ
        echo '<td width="250">';
        echo '<input type="text" name="memo" value="',$row['memo'],'">';
        echo '</td>';
        echo '<input type="hidden" name="id" value="',$row['id'],'">';
        echo '<td><input type="submit" name="submit" value="更新"></td>';
        echo '</form>';
        echo '</tr>';
        echo "\n";
      }
      ?>
    </table>
  </div>
</div>
