<?php

declare(strict_types=1);

namespace ToyWpRouting;

class OptimizedRewrite implements RewriteInterface
{
    protected $handler;
    protected $isActiveCallback;
    protected $methods;
    protected $prefixedToUnprefixedQueryVariablesMap;
    protected $queryVariables;
    protected $rules;

    public function __construct(
        array $methods,
        array $rules,
        $handler,
        array $prefixedToUnprefixedQueryVariablesMap,
        array $queryVariables,
        $isActiveCallback = null
    ) {
        $this->methods = $methods;
        $this->rules = $rules;
        $this->handler = $handler;
        $this->prefixedToUnprefixedQueryVariablesMap = $prefixedToUnprefixedQueryVariablesMap;
        $this->queryVariables = $queryVariables;
        $this->isActiveCallback = $isActiveCallback;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function getIsActiveCallback()
    {
        return $this->isActiveCallback;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getPrefixedToUnprefixedQueryVariablesMap(): array
    {
        return $this->prefixedToUnprefixedQueryVariablesMap;
    }

    public function getQueryVariables(): array
    {
        return $this->queryVariables;
    }

    public function getRules(): array
    {
        return $this->rules;
    }
}
