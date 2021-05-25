<?php


namespace Gevman\Yii2RedisSubscriber;



use Yii;
use yii\base\Component;
use Redis;

class Connection extends Component
{
    public $redis = 'redis';

    /**
     * @var \yii\redis\Connection
     */
    protected $redisComponent;

    protected $redisPubSub;

    public function init()
    {
        $this->redisComponent = Yii::$app->get($this->redis);

        $this->redis = new Redis();
        $this->redis->connect(
            $this->redisComponent->hostname,
            $this->redisComponent->port,
            $this->redisComponent->connectionTimeout
        );

        if ($this->redisComponent->password) {
            $this->redis->auth($this->redisComponent->password);
        }

        parent::init();
    }

    public function subscribe($channels, callable $callback)
    {
        if(is_string($channels)) {
            $channels = [$channels];
        }

        return $this->redisPubSub->subscribe($channels, $callback);
    }
}