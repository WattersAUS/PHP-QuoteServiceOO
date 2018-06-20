<?php
//
//  Module: AuthorTest.php - G.J. Watson
//    Desc: Tests for Author Class
// Version: 1.01
//

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

set_include_path("../../lib");

require_once("ServiceException.php");
require_once("Author.php");
require_once("Quote.php");

final class AuthorTest extends TestCase {

    private $author;

    // author setup vars
    private $testAuthor;
    private $testName;
    private $testPerd;
    private $testTime;

    //quote setup vars
    private $testQuote1;
    private $testText1;
    private $testUsed1;
    private $testQTime1;

    private $testQuote2;
    private $testText2;
    private $testUsed2;
    private $testQTime2;

    protected function setUp() {
        $this->testAuthor = 10;
        $this->testName   = "Author Name";
        $this->testPerd   = "Period";
        $this->testTime   = "Time";
        $this->author     = new Author($this->testAuthor, $this->testName, $this->testPerd, $this->testTime);

        // set vars for quote creation
        $this->testQuote1 = 33;
        $this->testText1  = "Quote text 1";
        $this->testUsed1  = 444;
        $this->testQTime1 = "Time 1";

        $this->testQuote2 = 44;
        $this->testText2  = "Quote text 2";
        $this->testUsed2  = 555;
        $this->testQTime2 = "Time2";
    }

    protected function tearDown() {
        $this->author = NULL;
    }

    public function testAuthorConstructorWorks() {
        print("\nTEST: testAuthorConstructorWorks\n");
        print("Checking initial object set up correctly\n");
        $this->assertEquals($this->testAuthor,         $this->author->getAuthorID());
        $this->assertEquals(0, strcmp($this->testName, $this->author->getAuthorName()));
        $this->assertEquals(0, strcmp($this->testPerd, $this->author->getAuthorPeriod()));
        $this->assertEquals(0, strcmp($this->testTime, $this->author->getTimeAdded()));
        return;
    }

    public function testAuthorAddQuoteWorks() {
        print("\nTEST: testAuthorAddQuoteWorks\n");
        print("Checking initial object set up correctly\n");
        $this->assertEquals($this->testAuthor,         $this->author->getAuthorID());
        $this->assertEquals(0, strcmp($this->testName, $this->author->getAuthorName()));
        $this->assertEquals(0, strcmp($this->testPerd, $this->author->getAuthorPeriod()));
        $this->assertEquals(0, strcmp($this->testTime, $this->author->getTimeAdded()));

        // create a quote and add to the author
        $quote1     = new Quote($this->testQuote1, $this->testText1, $this->testUsed1, $this->testQTime1);
        $this->author->addQuote($quote1);

        $quote2     = new Quote($this->testQuote2, $this->testText2, $this->testUsed2, $this->testQTime2);
        $this->author->addQuote($quote2);

        // retrieve the added quotes as an array
        $arr = $this->author->getQuotesAsArray();

        print("Checking quote 1 set up as expected\n");
        $item = $arr[0];
        $this->assertEquals($this->testQuote1, $item["quote_id"]);
        $this->assertEquals(0, strcmp($this->testText1, $item["quote_text"]));
        $this->assertEquals($this->testUsed1,  $item["times_used"]);
        $this->assertEquals(0, strcmp($this->testQTime1, $item["added"]));

        print("Checking quote 2 set up as expected\n");
        $item = $arr[1];
        $this->assertEquals($this->testQuote2, $item["quote_id"]);
        $this->assertEquals(0, strcmp($this->testText2, $item["quote_text"]));
        $this->assertEquals($this->testUsed2,  $item["times_used"]);
        $this->assertEquals(0, strcmp($this->testQTime2, $item["added"]));
    }

    // public function testAuthorGetRandomQuoteThrowsNoQuotesError() {
    //     try {
    //         print("\nTEST: testAuthorGetRandomQuoteThrowsNoQuotesError\n");
    //         $arr = $this->author->getRandomQuoteAsArray();
    //         if (sizeof($arr) == 0) {
    //             throw new ServiceException(AUTHORNOQUOTES["message"], AUTHORNOQUOTES["code"]);
    //         }
    //         $this->assertEquals(1, 0);
    //     } catch (ServiceException $e) {
    //         // Should be caught here
    //         print("ServiceException: No quotes error caught successfully!\n");
    //         $result = print($e->jsonString());
    //         $this->assertEquals(1, $result);
    //     } catch (Exception $e) {
    //        // And not here
    //        $result = print("Caught as normal exception!");
    //        $this->assertNotEquals(1, $result);
    //     }
    // }

    // public function testAuthorGetRandomQuoteReturnsQuoteAsArray() {
    //     try {
    //         print("\nTEST: testAuthorGetRandomQuoteReturnsQuote\n");
    //         $quote1 = new Quote($this->testQuote1, $this->testText1, $this->testUsed1, $this->testQTime1);
    //         $this->author->addQuote($quote1);
    //         $arr = $this->author->getRandomQuoteAsArray();
    //         if (sizeof($arr) == 0) {
    //             throw new ServiceException(AUTHORNOQUOTES["message"], AUTHORNOQUOTES["code"]);
    //         }
    //         print("Random quote returned (converted to JSON) = ".json_encode($arr));
    //         $this->assertEquals(1, (sizeof($arr) == 4));
    //         $this->assertEquals($this->testQuote1, $arr["quote_id"]);
    //         $this->assertEquals(0,                 strcmp($this->testText1, $arr["quote_text"]));
    //         $this->assertEquals($this->testUsed1,  $arr["times_used"]);
    //         $this->assertEquals(0,                 strcmp($this->testQTime1, $arr["added"]));
    //     } catch (ServiceException $e) {
    //         // Should be caught here
    //         print("ServiceException: No quotes error caught this shouldn't happen here!\n");
    //         $result = print($e->jsonString());
    //         $this->assertEquals(0, $result);
    //     } catch (Exception $e) {
    //        // And not here
    //        $result = print("Caught as normal exception!");
    //        $this->assertNotEquals(1, $result);
    //     }
    // }

    public function testAuthorGetAsArray() {
        print("\nTEST: testAuthorGetAsArray\n");
        $arr = $this->author->getAuthorAsArray();
        print("Author returned (converted to JSON) = ".json_encode($arr));
        $this->assertEquals(1, (sizeof($arr) == 4));
        $this->assertEquals($this->testAuthor,         $arr["author_id"]);
        $this->assertEquals(0, strcmp($this->testName, $arr["author_name"]));
        $this->assertEquals(0, strcmp($this->testPerd, $arr["author_period"]));
        $this->assertEquals(0, strcmp($this->testTime, $arr["added"]));
    }

    public function testAuthorGetWithAllQuotesAsArray() {
        print("\nTEST: testAuthorGetWithAllQuotesAsArray\n");
        $arr = $this->author->getAuthorAsArray();
        print("Author returned (converted to JSON) = ".json_encode($arr));
        $this->assertEquals(1, (sizeof($arr) == 4));
        $this->assertEquals($this->testAuthor,         $arr["author_id"]);
        $this->assertEquals(0, strcmp($this->testName, $arr["author_name"]));
        $this->assertEquals(0, strcmp($this->testPerd, $arr["author_period"]));
        $this->assertEquals(0, strcmp($this->testTime, $arr["added"]));

        try {
            $quote1 = new Quote($this->testQuote1, $this->testText1, $this->testUsed1, $this->testQTime1);
            $this->author->addQuote($quote1);
            $quote2 = new Quote($this->testQuote2, $this->testText2, $this->testUsed2, $this->testQTime2);
            $this->author->addQuote($quote2);

            $arr = $this->author->getAuthorWithAllQuotesAsArray();
            print("Author and all quotes returned (converted to JSON) = ".json_encode($arr));
            $this->assertEquals(1, (sizeof($arr) == 5));

            $this->assertEquals($this->testAuthor,         $arr["author_id"]);
            $this->assertEquals(0, strcmp($this->testName, $arr["author_name"]));
            $this->assertEquals(0, strcmp($this->testPerd, $arr["author_period"]));
            $this->assertEquals(0, strcmp($this->testTime, $arr["added"]));

            $quotes = $arr["quotes"];
            $this->assertEquals($this->testQuote1, $quotes[0]["quote_id"]);
            $this->assertEquals(0,                 strcmp($this->testText1, $quotes[0]["quote_text"]));
            $this->assertEquals($this->testUsed1,  $quotes[0]["times_used"]);
            $this->assertEquals(0,                 strcmp($this->testQTime1, $quotes[0]["added"]));

            $this->assertEquals($this->testQuote2, $quotes[1]["quote_id"]);
            $this->assertEquals(0,                 strcmp($this->testText2, $quotes[1]["quote_text"]));
            $this->assertEquals($this->testUsed2,  $quotes[1]["times_used"]);
            $this->assertEquals(0,                 strcmp($this->testQTime2, $quotes[1]["added"]));
        } catch (ServiceException $e) {
            // Should be caught here
            print("ServiceException: No quotes error caught this shouldn't happen here!\n");
            $result = print($e->jsonString());
            $this->assertEquals(0, $result);
        } catch (Exception $e) {
           // And not here
           $result = print("Caught as normal exception!");
           $this->assertNotEquals(1, $result);
        }
    }

    public function testAuthorGetWithSelectedQuoteAsArray() {
        print("\nTEST: testAuthorGetWithSelectedQuoteAsArray\n");
        $arr = $this->author->getAuthorAsArray();
        print("Author returned (converted to JSON) = ".json_encode($arr));
        $this->assertEquals(1, (sizeof($arr) == 4));
        $this->assertEquals($this->testAuthor,         $arr["author_id"]);
        $this->assertEquals(0, strcmp($this->testName, $arr["author_name"]));
        $this->assertEquals(0, strcmp($this->testPerd, $arr["author_period"]));
        $this->assertEquals(0, strcmp($this->testTime, $arr["added"]));

        try {
            $quote1 = new Quote($this->testQuote1, $this->testText1, $this->testUsed1, $this->testQTime1);
            $this->author->addQuote($quote1);

            $arr = $this->author->getAuthorWithSelectedQuoteAsArray(0);

            print("Author and all quotes returned (converted to JSON) = ".json_encode($arr));
            $this->assertEquals(1, (sizeof($arr) == 5));

            $this->assertEquals($this->testAuthor,         $arr["author_id"]);
            $this->assertEquals(0, strcmp($this->testName, $arr["author_name"]));
            $this->assertEquals(0, strcmp($this->testPerd, $arr["author_period"]));
            $this->assertEquals(0, strcmp($this->testTime, $arr["added"]));

            $quote = $arr["quote"];
            $this->assertEquals($this->testQuote1, $quote["quote_id"]);
            $this->assertEquals(0,                 strcmp($this->testText1, $quote["quote_text"]));
            $this->assertEquals($this->testUsed1,  $quote["times_used"]);
            $this->assertEquals(0,                 strcmp($this->testQTime1, $quote["added"]));
        } catch (ServiceException $e) {
            // Should be caught here
            print("ServiceException: No quotes error caught this shouldn't happen here!\n");
            $result = print($e->jsonString());
            $this->assertEquals(0, $result);
        } catch (Exception $e) {
           // And not here
           $result = print("Caught as normal exception!");
           $this->assertNotEquals(1, $result);
        }

    }
}
?>
