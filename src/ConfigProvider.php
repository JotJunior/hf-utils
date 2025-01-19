<?php

declare(strict_types=1);

namespace Jot\HfUtils;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'commands' => [],
            'listeners' => [],
            'publish' => [],
        ];
    }
}