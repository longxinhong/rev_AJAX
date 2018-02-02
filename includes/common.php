<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/26
 * Time: 11:38
 */

/**
 * @param $a = '/a/b/c/d/e.php';
 * @param  $b = '/a/b/12/34/c.php';
 * @return string 计算出 $b 相对于 $a 的相对路径应该是 ../../c/d将()添上
 */
function getRelativePath($a, $b) {
    $returnPath = array(dirname($b));
    $arrA = explode('/', $a);
    $arrB = explode('/', $returnPath[0]);
    for ($n = 1, $len = count($arrB); $n < $len; $n++) {
        if ($arrA[$n] != $arrB[$n]) {
            break;
        }
    }
    if ($len - $n > 0) {
        $returnPath = array_merge($returnPath, array_fill(1, $len - $n, '..'));
    }

    $returnPath = array_merge($returnPath, array_slice($arrA, $n));
    return implode('/', $returnPath);
}

/**
 * @param $string
 * @param $start
 * @param $length
 * @return string 实现中文字串截取无乱码的方法。
 */
function GBsubstr($string, $start, $length) {
    if(strlen($string)>$length){
        $str=null;
        $len=$start+$length;
        for($i=$start;$i<$len;$i++){
            if(ord(substr($string,$i,1))>0xa0){
                $str.=substr($string,$i,2);
                $i++;
            }else{
                $str.=substr($string,$i,1);
            }
        }
        return $str.'...';
    }else{
        return $string;
    }
}

/**
 * @param $dir
 * @return array 写一个函数，能够遍历一个文件夹下的所有文件和子文件夹
 */
function my_scandir($dir)
{
    $files = array();
    if ( $handle = opendir($dir) ) {
        while ( ($file = readdir($handle)) !== false ) {
            if ( $file != ".." && $file != "." ) {
                if ( is_dir($dir . "/" . $file) ) {
                    $files[$file] = scandir($dir . "/" . $file);
                }else {
                    $files[] = $file;
                }
            }
        }
        closedir($handle);
        return $files;
    }
}