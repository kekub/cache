<?php

namespace kevinkub\Cache;

class Cache
{
    private static $cacheFolder;
    private static $cacheContent;

    public static function setCacheFolder($folder)
    {
        if(!is_dir($folder))
        {
            throw new \Exception("Folder {$folder} does not exist.");
        }
        if(!is_writeable($folder))
        {
            throw new \Exception("Folder {$folder} is not writeable.");
        }
        if(substr($folder, -1) !== '/')
        {
            $folder .= '/';
        }

        self::$cacheFolder = $folder;
    }

    public static function store($key, $fn, $lifeTime = -1)
    {
        $filePath = self::getFilePathForKey($key);

        $isCached = false;
        if(file_exists($filePath))
        {
            $isCached = require $filePath;
        }
        if($isCached)
        {
            return self::$cacheContent;
        }
        else
        {
            if($lifeTime !== -1)
            {
                $lifeTime = @strtotime($lifeTime);
            }
            $result = $fn();
            $code = sprintf('<?php if(!method_exists("%s","retrieve")) throw new \\Exception("Tried to load cache file without loading cache."); return %s::retrieve(%s, %s);', str_replace("\\", "\\\\", __CLASS__), __CLASS__, $lifeTime, var_export($result, true));
            file_put_contents($filePath, $code);
            return $result;
        }
    }

    public static function trash($key)
    {
        return @unlink(self::getFilePathForKey($key));
    }

    public static function trashAll()
    {
        $status = true;
        foreach((array) glob(self::getFilePathForKey('*')) as $file)
        {
            if(@unlink($file) === false) {
                $status = false;
            }
        }
        return $status;
    }

    public static function clean()
    {
        foreach((array) glob(self::getFilePathForKey('*')) as $file)
        {
            if(!(require $file))
            {
                @unlink($file);
            }
        }
    }


    private static function getFilePathForKey($key)
    {
        if(self::$cacheFolder === null) throw new \Exception("Cache folder is not set. You need to set it by calling " . __CLASS__ . "::setCacheFolder('path/to/folder');");
        $escapedKey = preg_replace('/[^A-Za-z0-9_\-]/', '_', $key);
        return self::$cacheFolder . $escapedKey . '.php';
    }

    public static function retrieve($timeToDie, $content)
    {
        self::$cacheContent = null;
        if($timeToDie > time() || $timeToDie == -1) {
            self::$cacheContent = $content;
            return true;
        } else {
            return false;
        }
    }
}
