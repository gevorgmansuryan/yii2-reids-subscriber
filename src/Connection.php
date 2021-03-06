<?php


namespace Gevman\Yii2RedisSubscriber;


use yii\db\Exception;
use yii\redis\SocketException;

class Connection extends \yii\redis\Connection
{
    public $dataTimeout = -1;

    protected function parseSubscribeResponse()
    {
        if (($line = fgets($this->socket)) === false) {
            throw new SocketException('Failed to read from socket.');
        }
        $type = $line[0] ?? null;

        if (!$line) {
            return;
        }

        $line = mb_substr($line, 1, -2, '8bit');
        switch ($type) {
            case '$': // Bulk replies
                if ($line == '-1') {
                    return null;
                }
                $length = (int)$line + 2;
                $data = '';
                while ($length > 0) {
                    if (($block = fread($this->socket, $length)) === false) {
                        throw new SocketException('Failed to read from socket.');
                    }
                    $data .= $block;
                    $length -= mb_strlen($block, '8bit');
                }

                return mb_substr($data, 0, -2, '8bit');
            case '*': // Multi-bulk replies
                $count = (int) $line;
                $data = [];
                for ($i = 0; $i < $count; $i++) {
                    $data[] = $this->parseSubscribeResponse();
                }

                return $data;
            default:
                throw new Exception('Received illegal data from redis: ' . $line);
        }
    }

    public function listen($channels, callable $callback, ?callable $errorCallback = null, ?callable $fallback = null)
    {
        try {

            $this->subscribe($channels);
        } catch (\Throwable $e) {
            if (is_callable($errorCallback)) {
                call_user_func($errorCallback, $e);
            }

            if (is_callable($fallback)) {
                $fallback();
            }

            throw $e;
        };

        while (true) {
            try {
                $data = $this->parseSubscribeResponse();
                call_user_func_array($callback, $data);
            } catch (\Throwable $e) {
                if (is_callable($errorCallback)) {
                    if (call_user_func($errorCallback, $e) === false) {
                        break;
                    }
                }
            }
        }

        if (is_callable($fallback)) {
            $fallback();
        }
    }
}