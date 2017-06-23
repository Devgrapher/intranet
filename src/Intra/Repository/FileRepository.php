<?php
declare(strict_types=1);

namespace Intra\Repository;

class FileRepository extends Repository
{
    public function model()
    {
        return 'Intra\Model\FileModel';
    }

    public function getFiles(string $group, string $key)
    {
        $files = $this->find([
            'group' => $group,
            'key' => $key,
        ], [
            'uid',
            'original_filename',
            'location',
            'reg_date'
        ])->toArray();

        return $files;
    }

    public function getLastFile(string $group, string $key)
    {
        $file = $this->first([
            'group' => $group,
            'key' => $key,
        ], [
            'uid', 'original_filename', 'location', 'reg_date'
        ], 'reg_date', 'desc');

        return $file->toArray();
    }

    public function createFile(int $uid, string $group, string $key, string $original_filename, string $location)
    {
        return $this->create([
            'group' => $group,
            'uid' => $uid,
            'key' => $key,
            'original_filename' => $original_filename,
            'location' => $location,
        ]);
    }

    public function deleteFiles(string $group, string $key)
    {
        $files = $this->find([
            'group' => $group,
            'key' => $key
        ], ['id']);
        $this->delete($files->toArray());
    }

    public function deleteFile(int $id)
    {
        $file = $this->find([
            'id' => $id,
        ], ['id'])->first();
        return $this->delete($file->toArray());
    }

    public function countKey(string $group, string $key)
    {
        return $this->find([
            'group' => $group,
            'key' => $key
        ])->count();
    }
}
