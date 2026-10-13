<?php

namespace Tests\Concerns;

use Mockery\ExpectationInterface;

trait MocksGraphQlOperations
{
    /**
     * Mock a GraphQL call by its operation name (e.g. "OrderVat"), returning the given `data`.
     *
     * @param  array<string, mixed>  $data
     */
    protected function mockGraphQlOperation(string $operationName, array $data, ?callable $paramsMatcher = null): ExpectationInterface
    {
        return $this->testApiHelper()
            ->getApi()
            ->shouldReceive('graph')
            ->withArgs(function (string $query, array $params = []) use ($operationName, $paramsMatcher): bool {
                if (preg_match('/\b(query|mutation)\s+' . preg_quote($operationName, '/') . '\b/', $query) !== 1) {
                    return false;
                }

                return $paramsMatcher === null || $paramsMatcher($params) === true;
            })
            ->andReturn(['body' => ['data' => $data]]);
    }
}
