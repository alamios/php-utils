<?php
    require_once("fileutils.php");
    if (!isset($_SESSION)) session_start(); 

    function setLang($langRelPath="resources/strings", $langExt="ini") {
        $absPath = $_SERVER['DOCUMENT_ROOT'] . "/" . $langRelPath . "/";
        $supported = getSupportedLangs($absPath);
        $selected = getSelectedLang();
        $preferred = getPreferredLangs();
        $choose = chooseLang($supported, $selected, $preferred);
        $_SESSION["lang"] = parse_ini_file($absPath . $choose . "." . $langExt, true, INI_SCANNER_RAW);
        $_SESSION["lang"]["selected"] = $choose;
        $_SESSION["lang"]["available"] = $supported;
    }

    function getSupportedLangs($langPath) { 
        $langFiles = getFiles($langPath);
        $langs = [];
        foreach ($langFiles as $lfile) {
            $p = explode("/", $lfile);
            $langs[] = explode(".", $p[count($p)-1])[0];
        }
        return $langs;
    }

    function getPreferredLangs() {
        $langs = [];
        foreach (explode(",", $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $p) {
            $langs[] = explode(";", $p)[0];
        }
        return $langs;
    }

    function getSelectedLang() {
        if (isset($_COOKIE["lang"]))
            return htmlspecialchars($_COOKIE["lang"]);
        return false;
    }
    
    function chooseLang($supported, $selected, $preferred) {
        if ($selected && in_array($selected, $supported)) {
            return $selected;
        }
        else {
            foreach ($preferred as $pref) {
                if (in_array($pref, $supported)) {
                    return $pref;
                }
            }
        }
        return $supported[0];
    }
?>