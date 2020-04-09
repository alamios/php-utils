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

    function generateUnusedFilepath($path, $file) {
        $fname = $file;
        $i = 1;
        while (file_exists($loc = $path ."/". $fname)) {
            $pos = strrpos($file, ".");
            if (!$pos)
                $pos = strlen($fname);
            $fname = insertAt($file, $pos, "_(". ++$i .")");
        }
        return $loc;
    }

    function formatPath($path) {
        $path = urldecode($path);
        $path = str_replace("\\", "/", $path);
        $end = strlen($path) - 1;
        if ($path[$end] == "/")
            $path = substr($path, 0, $end);
        return $path;
    }

    function addToZip($path, $zip, $dir=null, $recursive=false) {
        if (!$dir) $dir = basename($path);
        $zip->addEmptyDir($dir);
        $scan = new ExtendedDirectoryIterator($path);
        foreach($scan as $file) {
            $fpath = $path . "/" . $file;
            if (!$file->isDot() && !$file->isLink()
                && !$file->isHidden() && !$file->isSystem()) {
                if ($file->isDir()) {
                    if ($recursive)
                        addToZip($fpath, $zip, $dir."/".$file, true);
                }
                else {
                    $comppath = $dir . "/" . $file;
                    $zip -> addFile($fpath, $comppath);
                    $zip -> setCompressionName($comppath, ZipArchive::CM_STORE);
                }
            }
        }
    }

    function getFiles($path, $recursive=false) {
        $files = [];
        $scan = new ExtendedDirectoryIterator($path);
        foreach($scan as $file) {
            if (!$file->isDot() && !$file->isLink()
                && !$file->isHidden() && !$file->isSystem()) {
                if ($file->isDir()) {
                    if ($recursive)
                        $files = array_merge($files, getFiles($file->getPathname(), true));
                }
                else {
                    $files[] = $file->getPathname();
                }
            }
        }
        return $files;
    }

    function getAliasRealPath($aliaspath, $confpath) { 
        $conf = file_get_contents($confpath);
        $aliaspath = formatPath($aliaspath);   
        $subpath = "";
        while (strlen($aliaspath)) {
            $aindex = strpos($conf, "Alias ".$aliaspath);
            if (!$aindex)
                $subpath = basename($aliaspath) ."/". $subpath;
            $nextdiv = strpos($aliaspath, basename($aliaspath))-1;
            $aliaspath = substr($aliaspath, 0, $nextdiv);
        }
        $qindex1 = strpos($conf, "\"", $aindex)+1;
        $qindex2 = strpos($conf, "\"", $qindex1);
        $qlen = $qindex2 - $qindex1;
        $root = substr($conf, $qindex1, $qlen);
        return formatPath($root ."/". $subpath);
    }

    function clearFiles($path, $startpatt, $endpatt) {
        $scan = scandir($path);
        foreach($scan as $file) {
            if (startsWith($file, $startpatt) && endsWith($file, $endpatt))
                unlink($path."/".$file);
        }
    }
    
    class ExtendedDirectoryIterator extends DirectoryIterator {
        function isLink() {
            return parent::isLink() || endsWith($this->getFilename(), ".lnk");
        }
        function isSystem() {
            $attr = trim(exec('FOR %A IN ("'.$this->getPathname().'") DO @ECHO %~aA'));
            return $attr[4] === 's';
        }
        function isHidden() {
            $attr = trim(exec('FOR %A IN ("'.$this->getPathname().'") DO @ECHO %~aA'));
            return $attr[3] === 'h';
        }
    }
?>