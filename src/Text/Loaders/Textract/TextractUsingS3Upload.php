<?php

namespace HelgeSverre\Extractor\Text\Loaders\Textract;

use HelgeSverre\Extractor\Contracts\TextLoader;
use HelgeSverre\Extractor\Text\Loaders\Textract\Data\S3Object;
use HelgeSverre\Extractor\Text\Loaders\Textract\Exceptions\TextractConfigNotFoundException;
use HelgeSverre\Extractor\Text\Loaders\Textract\Exceptions\TextractFailed;
use HelgeSverre\Extractor\Text\Loaders\Textract\Exceptions\TextractStorageException;
use HelgeSverre\Extractor\Text\Loaders\Textract\Exceptions\TextractTimedOut;
use HelgeSverre\Extractor\Text\Loaders\Textract\Exceptions\TextractUnhandledStatus;
use HelgeSverre\Extractor\Text\TextContent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TextractUsingS3Upload implements TextLoader
{
    protected static mixed $generateFilePathUsing = null;

    public function __construct(protected TextractService $textractService)
    {
    }

    public static function generateFilePathUsing(callable $callback): void
    {
        static::$generateFilePathUsing = $callback;
    }

    public function defaultFilePathGenerator(): string
    {
        return sprintf('extractor/%s.pdf', Str::uuid());
    }

    public function getFilePath(): string
    {
        if (static::$generateFilePathUsing) {
            return (static::$generateFilePathUsing)();
        }

        return $this->defaultFilePathGenerator();
    }

    /**
     * @throws TextractTimedOut
     * @throws TextractConfigNotFoundException
     * @throws TextractFailed
     * @throws TextractUnhandledStatus
     * @throws TextractStorageException
     */
    public function load(mixed $data): ?TextContent
    {
        $disk = config($diskConfigPath = 'extractor.textract_disk')
            ?: throw new TextractConfigNotFoundException(
                "Config '$diskConfigPath' is not set, it is required for OCR-ing PDFs"
            );

        $bucket = config($configPath = sprintf('filesystems.disks.%s.bucket', $disk))
            ?: throw new TextractConfigNotFoundException(
                "Bucket is not defined in disk '$configPath'"
            );

        $path = $this->getFilePath();

        $content = $data instanceof UploadedFile ? $data->getContent() : $data;

        $wasStored = Storage::disk($disk)->put($path, $content);

        if (! $wasStored) {
            throw new TextractStorageException("Could not create the file in the textract s3 bucket with path '{$path}'.");
        }

        return new TextContent(
            $this->textractService->s3ObjectToText(
                s3Object: new S3Object(bucket: $bucket, name: $path),
                timeoutInSeconds: config('extractor.textract_timeout'),
                pollingIntervalInSeconds: config('extractor.textract_polling_interval')
            )
        );
    }
}
