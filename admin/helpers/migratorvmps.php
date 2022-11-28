<?php

defined('_JEXEC') or die;

class MigratorvmpsHelper
{
    /**
    * удалить директорию со всеми вложенными файлами
    * @param $dir
    * @return bool
    */
    public static function deleteDir($dir)
    {
        $files = array_diff(scandir($dir), ['.','..']);
        foreach ($files as $file) {
            (is_dir($dir.'/'.$file)) ? self::deleteDir($dir.'/'.$file) : unlink($dir.'/'.$file);
        }
        if ($dir === JPATH_COMPONENT_ADMINISTRATOR . '/prestashop_module/migratorvmps/images') {
            return true;
        } else {
            return rmdir($dir);
        }
    }

    public static function ZipFull($src_dir, $archive_path)
    {
        $zip = new ZipArchive();
        if ($zip->open($archive_path, ZIPARCHIVE::CREATE) !== true) {
            return false;
        }
        $zip = self::ZipDirectory($src_dir, $zip);
        $zip->close();

        header("Content-type: application/zip;\n");
        header("Content-Transfer-Encoding: Binary");
        header("Content-length: ".filesize($archive_path).";\n");
        header("Content-disposition: attachment; filename=\"".basename($archive_path)."\"");
        readfile($archive_path);
        unlink($archive_path);
        exit();
    }

    private static function ZipDirectory($src_dir, $zip, $dir_in_archive='')
    {
        // $src_dir = str_replace("\\","/",$src_dir);
        //$dir_in_archive = str_replace("\\","/",$dir_in_archive);
        $dirHandle = opendir($src_dir);
        while (false !== ($file = readdir($dirHandle))) {
            if (($file != '.')&&($file != '..')) {
                if (!is_dir($src_dir.$file)) {
                    $zip->addFile($src_dir.$file, $dir_in_archive.$file);
                } else {
                    $zip->addEmptyDir($dir_in_archive.$file);
                    $zip = self::ZipDirectory(
                        $src_dir . $file.DIRECTORY_SEPARATOR,
                        $zip,
                        $dir_in_archive . $file.DIRECTORY_SEPARATOR
                    );
                }
            }
        }

        return $zip;
    }
}
