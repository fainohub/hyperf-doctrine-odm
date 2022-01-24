<?php

declare(strict_types=1);

return [
    'connection' => [
        'server' => env('MONGO_DB_DSN', 'mongodb://mongodb:27017'),
        'database' => env('MONGO_DB_DATABASE', 'db'),
    ],

    'hydrator' => [
        'namespace' => 'MongoDbHydrator',
        'path' => BASE_PATH . '/cache/MongoDbHydrators',
    ],

    'proxy' => [
        'namespace' => 'MongoDbProxy',
        'path' => BASE_PATH . '/cache/MongoDbProxies',
    ],

    'collection' => [
        'namespace' => 'PersistentCollections',
        'path' => BASE_PATH . '/cache/PersistentCollections',
    ],

    'mapping' => [
        'driver' => 'xml', //xml or annotation
        'extension' => '.dcm.xml',
        'paths' => [
            //
        ]
    ],

    'persistent_collections' => [
        //
    ],

    'types' => [
        //
    ],
];
