<?php
//
//  Module: AliasTest.php - G.J. Watson
//    Desc: Tests for Alias Class
// Version: 1.00
//

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once("Alias.php");

final class AliasTest extends TestCase {

    private $alias;

    private $testAlias;
    private $testName;
    private $testTime;

    protected function setUp() {
        $this->testAlias = 66;
        $this->testName  = "Test name";
        $this->testTime  = "Time";
        $this->alias     = new Alias($this->testAlias, $this->testName, $this->testTime);
    }

    protected function tearDown() {
        $this->alias = NULL;
    }

    public function testAliasConstructorWorks() {
        print("\nFunction: testAliasConstructorWorks\n");
        // test everything set as expected in object
        $this->assertEquals($this->testAlias, $this->alias->getAliasID());
        $this->assertEquals(0,                strcmp($this->testName, $this->alias->getAliasName()));
        $this->assertEquals(0,                strcmp($this->testTime, $this->alias->getTimeAdded()));
    }
}
?>
