<?php
//
// Program: Quote.php (2017-10-25) G.J. Watson
//
// Purpose: Quote Object
//
// Date       Version Note
// ========== ======= ====================================================
// 2017-10-25 v0.01   First cut of code
//

class Quote {
    public $quote_id;
    public $quote_text;
    public $times_used; // updating handled in DB

    // JSON supplied
    private function __construct1($arg1) {
        $decode     = json_decode($arg1);
        $this->quote_id   = $decode->quote_id;
        $this->quote_text = $decode->quote_text;
        $this->times_used = $decode->times_used;
    }

    // New quote (we have some data)
    private function __construct2($arg1, $arg2) {
        $this->quote_id   = $arg1;
        $this->quote_text = $arg2;
        $this->times_used = 0;
    }

    // Blank quote
    private function __construct3() {
        $this->quote_id   = -1;
        $this->quote_text = "";
        $this->times_used = -1;
    }

    // we may be created using a json fragment or obj values
    public function __construct() {
        $argv = func_get_args();
        switch(func_num_args()) {
            case 1:
                self::__construct1($argv[0]);
                break;
            case 2:
                self::__construct2($argv[0], $argv[1]);
                break;
            default:
                self::__construct3();
                break;
        }
    }
}
?>
