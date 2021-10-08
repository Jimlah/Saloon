<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Interfaces\SaloonRequestInterface;
use Sammyjo20\Saloon\Traits\CollectsAuth;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsQuery;
use Sammyjo20\Saloon\Traits\CollectsAttributes;
use Sammyjo20\Saloon\Traits\InterceptsGuzzle;
use Sammyjo20\Saloon\Traits\SendsRequests;

abstract class SaloonRequest implements SaloonRequestInterface
{
    use CollectsData,
        CollectsHeaders,
        CollectsAuth,
        CollectsQuery, // Todo: Do we really need to have a collector for this?
        CollectsConfig,
        CollectsAttributes;

    use SendsRequests;
    use InterceptsGuzzle;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected ?string $method = null;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected ?string $connector = null;

    public function __construct(array $requestAttributes = [])
    {
        // Bootstrap our request
        // Todo: Tidy  up the below - note "options" and "data" is basically the same thing.
        // Todo: Throw exceptions if certain attributes aren't listed.
        // Todo: Throw exceptions if requested headers, attributes or data is missing.

        // Todo: Work out how to access "auth", "data", and "config" from within each other.
        // Todo: Setting the defaults may not work here.

        $this->setRequestAttributes($requestAttributes);
        $this->setAuth($this->defineAuth());
        $this->setHeaders($this->defineHeaders());
        $this->setData($this->defineData());
        $this->setQuery($this->defineQuery());
        $this->setConfig($this->defineConfig());
    }

    public function defineMethod(): ?string
    {
        if (empty($this->method)) {
            return null;
        }

        return $this->method;
    }

    public function getConnector(): ?SaloonConnector
    {
        if (empty($this->connector) || ! class_exists($this->connector)) {
            return null;
        }

        return new $this->connector;
    }

    public function mockSuccessResponse(): array
    {
        // Todo: Change this as it won't work if you want to define the success/failure response.

        return [
            'status' => 201,
            'headers' => [],
            'body' => 'Success',
        ];
    }

    public function mockFailureResponse(): array
    {
        return [
            'status' => 401,
            'headers' => [],
            'body' => '',
        ];
    }

    public function defineBody(): array
    {
        return $this->getData();
    }
}
