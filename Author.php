<?php
//
// Program: Quote.php (2017-10-25) G.J. Watson
//
// Purpose: Author Object
//
// Date       Version Note
// ========== ======= ====================================================
// 2017-10-25 v0.01   First cut of code
//

class Author {
    public $author_id;
    public $author_name;
    public $author_period;
    public $quotes;

    // JSON supplied
    private function __construct1($arg1) {
        $decode     = json_decode($arg1);
        $this->author_id     = $decode->author_id;
        $this->author_name   = $decode->author_name;
        $this->author_period = $decode->author_period;
        $this->quotes        = $decode->quotes;
    }

    // New quote (we have some data)
    private function __construct2($arg1, $arg2, $arg3) {
        $this->author_id     = $arg1;
        $this->author_name   = $arg2;
        $this->author_period = $arg3;
        $this->quotes        = array();
    }

    // Blank quote
    private function __construct3() {
        $this->author_id     = -1;
        $this->author_name   = "";
        $this->author_period = -1;
        $this->quotes        = array();
    }

    // we may be created using a json fragment or obj values
    public function __construct() {
        $argv = func_get_args();
        switch(func_num_args()) {
            case 1:
                self::__construct1($argv[0]);
                break;
            case 3:
                self::__construct2($argv[0], $argv[1], $argv[2]);
                break;
            default:
                self::__construct3();
                break;
        }
    }
}
?>
