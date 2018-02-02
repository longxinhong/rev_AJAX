<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22
 * Time: 11:49
 */

require "includes/mysql.php";
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

if ($act == 'getMsg') {
    set_time_limit(0);
    while (true){
//        sleep(1);
        $sql = "SELECT * FROM lz_chat_log where rec = 'user' AND is_new = 1 ORDER BY log_id DESC";
        $res = mysql_query($sql, $link);
        while ($row = mysql_fetch_assoc($res)){
            $sql = 'UPDATE lz_chat_log SET is_new =  0 WHERE log_id = '.$row['log_id'];
            mysql_query($sql, $link);
            die(json_encode($row));
        }
    }
} elseif ($act == 'sendMsg') {
    $content = htmlspecialchars($_POST['content']);
    $sql = "insert into lz_chat_log (rec, sender, content) VALUES ('admin', 'user', '$content')";
    mysql_query($sql, $link);
    die(json_encode(array('err'=>0)));
} else {
    include "user.html";
}