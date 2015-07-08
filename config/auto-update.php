<?php

return
    [
        'base_dir'    => base_path(),

        /*
        |--------------------------------------------------------------------------
        | Git Private Key
        |--------------------------------------------------------------------------
        |
        |
        |
        */

        'private-key' =>
            [
                'file' => __DIR__ . '/../priv.key',
            ],

        /*
        |--------------------------------------------------------------------------
        | Storage Settings
        |--------------------------------------------------------------------------
        |
        |
        |
        */

        'storage'     =>
            [
                'tmp-dir'     => storage_path( 'auto-update/tmp/' ),
                'tmp-file'    => 'test.json',

                'update-dir'  => storage_path( 'auto-update/update/' ),
                'update-file' => 'update.zip',
            ]
    ];