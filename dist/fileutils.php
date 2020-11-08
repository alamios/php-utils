<?php
    require_once("textutils.php");
    
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
                    $files[] = escape($file->getPathname());
                }
            }
        }
        return $files;
    }

    function escape($path) {
        return str_replace("\\", "/", $path);
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