<?php
declare(strict_types=1);

namespace Intra\Service\File;

class UserImageFileService extends FileService
{
    protected function getGroupName(): string
    {
        return 'user_img';
    }

    public function createThumbFromFile(string $file_path, int $width, int $height)
    {
        if (!is_file($file_path)) {
            return false;
        }

        $image_type = exif_imagetype($file_path);
        if (!in_array($image_type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
            return false;
        }

        $source_image = null;
        switch ($image_type) {
            case IMAGETYPE_GIF:
                $source_image = imagecreatefromgif($file_path);
                break;
            case IMAGETYPE_JPEG:
                $source_image = imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_PNG:
                $source_image = imagecreatefrompng($file_path);
                break;
        }

        $width_origin = imagesx($source_image);
        $height_origin = imagesy($source_image);

        $virtual_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $width, $height, $width_origin, $height_origin); //사이즈 변경하여 복사

        ob_start();
        imagejpeg($virtual_image);
        return ob_get_clean();
    }
}
