<?php
declare(strict_types=1);

namespace Intra\Service\File;

class UserImageFileService extends FileService
{
    const THUMBNAIL_WIDTH = 300;
    const THUMBNAIL_HEIGHT = 300;
    const THUMBNAIL_JPG_QUALITY = 90;

    protected function getGroupName(): string
    {
        return 'user_img';
    }

    public function createThumbFromFile(string $file_path, int $width = self::THUMBNAIL_WIDTH, int $height = self::THUMBNAIL_HEIGHT)
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

        $width_new = $width;
        $height_new = intval($width_new * $height_origin / $width_origin);
        if ($width_new > $width || $height_new > $height) {
            $height_new = $height;
            $width_new = intval($height_new * $width_origin / $height_origin);
        }

        $virtual_image = imagecreatetruecolor($width_new, $height_new);
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $width_new, $height_new, $width_origin, $height_origin); //사이즈 변경하여 복사

        ob_start();
        imagejpeg($virtual_image, null, self::THUMBNAIL_JPG_QUALITY);
        return ob_get_clean();
    }
}
