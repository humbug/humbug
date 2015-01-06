<?php
/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 */

namespace Humbug\StreamWrapper;

class Mutator
{

    protected $fp;

    protected static $mutant;

    public static function capture($mutant)
    {
        self::$mutant = $mutant;
        if (in_array('file', stream_get_wrappers())) {
            stream_wrapper_unregister('file');
        }
        stream_wrapper_register('file', __CLASS__);
    }

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        if ($path == self::$mutant || realpath($path) == self::$mutant) {
            $path = self::$mutant;
        }
        stream_wrapper_restore('file');
        $this->fp = fopen($path, $mode, $options);
        stream_wrapper_unregister('file');
        stream_wrapper_register('file', __CLASS__);
        return !empty($this->fp);
    }

    public function stream_read($count)
    {
        return fread($this->fp, $count);
    }

    public function stream_write($data)
    {
        return fwrite($this->fp, $data);
    }

    public function stream_tell()
    {
        return ftell($this->fp);
    }

    public function stream_eof()
    {
        return feof($this->fp);
    }

    public function stream_seek($offset, $whence)
    {
        return fseek($this->fp, $offset, $whence);
    }

    public function stream_stat()
    {
        return fstat($this->fp);
    }

    public function stream_lock($op)
    {
        return flock($this->fp, $op);
    }
    
    public function stream_truncate($new_size)
    {
        return ftruncate($this->fp, $new_size);
    }

    public function dir_opendir($path, $options)
    {
        stream_wrapper_restore('file');
        $this->fp = opendir($path);
        stream_wrapper_unregister('file');
        stream_wrapper_register('file', __CLASS__);
        return true;
    }

    public function dir_readdir()
    {
        return readdir($this->fp);
    }

    public function mkdir($path, $mode = 0777, $recursive = false, $context = null)
    {
        stream_wrapper_restore('file');
        if (!file_exists($path)) {
            mkdir($path, $mode, $recursive);
        }
        stream_wrapper_unregister('file');
        stream_wrapper_register('file', __CLASS__);
        return true;
    }

    public function rmdir($path, $options)
    {
        stream_wrapper_restore('file');
        $i = rmdir($path);
        stream_wrapper_unregister('file');
        stream_wrapper_register('file', __CLASS__);
        return $i;
    }

    public function unlink($path)
    {
        stream_wrapper_restore('file');
        $i = unlink($path);
        stream_wrapper_unregister('file');
        stream_wrapper_register('file', __CLASS__);
        return $i;
    }

    public function url_stat($path)
    {
        stream_wrapper_restore('file');
        $i = false;
        if (file_exists($path)) {
            $fp = fopen($path, 'r');
            if ($fp) {
                $i = fstat($fp);
            }
        }
        stream_wrapper_unregister('file');
        stream_wrapper_register('file', __CLASS__);
        return $i;
    }

}