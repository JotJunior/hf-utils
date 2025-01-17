<?php

declare(strict_types=1);

namespace Jot\HfUtils\Etcd;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Etcd\Exception\ClientNotFindException;
use Hyperf\Guzzle\HandlerStackFactory;
use Psr\Container\ContainerInterface;

use function Hyperf\Support\make;

class KVFactory extends \Hyperf\Etcd\KVFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get(ConfigInterface::class);
        $uri = $config->get('etcd.uri', 'http://127.0.0.1:2379');
        $version = $config->get('etcd.version', 'v3beta');
        $auth = $config->get('etcd.auth', []);
        $options = $config->get('etcd.options', []);
        $factory = $container->get(HandlerStackFactory::class);

        return make(KV::class, compact('uri', 'version', 'auth', 'options', 'factory'));

    }

}
