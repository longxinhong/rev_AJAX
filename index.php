<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/22
 * Time: 15:07
 */

//try{
//    $dsn='mysql:host=localhost;dbname=luzhu';
//    $username='root';
//    $passwd='root';
//    $pdo=new PDO($dsn, $username, $passwd);
//    var_dump($pdo);
//}catch(PDOException $e){
//    echo $e->getMessage();
//}

session_start();
echo "sessionId:".session_id();
echo "<br>";
var_dump($_SESSION);


echo "<br>";

var_dump($_COOKIE);

unset($_COOKIE['PHPSESSID']);