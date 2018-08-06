<?php
//
//  Module: QuoteTest.php - G.J. Watson
//    Desc: Tests for Quote Class
// Version: 1.00
//

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once("Quote.php");

final class QuoteTest extends TestCase {

    private $quote;

    private $testQuote;
    private $testText;
    private $testUsed;
    private $testTime;

    protected function setUp() {
        $this->testQuote = 44;
        $this->testText  = "Quote text";
        $this->testUsed  = 777;
        $this->testTime  = "Time";
        $this->quote     = new Quote($this->testQuote, $this->testText, $this->testUsed, $this->testTime);
    }

    protected function tearDown() {
        $this->quote = NULL;
    }

    public function testQuoteConstructorWorks() {
        print("\nFunction: testQuoteConstructorWorks\n");
        // test everything set as expected in object
        $this->assertEquals($this->testQuote, $this->quote->getQuoteID());
        $this->assertEquals(0,                strcmp($this->testText, $this->quote->getQuoteText()));
        $this->assertEquals($this->testUsed,  $this->quote->getTimesUsed());
        $this->assertEquals(0,                strcmp($this->testTime, $this->quote->getTimeAdded()));
    }
}
?>
