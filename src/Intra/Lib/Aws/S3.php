<?php
declare(strict_types=1);

namespace Intra\Lib\Aws;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;

class S3
{
    private $client;

    public function __construct(string $key = null, string $secret = null, string $region = null)
    {
        $this->client = S3Client::factory([
            'credentials' => [
                'key' => $key ? $key : $_ENV['AWS_ACCESS_KEY'],
                'secret' => $secret ? $secret : $_ENV['AWS_SECRET_KEY'],
            ],
            'region' => $region ? $region : $_ENV['AWS_REGION'],
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

    public function getPreSignedUrl(string $bucket, string $key, array $options = null, $expires = '+3 minutes'): string
    {
        $cmd_arg = [
            'Bucket' => $bucket,
            'Key' => $key,
        ];
        if (!empty($options)) {
            $cmd_arg = array_merge($cmd_arg, $options);
        }

        $cmd = $this->client->getCommand('GetObject', $cmd_arg);
        $request = $this->client->createPresignedRequest($cmd, $expires);

        return (string)$request->getUri();
    }

    public function getUrl(string $bucket, string $key): string
    {
        return $this->client->getObjectUrl($bucket, $key);
    }
}
