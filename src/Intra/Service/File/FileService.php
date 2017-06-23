<?php
declare(strict_types=1);

namespace Intra\Service\File;

use Intra\Lib\Aws;
use Intra\Repository\FileRepository;

abstract class FileService
{
    abstract protected function getGroupName(): string;

    public function getFiles(string $key)
    {
        $repo = new FileRepository();
        return $repo->getFiles($this->getGroupName(), $key);
    }

    public function getLastFile(string $key)
    {
        $repo = new FileRepository();
        return $repo->getLastFile($this->getGroupName(), $key);
    }

    public function getFileWithId(int $id)
    {
        $repo = new FileRepository();
        return $repo->find([
            'id' => $id
        ])->first()->toArray();
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
        $s3_key = $this->makeS3Key($group, $key, (string)$sub_key, $file_name);
        $s3_service = new Aws\S3();
        $upload_url = $s3_service->uploadToS3($s3_bucket, $s3_key, $file_content, $content_type);

        return $repo->createFile($uploader_uid, $group, $key, $file_name, $upload_url);
    }

    private function makeS3Key(string $group, string $key, string $sub_key, string $original_filename)
    {
        $prefix = str_replace('.', '/', $group);
        $without_ext = $prefix . '/' . $key . '.' . $sub_key;
        $ext = pathinfo($original_filename, PATHINFO_EXTENSION);
        return  $ext ? $without_ext . '.' . $ext : $without_ext;
    }
}
