<?php
//
//  Module: GetAllAuthorsTest.php - G.J. Watson
//    Desc: Tests for GetAllAuthors
// Version: 1.00
//

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

set_include_path("../");

require_once("Common.php");
require_once("Database.php");
require_once("JsonBuilder.php");
require_once("ServiceException.php");

require_once("objects/Author.php");

require_once("GetAllAuthors.php");

final class GetAllAuthorsTest extends TestCase {

    private $db;
    private $common;

    private $quotedb;
    private $quoteuser;
    private $quotepwd;
    private $quoteip;

    protected function setUp() {
        $this->db        = NULL;
        $this->quotedb   = "quotes";
        $this->quoteuser = "shinyide2_user";
        $this->quotepwd  = "shinyide2_user";
        $this->quoteip   = "127.0.0.1";
    }

    protected function tearDown() {
        $this->error = NULL;
    }

    public function testGetAllAuthors() {
        try {
            print("\n\nTEST: testGetAllAuthors\n");
            $this->common = new Common();
            $this->db     = new Database($this->quotedb,$this->quoteuser,$this->quotepwd, $this->quoteip);
            $this->db->connect();
            $arr = getAllAuthors($this->db, $this->common);
            print("Author array returned with ".sizeof($arr)." entries...");
            $this->assertNotEquals(0, sizeof($arr));
        } catch (ServiceException $e) {
            // Should be caught here
            $this->assertEquals(1, print($e->jsonString()));
        } catch (Exception $e) {
            // And not here
            $this->assertEquals(0, print("A normal exception has occured!"));
        }
    }
}
?>

