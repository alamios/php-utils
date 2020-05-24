<?php
    function startsWith($str, $startstr) { 
        $len = strlen($startstr); 
        return (substr($str, 0, $len) == $startstr); 
    } 

    function endsWith($str, $endstr) { 
        $len = strlen($endstr); 
        return $len == 0 || (substr($str, -$len) == $endstr); 
    } 

    function insertAt($str, $index, $insert) {
        return substr_replace($str, $insert, $index, 0);
    }

    function firstIndexOf($str, $target, $ignorecase=false) {
        return ($ignorecase) ? stripos($str, $target) : strpos($str, $target);
    }

    function lastIndexOf($str, $target, $ignorecase=false) {
        return ($ignorecase) ? strripos($str, $target) : strrpos($str, $target);
    }

    function clearTags($str) {
        return str_replace("<", "&lt;", str_replace(">", "&gt;", $str));
    }
?>