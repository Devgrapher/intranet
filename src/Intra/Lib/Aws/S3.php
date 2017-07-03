<?php
declare(strict_types=1);

namespace Intra\Lib\Aws;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3
{
    const AWS_ACCESS_KEY_ENV = 'aws_access_key';
    const AWS_SECRET_KEY_ENV = 'aws_secret_key';
    const AWS_REGION_ENV = 'aws_region';

    private $client;

    public function __construct(string $key = null, string $secret = null, string $region = null)
    {
        $this->client = S3Client::factory([
            'credentials' => [
                'key' => $key ? $key : $_ENV[self::AWS_ACCESS_KEY_ENV],
                'secret' => $secret ? $secret : $_ENV[self::AWS_SECRET_KEY_ENV],
            ],
            'region' => $region ? $region : $_ENV[self::AWS_REGION_ENV],
            'version' => '2006-03-01',
        ]);
    }

    public function uploadToS3(string $bucket, string $key, $body, string $content_type = null): string
    {
        try {
            $put_object_arg = [
                'Bucket' => $bucket,
                'ACL' => 'private',
                'Key' => $key,
                'Body' => $body,
            ];

            if ($content_type) {
                $put_object_arg['ContentType'] = $content_type;
            }
            $result = $this->client->putObject($put_object_arg);

            return $result['ObjectURL'];
        } catch (S3Exception $e) {
            echo $e->getMessage() . "\n";
        }
    }
}
