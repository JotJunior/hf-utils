<?php

namespace Jot\HfUtils;

use Hyperf\Elasticsearch\ClientBuilderFactory;
use Hyperf\Etcd\KVInterface;
use Hyperf\Etcd\V3\KV;
use Jot\HfUtils\Elasticsearch\ClientBuilder;
use Jot\HfUtils\Etcd\KV as JotKV;
use Psr\Container\ContainerInterface;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                ClientBuilder::class => function (ContainerInterface $container) {
                    return new ClientBuilder(
                        $container->get(KVInterface::class),
                        $container->get(ClientBuilderFactory::class)
                    );
                },
                KV::class => JotKV::class,
            ],
            'commands' => [],
            'listeners' => [],
            'publish' => [],
        ];
    }
}