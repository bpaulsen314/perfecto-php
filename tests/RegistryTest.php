<?php
namespace W3glue\Perfecto;

use \PHPUnit_Framework_TestCase as TestCase;

class RegistryTest extends TestCase
{
    public function testGet()
    {
        $registry = Registry::getInstance();

        // Scenario 1 - Scalar
        $name = $registry->get("sport.name");
        $this->assertEquals($name, "Baseball");

        // Scenario 2 - Composite
        $teams = $registry->get("sport.teams");
        $sport_data = $this->_getSportData();
        $this->assertEquals($teams, $sport_data["teams"]);
    }

    public function testGetAll()
    {
        $registry = Registry::getInstance();
        $data = $registry->get();
        $sport_data = $this->_getSportData();
        $this->assertEquals($data, ["sport" => $sport_data]);
    }

    public function testGetUndefinedKey()
    {
        $registry = Registry::getInstance();
        $abbreviation = $registry->get("sport.abbreviation");
        $this->assertFalse($abbreviation);
    }

    public function testDump()
    {
        $this->markTestIncomplete("No tests have been written for dump behavior.");
    }

    public function testImport()
    {
        $this->markTestIncomplete("No tests have been written for import behavior.");
    }


    /**
     *  @depends    testGetAll
     */
    public function testClear()
    {
        $registry = Registry::getInstance();
        $cleared = $registry->clear();
        $this->assertTrue($cleared);
        $data = $registry->get();
        $this->assertEmpty($data);
    }

    /**
     *  @depends    testGet
     *  @depends    testGetUndefinedKey
     */
    public function testDelete()
    {
        $registry = Registry::getInstance();

        // Scenario 1 - Scalar
        $deleted = $registry->delete("sport.name");
        $this->assertTrue($deleted);
        $city = $registry->get("sport.name");
        $this->assertFalse($city);

        // Scenario 2 - Composite
        $deleted = $registry->delete("sport.teams.STL");
        $this->assertTrue($deleted);
        $team_stl = $registry->get("sport.teams.STL");
        $this->assertFalse($team_stl);
        $teams = $registry->get("sport.teams");
        $this->assertCount(1, $teams);
    }

    /**
     *  @depends    testGetAll
     */
    public function testDeleteAll()
    {
        $registry = Registry::getInstance();
        $deleted = $registry->delete();
        $this->assertTrue($deleted);
        $data = $registry->get();
        $this->assertEmpty($data);
    }

    public function testDeleteUndefinedKey()
    {
        $registry = Registry::getInstance();
        $deleted = $registry->delete("sport.abbreviation");
        $this->assertFalse($deleted);
    }


    /**
     *  @depends    testGet
     */
    public function testSet()
    {
        $registry = Registry::getInstance();

        // Scenario 1 - Scalar
        $registry->set("sport.abbreviation", "BB");
        $sport_abbreviation = $registry->get("sport.abbreviation");
        $this->assertEquals($sport_abbreviation, "BB");

        // Scenario 2 - Composite
        $league_data = [
            "AL" => ["name" => "American League"],
            "NL" => ["name" => "National League"]
        ];
        $registry->set("sport.leagues", $league_data);
        $leagues = $registry->get("sport.leagues");
        $this->assertEquals($leagues, $league_data);
    }

    /**
     * @expectedException   Exception
     */
    public function testSetDefinedKey()
    {
        $registry = Registry::getInstance();
        $registry->set("sport.name", "Football");
    }

    /**
     * @expectedException   Exception
     */
    public function testSetInvalidKey()
    {
        $registry = Registry::getInstance();
        $registry->set("sport.name.abbreviation", "Baseball");
    }

    protected function setUp()
    {
        $registry = Registry::getInstance();
        $registry->clear();
        $registry->set("sport", $this->_getSportData());
    }

    protected function _getSportData()
    {
        return [
            "name" => "Baseball",
            "teams" => [
                "STL" => [
                    "city" => "St. Louis",
                    "mascot" => "Cardinals"
                ],
                "NYY" => [
                    "city" => "New York",
                    "mascot" => "Yankees"
                ]
            ]
        ];
    }
}
