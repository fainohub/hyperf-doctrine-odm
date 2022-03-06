# Hyperf Doctrine ODM
Doctrine MongoDB Object Document Mapper (ODM) for Hyperf framework.

## Installation
```
composer require fainohub/hyperf-doctrine-odm
```

## Setup

```
php bin/hyperf.php vendor:publish fainohub/hyperf-doctrine-odm
```

Configure the doctrine in `config/autoload/doctrine-odm.php`:
```php
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
```

Generate Hydrators:
```
php bin/hyperf.php doctrine:generate-hydrators
```
Generate Proxies:
```
php bin/hyperf.php doctrine:generate-proxies
```
Generate Persistent Collections:
```
php bin/hyperf.php doctrine:generate-persistent-collections
```

Automatically generate in composer.json
```
"scripts": {
    "post-autoload-dump": [
        "@php bin/hyperf.php doctrine:generate-hydrators",
        "@php bin/hyperf.php doctrine:generate-proxies",
        "@php bin/hyperf.php doctrine:generate-persistent-collections",
    ]
}
```
