<?php

namespace Jot\HfUtils;

use Hyperf\Etcd\V3\KV;
use Jot\HfUtils\Etcd\KV as JotKV;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                KV::class => JotKV::class,
            ],
            'commands' => [],
            'listeners' => [],
            'publish' => [],
        ];
    }
}