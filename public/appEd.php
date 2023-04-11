<?php

$source = 'test.jpg';
$encrypt_file = 'test_enc.encry';
$decrypt_file = 'test_dec.jpg';
$key = 'D89475D32EA8BBE933DBD299599EEA3E';

echo '<p>source:</p>';
echo '<img src="'.$source.'" width="200">';
echo '<hr>';

file_encrypt($source, $encrypt_file, $key); // encrypt

echo '<p>encrypt file:</p>';
echo '<img src="'.$encrypt_file.'" width="200">';
echo '<hr>';

file_encrypt($encrypt_file, $decrypt_file, $key); // decrypt

echo '<p>decrypt file:</p>';
echo '<img src="'.$decrypt_file.'" width="200">';

/** 文件加密,使用key与原文异或生成密文,解密则再执行一次异或即可
 * @param String $source 要加密或解密的文件
 * @param String $dest   加密或解密后的文件
 * @param String $key    密钥
 */
function file_encrypt($source, $dest, $key){
    if(file_exists($source)){

        $content = '';          // 处理后的字符串
        $keylen = strlen($key); // 密钥长度
        $index = 0;
        //读取二进制流文件
        $fp = fopen($source, 'rb+');
        //判断是否到文件指针底部
        while(!feof($fp)){
            //每一次读取一个字符串
            $tmp = fread($fp, 1);
            //读取字符串后进行123异或加解密，组合成content
            $content .= $tmp ^ '123';

            $index++;
        }

        fclose($fp);
        //content写入目标文件
        return file_put_contents($dest, $content, true);

    }else{
        return false;
    }
}

?>