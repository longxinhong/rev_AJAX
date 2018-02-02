<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/21
 * Time: 20:28
 */
//反向ajax
/*建表sql：create table lz_chat_log(
log_id int auto_increment primary key,
rec varchar (10) not null,
sender varchar(10) not null,
content text not null,
is_new tinyint not null DEFAULT 1)
*/

/*
ob_start();
echo str_repeat('',4096);
ob_end_flush();
ob_flush();
$i = 1 ;
while (true){
    echo $i++;
    ob_flush();
    flush();
    sleep(1);
}*/
set_time_limit(0);
require "./includes/mysql.php";
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

if ($act == '') {
    ob_start();
//echo str_repeat('',4096);
    ob_end_flush();
//ob_flush();
    while (true){
        $sql = "SELECT * FROM lz_chat_log where rec = 'admin' AND is_new = 1 ORDER BY log_id DESC";
        $res = mysql_query($sql, $link);
        while ($row = mysql_fetch_assoc($res)){
            $sql = 'UPDATE lz_chat_log SET is_new =  0 WHERE log_id = '.$row['log_id'];
            mysql_query($sql, $link);
            echo "<script>parent.showMsg(".json_encode($row).")</script>";
            ob_flush();
            flush();
            sleep(1);
        }

    }
} elseif ($act == 'sendMsg'){
    $content = htmlspecialchars($_POST['content']);
    $sql = "insert into lz_chat_log (rec, sender, content) VALUES ('user', 'admin', '$content')";
    mysql_query($sql, $link);
    die(json_encode(array('err'=>0)));
}
