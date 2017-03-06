<?php
date_default_timezone_set('Asia/Tokyo');
$time = new DateTime("now");
$now = $time->format('Y-m-d')."T".$time->format('H:i');
$errors = array();
$add_success="";//成功,失敗コメント用

if(isset($_POST['submit']) && $_POST['submit'] === "追加"){
  $taskname = $_POST['taskname'];
  $priority = $_POST['priority'];
  $deadline = $_POST['deadline'];
  $memo = $_POST['memo'];

  $taskname = htmlspecialchars($taskname, ENT_QUOTES);
  $priority = htmlspecialchars($priority, ENT_QUOTES);
  $deadline = htmlspecialchars($deadline, ENT_QUOTES);
  $memo = htmlspecialchars($memo, ENT_QUOTES);
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
    $sql=$pdo->prepare('insert into todo values(null,0,?,?,?,?)');
    if($sql->execute([$taskname,$memo,$deadline,$priority])){
      $add_success="<i class='fa fa-check-circle-o green' aria-hidden='true'></i> 追加に成功しました。<a href='all.php'>(更新)</a>";
      unset($taskname);
      unset($memo);
      unset($deadline);
      unset($priority);
    }else{
      $add_success="追加に失敗しました。";
    }
  }
}
?>
<div class="add">
  <h3 id="#addlink">新規タスク追加</h3>
  <h5><?php echo $add_success; ?></h5>
  <span class="asta">*</span> がついている欄は必須事項です。
  <table class="add-table">
    <form action="all.php" method="post">
      <tr style="padding-top:15px;">
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
      <tr>
        <td><b>タスク名<span class="asta"> *</span></b></td>
        <td width="450"><input type="text" name="taskname" value="<?php if(isset($taskname)){echo $taskname;}?>"></td>
      </tr>
      <tr>
        <td><b>優先度<span class="asta"> *</span></b></td>
        <td>
          <?php
          //フォームで未入力の場合は「高」のところに、再入力する際には入力してた場所に印を置くための設定
          $check=array("","","");
          if(isset($priority)){
            if($priority=="3")$check[0]="checked";
            else if($priority=="2")$check[1]="checked";
            else if($priority=="1")$check[2]="checked";
            else $check[0]="checked";
          }
          ?>
          <span><input type="radio" name="priority" value="3" <?php echo $check[0] ?>>高</span>
          <span><input type="radio" name="priority" value="2" <?php echo $check[1] ?>>中</span>
          <span><input type="radio" name="priority" value="1" <?php echo $check[2] ?>>低</span>
        </td>
      </tr>
      <tr>
        <td><b>〆切<span class="asta"> *</span></b></td>
        <td width="400"><input type="datetime-local" name="deadline" min="<?php echo $now; ?>" value="<?php if(isset($deadline)){echo $deadline;}?>"><br>※HTML5未対応ブラウザ用日時の書き方<br>例：2017-04-06T09:30</td>
      </tr>
      <tr>
        <td><b>メモ<b></td>
          <td row="2"><textarea name="memo" value="<?php if(isset($memo)){echo $memo;}?>"></textarea></td>
        </tr>
        <tr class="backwhite">
          <td class="backwhite"><input type="submit" name="submit" value="追加"></td>
        </tr>
      </form>
    </table>
  </div>
