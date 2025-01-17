<?php

namespace Jot\HfUtils\Etcd;

use Hyperf\Etcd\V3\EtcdClient;
use Hyperf\Etcd\V3\KV as HyperfKV;
use GuzzleHttp;
use Hyperf\Guzzle\HandlerStackFactory;
use Jot\HfUtils\Exception\AuthenticationException;
use function Hyperf\Support\make;

class KV extends HyperfKV
{

    public function __construct(string $uri, string $version, protected array $auth, protected array $options, protected HandlerStackFactory $factory)
    {
        parent::__construct($uri, $version, $options, $factory);
    }

    /**
     * Fetches the value associated with the given key from the configuration.
     *
     * @param string $key The key to retrieve the value for.
     * @param array $options Optional parameters for the retrieval process.
     * @return array|null The retrieved value as an associative array or null if no value is found.
     * @throws AuthenticationException If there is an issue with the configuration or retrieval process.
     */
    public function get($key, array $options = []): ?array
    {
        return $this->handleRequestWithRetry(function () use ($key, $options) {
            return parent::get($key, $options);
        });
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

        $this->authenticate();

        return $etcdClient;
    }


    /**
     * Authenticates the client using stored credentials.
     *
     * Attempts to authenticate the client if a user and password are provided.
     * If authentication is successful, it sets the retrieved token for the client.
     * In case of issues during the process, retries the request as needed.
     *
     * @return void
     * @throws AuthenticationException If authentication fails or the provided credentials are invalid.
     */
    protected function authenticate(): void
    {
        if (!empty($this->auth['user']) && !empty($this->auth['password'])) {
            return;
        }
        $this->handleRequestWithRetry(function () {
            $token = $this->client()->authenticate($this->auth['user'], $this->auth['password']);
            $this->client()->setToken($token['token']);
        });
    }

    /**
     * Handles a request with retry logic for specific authentication errors.
     *
     * @param callable $request A callable representing the request to execute.
     *                           This method will retry the request if authentication issues arise.
     * @return array The result of the successfully executed request.
     * @throws AuthenticationException If authentication fails or the etcd authentication configuration is incorrect.
     * @throws \Throwable If any other unexpected error occurs during the request execution.
     */
    private function handleRequestWithRetry(callable $request): array
    {
        try {
            return $request();
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'etcdserver: invalid auth token')) {
                $this->authenticate();
                return $request();
            }
            if (str_contains($e->getMessage(), 'etcdserver: authentication failed')) {
                throw new AuthenticationException('Etcd authentication failed. Check if etcd.auth is configured correctly.');
            }
            throw $e;
        }
    }

}
