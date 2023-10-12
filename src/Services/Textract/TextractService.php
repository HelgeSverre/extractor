<?php

namespace HelgeSverre\Extractor\Services\Textract;

use Aws\Textract\TextractClient;
use Exception;
use HelgeSverre\Extractor\Services\Textract\Data\S3Object;
use HelgeSverre\Extractor\Services\Textract\Data\TextractResponse;
use Illuminate\Support\Arr;

class TextractService
{
    protected const STATUS_IN_PROGRESS = 'IN_PROGRESS';

    protected const STATUS_PARTIAL_SUCCESS = 'PARTIAL_SUCCESS';

    protected const STATUS_FAILED = 'FAILED';

    protected const STATUS_SUCCEEDED = 'SUCCEEDED';

    public function __construct(protected TextractClient $textractClient)
    {
    }

    public function s3ObjectToText(S3Object $s3Object, int $timeoutInSeconds = 60, int $pollingIntervalInSeconds = 1): ?string
    {
        $result = $this->textractClient->startDocumentTextDetection([
            'ClientRequestToken' => $s3Object->getClientRequestToken(),
            'DocumentLocation' => [
                'S3Object' => array_filter([
                    'Bucket' => $s3Object->bucket,
                    'Name' => $s3Object->name,
                    'Version' => $s3Object->version,
                ]),
            ],
        ]);

        $jobId = Arr::get($result, 'JobId');

        if (! $jobId) {
            return null;
        }

        $startTime = time();

        while (true) {
            $elapsedTime = time() - $startTime;

            if ($elapsedTime >= $timeoutInSeconds) {
                throw new Exception('Textract job timed out.');
            }

            if ($elapsedTime > 0) {
                sleep($pollingIntervalInSeconds);
            }

            $response = $this->textractClient->getDocumentTextDetection(['JobId' => $jobId]);
            $status = Arr::get($response, 'JobStatus');

            switch ($status) {
                case self::STATUS_SUCCEEDED:
                    return TextractResponse::fromAwsResult($response)?->getText();

                case self::STATUS_IN_PROGRESS:
                    continue 2;

                case self::STATUS_PARTIAL_SUCCESS:
                case self::STATUS_FAILED:
                    throw new Exception("Textract job failed with status '$status'.");
                default:
                    throw new Exception("Unhandled Textract job status: '$status'.");
            }
        }
    }

    public function bytesToText(string $content): ?string
    {
        $result = $this->textractClient->detectDocumentText([
            'Document' => [
                'Bytes' => $content,
            ],
        ]);

        return TextractResponse::fromAwsResult($result)?->getText();
    }
}
