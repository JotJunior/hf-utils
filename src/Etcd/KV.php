<?php

namespace Jot\HfUtils\Etcd;

use Hyperf\Etcd\V3\EtcdClient;
use Hyperf\Etcd\V3\KV as HyperfKV;
use GuzzleHttp;
use Hyperf\Guzzle\HandlerStackFactory;
use function Hyperf\Support\make;

class KV extends HyperfKV
{

    public function __construct(string $uri, string $version, protected array $auth, protected array $options, protected HandlerStackFactory $factory)
    {
        parent::__construct($uri, $version, $options, $factory);
    }

    protected function client(): EtcdClient
    {
        $options = array_replace([
            'base_uri' => $this->baseUri,
            'handler' => $this->getDefaultHandler(),
        ], $this->options);

        $httpClient = make(GuzzleHttp\Client::class, [
            'config' => $options,
        ]);

        $etcdClient = make(EtcdClient::class, [
            'client' => $httpClient,
        ]);

        if (!empty($this->auth['user']) && !empty($this->auth['password'])) {
            $token = $etcdClient->authenticate($this->auth['user'], $this->auth['password']);
            $etcdClient->setToken($token['token']);
        }

        return $etcdClient;
    }

}
