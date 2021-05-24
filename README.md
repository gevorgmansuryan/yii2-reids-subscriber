# Yii2 Redis Subscriber
Yii2 Redis Channel Subscriber implementation for yii2-redis

## installation
using composer
```bash
composer require gevman/yii2-redis-subscriber
```

- Configuration (pusher project)

```php
'components' => [
    //...
    'redis' => [
        'class' => \yii\redis\Connection::class,
        'hostname' => 'localhost',
        'port' => 6379,
    ],
    //...
],
```

- Configuration (subscriber project)

```php
'components' => [
    //...
    'redisSubscriber' => [
        'class' => \Gevman\Yii2RedisSubscriber\Connection::class,
        'hostname' => 'localhost',
        'port' => 6379,
    ],
    //...
],
```

## Usage


- Push message to channel
```php
\Yii::$app->redis->publish('some_channel', 'some message');
```
- Listen channel messages
```php
\Yii::$app->redisSubscriber->listen(
    'some_channel', 
    function($type, $channel, $message) {
        do_something($type, $channel, $message);
    },
    function(\Throwable $error) {
        \Yii::error($error->getMessage());
    }
);
```