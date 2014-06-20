<?php
namespace Perfecto;

use \PHPUnit_Framework_TestCase as TestCase;

class StringHelperTest extends TestCase
{
    public function testCamelNotate()
    {
        $helper = StringHelper::getInstance();
        $this->assertEquals($helper->camelNotate("string_helper"), "StringHelper");
        $this->assertEquals($helper->camelNotate("string_helper", true), "stringHelper");
        $this->assertEquals($helper->camelNotate("string-helper"), "StringHelper");
        $this->assertEquals($helper->camelNotate("string-helper", true), "stringHelper");
    }

    public function testDashNotate()
    {
        $helper = StringHelper::getInstance();
        $this->assertEquals($helper->dashNotate("string_helper"), "string-helper");
        $this->assertEquals($helper->dashNotate("StringHelper"), "string-helper");
    }

    public function testDepluralize()
    {
        $helper = StringHelper::getInstance();
        $this->assertEquals($helper->depluralize("Boxes"), "Box");
        $this->assertEquals($helper->depluralize("communities"), "community");
        $this->assertEquals($helper->depluralize("HALVES"), "HALF");
        $this->assertEquals($helper->depluralize("nails"), "nail");
        $this->assertEquals($helper->depluralize("People"), "Person");
        $this->assertEquals($helper->depluralize("POTATOES"), "POTATO");
    }

    public function testPluralize()
    {
        $helper = StringHelper::getInstance();
        $this->assertEquals($helper->pluralize("Box"), "Boxes");
        $this->assertEquals($helper->pluralize("community"), "communities");
        $this->assertEquals($helper->pluralize("HALF"), "HALVES");
        $this->assertEquals($helper->pluralize("nail"), "nails");
        $this->assertEquals($helper->pluralize("Person"), "People");
        $this->assertEquals($helper->pluralize("POTATO"), "POTATOES");
    }

    public function testUnderscoreNotate()
    {
        $helper = StringHelper::getInstance();
        $this->assertEquals($helper->underscoreNotate("StringHelper"), "string_helper");
        $this->assertEquals($helper->underscoreNotate("string-helper"), "string_helper");
    }
}
