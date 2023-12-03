<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Textract Timeout
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum amount of time (in seconds) the application
    | should wait for the Textract process to complete before giving up.
    | It's especially useful for avoiding long-running processes.
    |
    */

    'textract_timeout' => env('TEXTRACT_TIMEOUT', 60),

    /*
    |--------------------------------------------------------------------------
    | Textract Polling Interval
    |--------------------------------------------------------------------------
    |
    | This value sets the time interval (in seconds) at which the application
    | should check the status of a running Textract job.
    | This allows for a controlled and paced polling mechanism.
    |
    */

    'textract_polling_interval' => env('TEXTRACT_POLLING_INTERVAL', 5),

    /*
    |--------------------------------------------------------------------------
    | Textract Disk
    |--------------------------------------------------------------------------
    |
    | Specifies the storage disk to be used when uploading files for Textract.
    | Make sure the disk is properly configured in your filesystems configuration.
    |
    */

    'textract_disk' => env('TEXTRACT_DISK'),

    /*
    |--------------------------------------------------------------------------
    | Textract Region, Version, Key, and Secret
    |--------------------------------------------------------------------------
    |
    | These settings are related to the AWS Textract configuration. They define
    | the region where Textract is being used, the version of the Textract API,
    | and the access key and secret key for authentication purposes.
    |
    */

    'textract_region' => env('TEXTRACT_REGION'),
    'textract_version' => env('TEXTRACT_VERSION'),
    'textract_key' => env('TEXTRACT_KEY'),
    'textract_secret' => env('TEXTRACT_SECRET', '2018-06-27'),

];
