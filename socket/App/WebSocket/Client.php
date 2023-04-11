<?php


class Client
{
    protected $service;

    public function __call($name, $arguments)
    {
        if ($name == 'service') {
            $this->service = $arguments[0];
            return $this;
        }
        $json_data = json_encode([
            'service' => $this->service,
            'action' => $name,
            'param' => $arguments[0],
        ]);
        $client = new \Swoole\Client(SWOOLE_SOCK_TCP);
        if (!$client->connect('10.0.126.128', 19968, -1)) {
            exit("connect failed. Error: {$client->errCode}\n");
        }
        $client->send($json_data);
        $result = $client->recv();
        var_dump($result);
        $client->close();
    }
}

$client = new Client();
$client->service('CartService')->getList(1);
