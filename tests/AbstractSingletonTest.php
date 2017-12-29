<?php
namespace Bpaulsen314\Perfecto;

use \PHPUnit_Framework_TestCase as TestCase;

class SingletonStub extends AbstractSingleton
{
}

class ChildSingletonStub extends SingletonStub
{
}

class AbstractSingletonTest extends TestCase
{
    public function testGetInstance()
    {
        $singleton1 = SingletonStub::getInstance();
        $this->assertInstanceOf(
            "Bpaulsen314\\Perfecto\\SingletonStub", $singleton1
        );

        $singleton2 = SingletonStub::getInstance();
        $this->assertEquals($singleton1->getOid(), $singleton2->getOid());

        $singleton3 = ChildSingletonStub::getInstance();
        $this->assertInstanceOf(
            "Bpaulsen314\\Perfecto\\ChildSingletonStub", $singleton3
        );
        $this->assertNotEquals($singleton1, $singleton3);
    }
}
