<?php
namespace Intra\Model;

class LightFileModel
{
    private $dir;

    public function __construct($group)
    {
        $upload_dir = $_ENV['upload_dir'];
        if (!is_writable($upload_dir)) {
            throw new \Exception('!is_writable($upload_dir)');
        }
        $this->dir = $upload_dir . '/' . $group;
        if (!is_dir($this->dir) && is_file($this->dir)) {
            throw new \Exception('!is_dir($this->dir) && is_file($this->dir)');
        }
        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }
        if (!is_writable($this->dir)) {
            throw new \Exception('!is_writable($this->dir)');
        }
    }

    public function isExist($filename)
    {
        return file_exists($this->getLocation($filename));
    }

    /**
     * @param $filename
     * @return string
     */
    public function getLocation($filename)
    {
        return $this->dir . '/' . $filename;
    }

    public function makeDirectory($dirname)
    {
        if (is_dir($dirname)) {
            return;
        }
        mkdir($dirname, 0777, true);
    }

    public function getUploadableLocation($filename)
    {
        $return = $this->dir . '/' . $filename;
        $this->makeDirectory(dirname($return));

        return $return;
    }
}
