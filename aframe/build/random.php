<?php
//random 字母和数字
require_once './../framework/bootstrap.inc.php';
$salt = mt_rand(0,25);
$str = strtoupper("abcdefghijklmnopqrstuvwxyz");
var_dump(mt_rand(0,100));
exit($str[$salt]);
// Random rd=new Random();
//         int m =0;//生成0-26的随机数
//         String n = "";
//         for(int i = 0;i<4;i++){
//             String str="abcdefghijklmnopqrstuvwxyz";
//             m = rd.nextInt(26);
//             n = n+str.charAt(m);
//         }
//         System.out.println("n:"+n);
?>