<?php

declare(strict_types=1);

namespace ToyWpRouting\Tests;

use Invoker\Invoker;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ToyWpRouting\InvokerBackedInvocationStrategy;
use ToyWpRouting\Rewrite;

// @todo Test with custom resolver set on Invoker instance?
class InvokerBackedInvocationStrategyTest extends TestCase
{
    public function testInvokeHandler()
    {
        $invocationCount = 0;

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
        $rewrite = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => 'one']],
            function () use (&$invocationCount) {
                $invocationCount++;

                return 'returnvalue';
            }
        );

        $returnValue = $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithAdditionalParameters()
    {
        $invocationCount = 0;
        $invocationParam = '';

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
        $strategy->withAdditionalContext(['queryVars' => ['one' => 'testvalue']]);
        $rewrite = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => '$matches[1]']],
            function ($one) use (&$invocationCount, &$invocationParam) {
                $invocationCount++;
                $invocationParam = $one;

                return 'returnvalue';
            }
        );

        $returnValue = $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame('testvalue', $invocationParam);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithContainerBackedInvoker()
    {
        $container = new class () implements ContainerInterface {
            public $invocationCount = 0;

            public function get($name)
            {
                if ('testhandler' === $name) {
                    return function () {
                        $this->invocationCount++;

                        return 'returnvalue';
                    };
                }
            }

            public function has($name)
            {
                return 'testhandler' === $name;
            }
        };

        $strategy = new InvokerBackedInvocationStrategy(new Invoker(null, $container));
        $rewrite = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => '$matches[1]']],
            'testhandler'
        );

        $returnValue = $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $container->invocationCount);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeHandlerWithPrefixedAdditionalParameters()
    {
        $invocationCount = 0;
        $invocationParam = [];

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
        $strategy->withAdditionalContext(['queryVars' => ['pfx_one' => 'testvalue']]);
        $rewrite = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => '$matches[1]']],
            function ($one) use (&$invocationCount, &$invocationParam) {
                $invocationCount++;
                $invocationParam = $one;

                return 'returnvalue';
            },
            'pfx_'
        );

        $returnValue  = $strategy->invokeHandler($rewrite);

        $this->assertSame(1, $invocationCount);
        $this->assertSame('testvalue', $invocationParam);
        $this->assertSame('returnvalue', $returnValue);
    }

    public function testInvokeIsActiveCallback()
    {
        $invocationCount = 0;

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
        $one = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => 'one']],
            function () {
            },
            '',
            function () use (&$invocationCount) {
                $invocationCount++;

                return true;
            }
        );
        $two = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => 'one']],
            function () {
            },
            '',
            function () use (&$invocationCount) {
                $invocationCount++;

                return false;
            }
        );

        $this->assertTrue($strategy->invokeIsActiveCallback($one));
        $this->assertFalse($strategy->invokeIsActiveCallback($two));
        $this->assertSame(2, $invocationCount);
    }

    public function testInvokeIsActiveCallbackWithContainerBackedInvoker()
    {
        $container = new class () implements ContainerInterface {
            public $invocationCount = 0;

            public function get($name)
            {
                if ('testisactivecallback' === $name) {
                    return function () {
                        $this->invocationCount++;

                        return false;
                    };
                }
            }

            public function has($name)
            {
                return 'testisactivecallback' === $name;
            }
        };

        $strategy = new InvokerBackedInvocationStrategy(new Invoker(null, $container));
        $rewrite = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => '$matches[1]']],
            function () {
            },
            '',
            'testisactivecallback'
        );

        $this->assertFalse($strategy->invokeIsActiveCallback($rewrite));
        $this->assertSame(1, $container->invocationCount);
    }

    public function testInvokeIsActiveCallbackWithNoCallbackSet()
    {
        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
        $rewrite = new Rewrite(['GET'], ['^one$' => ['one' => 'one']], function () {
        });

        $isActive = $strategy->invokeIsActiveCallback($rewrite);

        $this->assertTrue($isActive);
    }

    public function testInvokeIsActiveCallbackWithNonBooleanReturnValue()
    {
        $invocationCount = 0;

        $strategy = new InvokerBackedInvocationStrategy(new Invoker());
        $one = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => 'one']],
            function () {
            },
            '',
            function () use (&$invocationCount) {
                $invocationCount++;

                return 1;
            }
        );
        $two = new Rewrite(
            ['GET'],
            ['^one$' => ['one' => 'one']],
            function () {
            },
            '',
            function () use (&$invocationCount) {
                $invocationCount++;

                return '';
            }
        );

        $this->assertTrue($strategy->invokeIsActiveCallback($one));
        $this->assertFalse($strategy->invokeIsActiveCallback($two));
        $this->assertSame(2, $invocationCount);
    }
}