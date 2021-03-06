<?php

declare(strict_types=1);

/*
 * This file is part of Contao.
 *
 * (c) Leo Feyer
 *
 * @license LGPL-3.0-or-later
 */

namespace Contao\CoreBundle\Tests\Contao;

use Contao\Input;
use Contao\System;
use Contao\Widget;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @group contao3
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class WidgetTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        if (!\function_exists('utf8_decode_entities')) {
            include_once __DIR__.'/../../src/Resources/contao/helper/functions.php';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        \define('TL_MODE', 'FE');

        $container = new ContainerBuilder();
        $container->set('request_stack', new RequestStack());

        System::setContainer($container);
    }

    /**
     * @param string $key
     * @param string $input
     * @param mixed  $value
     * @param string $expected
     *
     * @dataProvider postProvider
     */
    public function testReadsThePostData(string $key, string $input, $value, string $expected = null): void
    {
        // Prevent "undefined index" errors
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_NOTICE);

        $widget = $this->createMock(Widget::class);

        $class = new \ReflectionClass(Widget::class);
        $method = $class->getMethod('getPost');
        $method->setAccessible(true);

        $_POST[$input] = $value;
        Input::resetCache();
        Input::initialize();

        $this->assertSame($expected, $method->invoke($widget, $key));

        // Restore the error reporting level
        error_reporting($errorReporting);
    }

    /**
     * @return array
     */
    public function postProvider(): array
    {
        return [
            ['foo', 'foo', 'bar', 'bar'],
            ['foo[0]', 'foo', ['bar'], 'bar'],
            [
                'foo[k1][k2][k3]',
                'foo',
                ['k1' => ['k2' => ['k3' => 'bar']]],
                'bar',
            ],
            ['foo[0]', 'foo', ['k1' => 'bar'], null],
            ['foo[k1][0]', 'foo', ['k1' => 'bar'], 'bar'],
            ['foo', 'nofoo', 'bar', null],
            ['', 'foo', 'bar', null],
            ['', '', 'bar', 'bar'],
            ['[0]', '', ['bar'], 'bar'],
        ];
    }

    public function testValidatesThePostData(): void
    {
        /** @var Widget|\PHPUnit_Framework_MockObject_MockObject $widget */
        $widget = $this
            ->getMockBuilder(Widget::class)
            ->disableOriginalConstructor()
            ->setMethods(['validator'])
            ->getMockForAbstractClass()
        ;

        $widget
            ->expects($this->exactly(3))
            ->method('validator')
            ->withAnyParameters()
            ->willReturnArgument(0)
        ;

        $widget
            ->setInputCallback(
                function (): string {
                    return 'foobar';
                }
            )
            ->validate()
        ;

        $this->assertSame('foobar', $widget->value);

        $widget
            ->setInputCallback(
                function () {
                    return null;
                }
            )
            ->validate()
        ;

        $this->assertNull($widget->value);

        $widget
            ->setInputCallback()
            ->validate() // getPost() should be called once here
        ;
    }
}
