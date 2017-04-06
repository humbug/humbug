<?php

/**
 * Humbug
 *
 * @category   Humbug
 * @package    Humbug
 * @copyright  Copyright (c) 2015 Pádraic Brady (http://blog.astrumfutura.com)
 * @license    https://github.com/padraic/humbug/blob/master/LICENSE New BSD License
 *
 * @author     piotr@zuralski.net
 */

namespace Humbug\Test;

trait PHPUnitHandleDeprecatedMethodsTrait
{

    /**
     * @param string $originalClassName
     * @param array $methods
     * @param array $arguments
     * @param string $mockClassName
     * @param bool $callOriginalConstructor
     * @param bool $callOriginalClone
     * @param bool $callAutoload
     * @param bool $cloneArguments
     * @param bool $callOriginalMethods
     * @param null $proxyTarget
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \PHPUnit_Framework_Exception
     */
    public function getMock(
        $originalClassName,
        $methods = [],
        array $arguments = [],
        $mockClassName = '',
        $callOriginalConstructor = true,
        $callOriginalClone = true,
        $callAutoload = true,
        $cloneArguments = false,
        $callOriginalMethods = false,
        $proxyTarget = null
    )
    {
        /** @var \PHPUnit_Framework_TestCase $this */
        if (method_exists($this, 'createMock')) {
            if (func_num_args() == 1) {
                return $this->createMock($originalClassName);
            }
            /** @var \PHPUnit_Framework_MockObject_MockBuilder $mock */
            $mock = $this->getMockBuilder($originalClassName);
            $mock->setMethods($methods)
                ->setConstructorArgs($arguments)
                ->setMockClassName($mockClassName);

            if (!$callOriginalConstructor) {
                $mock->disableOriginalConstructor();
            }
            if (!$callOriginalClone) {
                $mock->disableOriginalClone();
            }
            if (!$callAutoload) {
                $mock->disableAutoload();
            }
            if ($cloneArguments) {
                $mock->enableArgumentCloning();
            }
            if ($callOriginalMethods) {
                $mock->enableProxyingToOriginalMethods();
            }
            if ($proxyTarget !== null) {
                $mock->setProxyTarget($proxyTarget);
            }
            return $mock->getMock();

        } else {

            return parent::getMock(
                $originalClassName,
                $methods,
                $arguments,
                $mockClassName,
                $callOriginalConstructor,
                $callOriginalClone,
                $callAutoload,
                $cloneArguments,
                $callOriginalMethods,
                $proxyTarget
            );
        }
    }

}