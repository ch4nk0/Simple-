<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Simple My ToDo App</title>
  <link rel="stylesheet" href="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/css/bootstrap.css">
  <link rel="stylesheet" type="text/css" href="index.css" >
  <!-- FontAwesomeの読み込み -->
  <link rel="stylesheet" type="text/css" href="./font-awesome-4.7.0/css/font-awesome.css">
  <script src="https://code.jquery.com/jquery-2.2.3.js"></script>
  <script src="https://cdn.rawgit.com/twbs/bootstrap/v4-dev/dist/js/bootstrap.js"></script>
</head>
<body>
  <h1><i class="fa fa-pencil" aria-hidden="true" style="color:#003300"></i>  <span style="color:#6495ED;">Simple</span>×予定管理</h1>
  <div class="all">
    <?php
    date_default_timezone_set('Asia/Tokyo');
    $time = new DateTime("now");
    $now = $time->format('Y/m/d');
    $week=array("","","","","","","");
    $pdo=new PDO('mysql:host=DB_HOST;dbname=DB_NAME;charset=utf8','DB_USERNAME','DB_PASSWORD');
    foreach($pdo -> query('select * from todo')as $row){//週表を出すための準備
      for($i=0;$i<7;$i++){//今日から一週間分を考える
        if(date("Y-m-d", strtotime($i."day")) == substr($row['deadline'], 0, -9)){//文字列で一致するか比較する
          if($week[$i]==""){
            $week[$i]=$row['taskname'];//もしDBの中に日付と一致するものがあれば配列に格納
          }else{
            $week[$i]=$week[$i]." , ".$row['taskname'];//複数ある場合は区切る
          }
        }
      }
    }
    ?>
    <div class="ichiran" id="all">
      <h3>一覧</h3>
      <h4 class="clock"></h4>
      <h6>登録順   <a href="all.php?sort=ASC&karamu=id" class="tri-white">▼</a><a href="all.php?sort=DESC&karamu=id"class="tri-white">▲</a></h6>
      <table class="all-table">
        <tr>
          <th>警告</th>
          <th>チェック</th>
          <th>タスク名</th>
          <th>優先度  <a href="all.php?sort=ASC&karamu=priority" class="tri-white">▼</a><a href="all.php?sort=DESC&karamu=priority" class="tri-white">▲</a></th>
          <th>〆切  <a href="all.php?sort=ASC&karamu=deadline" class="tri-white">▼</a><a href="all.php?sort=DESC&karamu=deadline" class="tri-white">▲</a></th>
          <th width="250">メモ</th>
          <th>削除</th>
        </tr>
        <?php
        if(isset($_POST['command'])){
          switch($_POST['command']){//コマンドで処理を分岐
            case 'check':
            $sql=$pdo->prepare('update todo set checkbox=? where id=?');
            $sql->execute([$_REQUEST['checkbox'],$_REQUEST['id']]);
            break;
            case 'delete':
            $sql=$pdo->prepare('delete from todo where id=?');
            $sql->execute([$_REQUEST['id']]);
            break;
          }
        }
        //初アクセスは登録順で降順(登録が古い順番から)
        if (!(isset($_REQUEST["sort"]))) {//初回アクセス時
          $sort="ASC";
        }else{
          $sort=mysql_escape_string($_REQUEST['sort']);//昇順降順用
        }
        if (!(isset($_REQUEST["karamu"]))) {//初回アクセス時
          $karamu="id";
        }else{
          $karamu=mysql_escape_string($_REQUEST['karamu']);//項目用
        }

        foreach($pdo -> query('select * from todo order by '.$karamu.' '.$sort.','.'deadline ASC')as $row){
          //もし優先度で並べ替えた場合は〆切が早い方にする
          echo '<tr>';
          $flag=0;//警告旗
          if(date("Y-m-d H:i") > substr($row['deadline'], 0, -3)){
            //〆切を過ぎたものにはボムを出す
            $flag=2;
          }else if(date("Y-m-d H:i", strtotime("1 day")) > substr($row['deadline'], 0, -3)){
            //あと一日を切ったタスクは旗を出す
            $flag=1;
          }else if(date("Y-m-d H:i", strtotime("2 day")) > substr($row['deadline'], 0, -3)){
            if($row['priority']==3){
              //あと2日を切った優先度高のタスクは旗を出す
              $flag=1;
            }
          }
          echo '<td>';
          if($flag==1){
            $dead_datetime = new DateTime(substr($row['deadline'], 0, -3));
            $interval = $time->diff($dead_datetime);
            echo "<span><i class='fa fa-flag' aria-hidden='true'></i> あと";
            if($interval->format('%d')==0) echo $interval->format('%h');
            else echo $interval->format('1日%h');
            echo"時間</span>";
          }else if($flag==2){
            echo "<span><i class='fa fa-bomb' aria-hidden='true' style='color:#FF3366'></i>早く！</span>";
          }
          echo '</td>';
          //check
          echo '<form action="all.php" method="post">';
          echo '<input type="hidden" name="command" value="check">';
          if($row['checkbox']==0){
            echo '<input type="hidden" name="checkbox" value="1">';
            echo '<input type="hidden" name="id" value="',$row['id'],'">';
            echo '<td><input class="submit_button_0" type="submit" value="&#xf00c;"></td>';
          }else if($row['checkbox']==1){
            echo '<input type="hidden" name="checkbox" value="0">';
            echo '<input type="hidden" name="id" value="',$row['id'],'">';
            echo '<td><input class="submit_button_1" type="submit" value="&#xf00c;"></td>';
          }
          echo '</form>';
          echo '<td>',htmlspecialchars($row['taskname']),'</td>';
          if($row['priority']=="3"){
            $priority="<span style='color:red;'><b>高</b></span>";
          }else if($row['priority']=="2"){
            $priority="<span style='color:blue;'><b>中</b></span>";
          }else{
            $priority="<span><b>低</b></span>";
          }
          echo '<td>',$priority,'</td>';
          $deadline = substr($row['deadline'], 0, -3);//文字の切り落としで秒を削除
          echo '<td>',htmlspecialchars($deadline),'</td>';
          echo '<td width="250">',htmlspecialchars($row['memo']),'</td>';
          echo '<form action="all.php" method="post">';
          echo '<input type="hidden" name="command" value="delete">';
          echo '<input type="hidden" name="id" value="',$row['id'],'">';
          echo '<td><input type="submit" value="削除"></td>';
          echo '</form>';
          echo '</tr>';
          echo "\n";
        }
        ?>
      </table>
    </div>
    <?php require 'update.php'; ?>
    <div class="week">
      <h3>今日から一週間の予定</h3>
      <table class="all-table">
        <tr>
          <th width="40">日付</th>
          <th width="230">タスク名</th>
        </tr>
        <?php
        $weekjp = array(
          '日', //0
          '月', //1
          '火', //2
          '水', //3
          '木', //4
          '金', //5
          '土'  //6
        );
        for($i=0;$i<7;$i++){
          echo '<tr>';
          echo '<td width="40">'.date("m/d", strtotime($i."day")).'('.$weekjp[date("w", strtotime($i."day"))].')'.'</td>';
          echo '<td width="230">'.$week[$i].'</td>';
          echo '</tr>';
        }
        ?>
      </table>
    </div>
    <?php require 'add.php'; ?>
  </div>
  <script src="js/jquery-2.1.1.min.js"></script>
  <script src="./index.js"></script>
</body>
</html>
