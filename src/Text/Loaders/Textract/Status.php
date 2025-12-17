<?php

declare(strict_types=1);

namespace HelgeSverre\Extractor\Text\Loaders\Textract;

enum Status: string
{
    case statusInProgress = 'IN_PROGRESS';
    case statusPartialSuccess = 'PARTIAL_SUCCESS';
    case statusFailed = 'FAILED';
    case statusSucceeded = 'SUCCEEDED';
}
