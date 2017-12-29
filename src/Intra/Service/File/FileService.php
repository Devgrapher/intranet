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

        return $this->convertPathToSignedS3Url($file['location'], $file['original_filename']);
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

        return $this->convertPathToSignedS3Url($file['location'], $file['original_filename']);
    }

    public function deleteFile(int $id)
    {
        $repo = new FileRepository();

        return $repo->deleteFile($id);
    }

    public function uploadFile(int $uploader_uid, string $key, string $upload_file_name, $file_content, string $content_type = null)
    {
        $group = $this->getGroupName();
        $repo = new FileRepository();
        $sub_key = $repo->countKey($group, $key) + 1;

        $s3_bucket = $_ENV['AWS_S3_BUCKET'];
        $ext = pathinfo($upload_file_name, PATHINFO_EXTENSION);

        $s3_prefix = $this->makeS3Prefix($group);
        $s3_filename = $this->makeS3FileName($key, (string)$sub_key, $ext);

        $s3_service = new Aws\S3();
        $s3_service->uploadToS3($s3_bucket, $s3_prefix . '/' . $s3_filename, $file_content, $content_type);

        return $repo->createFile($uploader_uid, $group, $key, $upload_file_name, $group . '/' . $s3_filename);
    }

    public function uploadFileWithZipped(int $uploader_uid, string $key, string $upload_file_name, string $file_path, string $content_type = null, string $password = null)
    {
        $zip_file_name = "$upload_file_name.zip";
        $file_dir = pathinfo($file_path, PATHINFO_DIRNAME);
        exec("mv $file_path $file_dir/$upload_file_name");
        if (isset($password)) {
            exec("cd $file_dir && zip -P $password $zip_file_name $upload_file_name");
        } else {
            exec("cd $file_dir && zip $zip_file_name $upload_file_name");
        }

        $result = $this->uploadFile($uploader_uid, $key, $zip_file_name, file_get_contents("$file_dir/$zip_file_name"), $content_type);
        unlink("$file_dir/$zip_file_name");

        return $result;
    }

    public function convertPathToSignedS3Url(string $path, string $filename = null): string
    {
        $pathinfo = explode('/', $path);
        $group = str_replace('.', '/', $pathinfo[0]);

        $s3_service = new Aws\S3();
        $s3_bucket = $_ENV['AWS_S3_BUCKET'];
        $s3_bucket_key = $group . '/' . $pathinfo[1];
        $options = [];
        if (!empty($filename)) {
            $options['ResponseContentDisposition'] = 'filename=' . urlencode($filename);
        }

        return $s3_service->getPreSignedUrl($s3_bucket, $s3_bucket_key, $options);
    }

    public function convertPathToS3Url(string $path): string
    {
        $pathinfo = explode('/', $path);
        $group = str_replace('.', '/', $pathinfo[0]);

        $s3_service = new Aws\S3();
        $s3_bucket = $_ENV['AWS_S3_BUCKET'];
        $s3_bucket_key = $group . '/' . $pathinfo[1];

        return $s3_service->getUrl($s3_bucket, $s3_bucket_key);
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
