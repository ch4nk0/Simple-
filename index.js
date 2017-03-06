
$(function(){
  //以下編集のボタン切り替え
  $("#edit").css("display", "none");
  $("#editclose").css("display", "none");
  $('#editopen').click(function(){
    $('#edit').toggle();
    $('#editclose').toggle();
    $("#editopen").css("display", "none");
  });
  $('#editclose').click(function(){
    $("#edit").css("display", "none");
    $('#editopen').toggle();
    $("#editclose").css("display", "none");
  });
  //以下時計
  jQuery.extend({
    clock : function clock(target){
      var dayOfTheWeek = new Array("日","月","火","水","木","金","土");
      var now  = new Date();
      var year = now.getFullYear();
      var month = now.getMonth()+1;
      var date = now.getDate();
      var day = now.getDay();
      var hour = now.getHours();
      var min = now.getMinutes();
      if(month < 10)month = "0" + month;
      if(date < 10)date = "0" + date;
      if(hour < 10)hour = "0" + hour;
      if(min < 10)min = "0" + min;
      var time_str = year + "/" + month + "/" + date + "(" + dayOfTheWeek[day] + ") " + hour + ":" + min;
      // htmlの内容を更新
      target.text(time_str);
      // 1000ミリ秒（1秒）毎に更新
      setTimeout(function(){
        clock(target)
      },1000);
    }
  });
  // 現在日時を表示します。
  jQuery.clock(jQuery(".clock"));
});
