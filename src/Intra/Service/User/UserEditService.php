<?php
namespace Intra\Service\User;

use Intra\Model\LightFileModel;
use Intra\Model\UserModel;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Class UserEditService
 */
class UserEditService
{
    public static function getImageLocation($uid)
    {
        $file_model = new LightFileModel('user_img');

        return $file_model->getUploadableLocation($uid);
    }

    public static function getThumbLocation($uid)
    {
        $file_model = new LightFileModel('user_img');

        return $file_model->getUploadableLocation($uid . '.' . 'jpeg');
    }

    public static function saveImage($uid, File $uploadedFile)
    {
        $dest = self::getImageLocation($uid);
        if ($uploadedFile->move(dirname($dest), basename($dest))) {
            return $dest;
        }

        return null;
    }

    public static function createThumb($uid, $width, $height)
    {
        $source = self::getImageLocation($uid);
        if (!is_file($source)) {
            return false;
        }

        $image_type = exif_imagetype($source);
        if (!in_array($image_type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG])) {
            return false;
        }

        $source_image = null;
        switch ($image_type) {
            case IMAGETYPE_GIF:
                $source_image = imagecreatefromgif($source);
                break;
            case IMAGETYPE_JPEG:
                $source_image = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $source_image = imagecreatefrompng($source);
                break;
        }

        $width_origin = imagesx($source_image);
        $height_origin = imagesy($source_image);

        $virtual_image = imagecreatetruecolor($width, $height);
        imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $width, $height, $width_origin, $height_origin); //사이즈 변경하여 복사

        $dest = self::getThumbLocation($uid);

        return imagejpeg($virtual_image, $dest);
    }

    /**
     * @param $key
     * @return null|string
     */
    public static function getImage($key)
    {
        $file_model = new LightFileModel('user_img');
        $dest = $file_model->getUploadableLocation($key);
        if (is_file($dest)) {
            return $dest;
        }

        return null;
    }

    /**
     * @param $uid
     * @param $key
     * @param $value
     * @return null|string
     */
    public static function updateInfo($uid, $key, $value)
    {
        $self = UserSession::getSelfDto();
        if (!$self) {
            return null;
        }

        if ($self->uid != $uid
            && !UserSession::isUserManager()) {
            return null;
        }

        UserModel::update($uid, [$key => $value]);

        $user_dto = UserDtoFactory::createByUid($uid);

        return $user_dto->$key;
    }
}
