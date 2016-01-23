<?php
namespace W3glue\Perfecto;

use \PHPUnit_Framework_TestCase as TestCase;

class ArrayHelperTest extends TestCase
{
    public function testAreIdentical()
    {
        $helper = ArrayHelper::getInstance();
        $arr1 = [
            "first_name" => "Ben",
            "last_name" => "Paulsen",
            "birthdate" => [
                "month" => "October",
                "day" => 15,
                "year" => 1980
            ]
        ];
        $arr2 = [
            "first_name" => "Ben",
            "birthdate" => [
                "month" => "October",
                "year" => 1980,
                "day" => 15
            ],
            "last_name" => "Paulsen"
        ];
        $arr3 = [
            "first_name" => "Benjamin",
            "birthdate" => [
                "month" => "October",
                "year" => 1980,
                "day" => 15
            ],
            "last_name" => "Paulsen"
        ];
        $this->assertTrue($helper->areIdentical($arr1, $arr2));
        $this->assertFalse($helper->areIdentical($arr1, $arr3));
        $this->assertFalse($helper->areIdentical($arr2, $arr3));
    }

    public function testCamelNotateKeys()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();
        $notated_data = $helper->camelNotateKeys($data);
        $this->_assertKeyNotation("camel", $notated_data);
        $this->assertArrayHasKey(
            "firstName",
            $notated_data["teams"]["stl"]["players"][0]
        );
        $this->assertArrayHasKey(
            "lastName",
            $notated_data["teams"]["stl"]["players"][0]
        );
    }

    public function testCastNumericStrings()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();
        $helper->castNumericStrings($data);
        $this->_assertNoNumericStrings($data);
    }

    public function testDelete()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();
        $deleted = $helper->delete($data, "teams.stl");
        $this->assertFalse($deleted);
        $team = $helper->getValue($data, "teams.STL");
        $this->assertTrue(is_array($team));
        $deleted = $helper->delete($data, "teams.STL");
        $this->assertTrue($deleted);
        $team = $helper->getValue($data, "teams.STL");
        $this->assertFalse($team);
    }

    public function testDashNotateKeys()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();
        $notated_data = $helper->dashNotateKeys($data);
        $this->_assertKeyNotation("dash", $notated_data);
        $this->assertArrayHasKey(
            "first-name",
            $notated_data["teams"]["stl"]["players"][0]
        );
        $this->assertArrayHasKey(
            "last-name",
            $notated_data["teams"]["stl"]["players"][0]
        );
    }

    public function testExtractValues()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();

        //  array, key
        $last_names = $helper->extractValues(
            $data,
            "teams.players.last_name"
        );
        sort($last_names);
        $this->assertEquals(
            $last_names,
            array("Brock", "Gehrig", "Gibson", "Musial", "Ruth")
        );

        // key, array
        $last_names = $helper->extractValues(
            $data,
            "teams.players.last_name"
        );
        sort($last_names);
        $this->assertEquals(
            $last_names,
            array("Brock", "Gehrig", "Gibson", "Musial", "Ruth")
        );
    }

    public function testGetValue()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();
        $sport_name = $helper->getValue("sport.name", $data);
        $this->assertFalse($sport_name);
        $mascot = $helper->getValue("teams.STL.mascot", $data);
        $this->assertEquals($mascot, "Cardinals");
    }

    public function testHash()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();

        // make sure the output is a string
        $hash = $helper->hash($data);
        $this->assertInternalType("string", $hash);

        // compare two identical arrays with different ordering
        $player = $data["teams"]["NYY"]["players"][0];
        $player_hash = $helper->hash($player);

        $player_reversed = array_reverse($player);
        $player_reversed_hash = $helper->hash($player_reversed);

        $this->assertEquals($player_hash, $player_reversed_hash);
    }

    public function testHashOn()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();
        $players = $data["teams"]["STL"]["players"];

        // array, key
        $hashed_players = $helper->hashOn($players, "number");
        foreach ($hashed_players as $number => $player) {
            $player_number = $player["number"];
            $player_number = "$player_number";
            $this->assertEquals($number, $player_number);
        }
        $keys = array_keys($hashed_players);
        sort($keys);
        $this->assertEquals($keys, array("6", "20", "35"));

        // key, array
        $hashed_players = $helper->hashOn("number", $players);
        foreach ($hashed_players as $number => $player) {
            $player_number = $player["number"];
            $player_number = "$player_number";
            $this->assertEquals($number, $player_number);
        }
        $keys = array_keys($hashed_players);
        sort($keys);
        $this->assertEquals($keys, array("6", "20", "35"));
    }

    public function testIsHashTable()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();

        // is a hash table
        $is_hash_table = $helper->isHashTable($data);
        $this->assertTrue($is_hash_table);

        // is NOT a hashtable
        $is_hash_table = $helper->isHashTable(
            $data["teams"]["STL"]["players"]
        );
        $this->assertFalse($is_hash_table);
    }

    public function testIsList()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();

        // is a list
        $is_list = $helper->isList(
            $data["teams"]["STL"]["players"]
        );
        $this->assertTrue($is_list);

        // is NOT a list
        $is_list = $helper->isList($data);
        $this->assertFalse($is_list);
    }

    public function testUnderscoreNotateKeys()
    {
        $helper = ArrayHelper::getInstance();
        $data = $this->_getData();
        $notated_data = $helper->underscoreNotateKeys($data);
        $this->_assertKeyNotation("underscore", $notated_data);
        $this->assertArrayHasKey(
            "first_name",
            $notated_data["teams"]["stl"]["players"][0]
        );
        $this->assertArrayHasKey(
            "last_name",
            $notated_data["teams"]["stl"]["players"][0]
        );
    }

    protected function _assertKeyNotation($type, $arr)
    {
        foreach ($arr as $key => $value) {
            if (is_string($key)) {
                if (preg_match("#^dash#i", $type)) {
                    $this->assertNotRegExp("#([A-Z][a-z]|_)#", $key);
                } else if (preg_match("#^underscore#i", $type)) {
                    $this->assertNotRegExp("#([A-Z][a-z]|-)#", $key);
                } else if (preg_match("#^camel#i", $type)) {
                    $this->assertNotRegExp("#(-|_)#", $key);
                }
            }
            if (is_array($value)) {
                $this->_assertKeyNotation($type, $value);
            }
        }
    }

    protected function _assertNoNumericStrings($arr)
    {
        foreach ($arr as $key => $value) {
            if (is_string($value)) {
                $this->assertNotRegExp("#^-?([1-9][0-9]*|0)$#", $value);
                $this->assertNotRegExp(
                    "#^-?([1-9][0-9]*|0)?\.(0|[0-9]*[1-9])$#", $value
                );
            } else if (is_array($value)) {
                $this->_assertNoNumericStrings($value);
            }
        }
    }

    protected function _getData()
    {
        return array(
            "sport" => "Baseball",
            "teams" => array(
                "STL" => array(
                    "city" => "St. Louis",
                    "mascot" => "Cardinals",
                    "players" => array(
                        array(
                            "first_name" => "Stan",
                            "last_name" => "Musial",
                            "number" => 6,
                            "average" => ".331"
                        ),
                        array(
                            "first_name" => "Bob",
                            "last_name" => "Gibson",
                            "number" => "35",
                            "average" => ".206"
                        ),
                        array(
                            "first_name" => "Lou",
                            "last_name" => "Brock",
                            "number" => 20,
                            "average" => .293
                        )
                    )
                ),
                "NYY" => array(
                    "city" => "New York",
                    "mascot" => "Yankees",
                    "players" => array(
                        array(
                            "first_name" => "Babe",
                            "last_name" => "Ruth",
                            "number" => 3,
                            "average" => ".342"
                        ),
                        array(
                            "first_name" => "Lou",
                            "last_name" => "Gehrig",
                            "number" => "4",
                            "average" => .340
                        )
                    )
                )
            )
        );
    }
}
