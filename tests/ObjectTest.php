<?php
namespace Bpaulsen314\Perfecto;

use \PHPUnit_Framework_TestCase as TestCase;

class ObjectStub extends Object
{
    protected static $_magicCallMethods = array(
        "addRelationship" => true,
        "getCode" => true,
        "getDescription" => true,
        "getName" => true,
        "getRelationships" => true,
        "getUndefinedProperty" => true,
        "isActive" => true,
        "setActive" => true,
        "setDescription" => true,
        "setName" => true,
        "setUndefinedProperty" => true,
    );

    protected $_active = null;
    protected $_code = null;
    protected $_description = null;
    protected $_name = null;
    protected $_relationships = null;
}

class ObjectTest extends TestCase
{
    /**
     * @expectedException   Exception
     */
    public function testMagicCallGetUndefinedProperty()
    {
        $stub = new ObjectStub();
        $stub->getUndefinedProperty();
    }

    public function testMagicCallAdd()
    {
        $stub = new ObjectStub();
        $stub->addRelationship("OBJECT_123210");
        $stub->addRelationship("OBJECT_123211");
        $stub->addRelationship("OBJECT_123212");
        $relationships = $stub->getRelationships();
        $this->assertCount(3, $relationships);
    }

    public function testMagicCallIs()
    {
        $stub = new ObjectStub();
        $stub->setActive(true);
        $this->assertTrue($stub->isActive());
        $stub->setActive(false);
        $this->assertFalse($stub->isActive());
    }

    public function testMagicCallSetGet()
    {
        $stub = new ObjectStub();
        $stub->setName("Name");
        $this->assertEquals("Name", $stub->getName());
        $stub->setDescription("This is a description.");
        $this->assertEquals("This is a description.", $stub->getDescription());
    }

    /**
     * @expectedException   Exception
     */
    public function testMagicCallSetTooFewParameters()
    {
        $stub = new ObjectStub();
        $stub->setName();
    }

    /**
     * @expectedException   Exception
     */
    public function testMagicCallSetTooManyParameters()
    {
        $stub = new ObjectStub();
        $stub->setName("Name1", "Name2");
    }

    /**
     * @expectedException   Exception
     */
    public function testMagicCallSetUndefinedProperty()
    {
        $stub = new ObjectStub();
        $stub->setUndefinedProperty(true);
    }

    /**
     * @expectedException   Exception
     */
    public function testMagicCallUndefinedMethod()
    {
        $stub = new ObjectStub();
        $stub->setCode();
    }
}
