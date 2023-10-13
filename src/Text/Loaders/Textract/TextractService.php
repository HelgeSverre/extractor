<?php

namespace HelgeSverre\Extractor\Text\Loaders\Textract;

use Aws\Textract\TextractClient;
use HelgeSverre\Extractor\Text\Loaders\Textract\Data\Response;
use HelgeSverre\Extractor\Text\Loaders\Textract\Data\S3Object;
use HelgeSverre\Extractor\Text\Loaders\Textract\Exceptions\TextractFailed;
use HelgeSverre\Extractor\Text\Loaders\Textract\Exceptions\TextractTimedOut;
use HelgeSverre\Extractor\Text\Loaders\Textract\Exceptions\TextractUnhandledStatus;
use Illuminate\Support\Arr;

class TextractService
{
    public function __construct(protected TextractClient $textractClient)
    {
    }

    /**
     * @throws TextractFailed
     * @throws TextractTimedOut
     * @throws TextractUnhandledStatus
     */
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
        $finalText = ''; // Variable to accumulate the final result

        while (true) {
            $elapsedTime = time() - $startTime;

            if ($elapsedTime >= $timeoutInSeconds) {
                throw new TextractTimedOut('Textract job timed out.');
            }

            if ($elapsedTime > 0) {
                sleep($pollingIntervalInSeconds);
            }

            $nextToken = null;
            do {
                $response = $this->textractClient->getDocumentTextDetection([
                    'JobId' => $jobId,
                    'NextToken' => $nextToken,
                ]);

                $rawStatus = Arr::get($response, 'JobStatus');
                $status = Status::tryFrom($rawStatus);

                switch ($status) {
                    case Status::statusSucceeded:
                        $finalText .= Response::fromAwsResult($response)?->text();
                        $nextToken = Arr::get($response, 'NextToken'); // Set the nextToken if it exists
                        if (! $nextToken) { // if there's no next token, we break
                            break 2; // Break the outer loop
                        }
                        break;

                    case Status::statusInProgress:
                        $nextToken = null; // Reset the token
                        break;

                    case Status::statusPartialSuccess:
                    case Status::statusFailed:
                        throw new TextractFailed("Textract job failed with status '$rawStatus'.");
                    default:
                        throw new TextractUnhandledStatus("Unhandled Textract job status: '$rawStatus'.");
                }
            } while ($nextToken);
        }

        return $finalText; // Return the concatenated result
    }

    public function bytesToText(string $content): ?string
    {
        $result = $this->textractClient->detectDocumentText([
            'Document' => [
                'Bytes' => $content,
            ],
        ]);

        return Response::fromAwsResult($result)?->text();
    }
}
