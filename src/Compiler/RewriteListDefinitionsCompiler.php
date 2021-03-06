<?php

declare(strict_types=1);

namespace ToyWpRouting\Compiler;

use ToyWpRouting\RewriteInterface;

class RewriteListDefinitionsCompiler
{
    /**
     * @var RewriteInterface[]
     */
    private array $rewrites;

    public function __construct(array $rewrites)
    {
        $this->rewrites = (fn (RewriteInterface ...$rewrites) => $rewrites)(...$rewrites);
    }

    public function __toString()
    {
        return $this->compile();
    }

    public function compile(): string
    {
        return vsprintf($this->prepareTemplate(), array_map(
            fn (RewriteInterface $rewrite) => (string) (new RewriteCompiler($rewrite)),
            $this->rewrites
        ));
    }

    private function prepareTemplate(): string
    {
        return implode(PHP_EOL, array_map(
            fn ($n) => "\$rewrite{$n} = %s;" . PHP_EOL . "\$this->rewrites->attach(\$rewrite{$n});",
            range(0, count($this->rewrites) - 1)
        ));
    }
}
