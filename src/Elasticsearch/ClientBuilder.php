<?php

declare(strict_types=1);

namespace Jot\HfUtils\Elasticsearch;

use Elasticsearch\Client as ElasticsearchClient;
use Hyperf\Elasticsearch\ClientBuilderFactory;
use Hyperf\Etcd\KVInterface;

/**
 * Service class for interacting with Elasticsearch through a configured client.
 */
class ClientBuilder
{

    private KVInterface $etcdClient;
    private ClientBuilderFactory $clientBuilderFactory;

    public function __construct(KVInterface $etcdClient, ClientBuilderFactory $clientBuilderFactory)
    {
        $this->etcdClient = $etcdClient;
        $this->clientBuilderFactory = $clientBuilderFactory;
    }

    public function build(): ElasticsearchClient
    {
        $hosts = $this->etcdClient->get('/services/elasticsearch/hosts');
        $username = $this->etcdClient->get('/services/elasticsearch/username');
        $password = $this->etcdClient->get('/services/elasticsearch/password');

        $clientBuilder = $this->clientBuilderFactory->create();
        $clientBuilder->setHosts(explode(',', $hosts))
            ->setBasicAuthentication((string)$username, (string)$password);

        return $clientBuilder->build();
    }

}