<?php
declare(strict_types=1);

namespace Intra\Service\File;

use Intra\Lib\Aws;
use Intra\Repository\FileRepository;

abstract class FileService
{
    abstract protected function getGroupName(): string;

    public function getLastFileLocation(string $key): string
    {
        $repo = new FileRepository();
        $file = $repo->getLastFile($this->getGroupName(), $key);
        if (!isset($file) || !isset($file['location'])) {
            return '';
        }

        return $this->convertPathToS3($file['location']);
    }

    public function getFileLocation(int $file_id): string
    {
        $repo = new FileRepository();
        $file = $repo->find([
            'id' => $file_id
        ])->first()->toArray();
        if (!isset($file) || !isset($file['location'])) {
            return '';
        }

        return $this->convertPathToS3($file['location']);
    }

    public function deleteFile(int $id)
    {
        $repo = new FileRepository();
        return $repo->deleteFile($id);
    }

    public function uploadFile(int $uploader_uid, string $key, string $file_name, $file_content, string $content_type = null)
    {
        $group = $this->getGroupName();
        $repo = new FileRepository();
        $sub_key = $repo->countKey($group, $key) + 1;

        $s3_bucket = $_ENV['aws_s3_bucket'];
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);

        $s3_prefix = $this->makeS3Prefix($group);
        $s3_filename = $this->makeS3FileName($key, (string)$sub_key, $ext);

        $s3_service = new Aws\S3();
        $s3_service->uploadToS3($s3_bucket, $s3_prefix . '/' . $s3_filename, $file_content, $content_type);
        return $repo->createFile($uploader_uid, $group, $key, $file_name, $group . '/' . $s3_filename);
    }

    private function convertPathToS3(string $path): string
    {
        $pathinfo = explode('/', $path);
        $group = str_replace('.', '/', $pathinfo[0]);
        return 'https://' . $_ENV['aws_s3_bucket'] . '.s3.amazonaws.com/' . $group . '/' . $pathinfo[1];
    }

    private function makeS3Prefix(string $group): string
    {
        return $prefix = str_replace('.', '/', $group);
    }

    private function makeS3FileName(string $key, string $sub_key, string $ext): string
    {
        $file_name = $key . '.' . $sub_key;
        return  $ext ? $file_name . '.' . $ext : $file_name;
    }
}
