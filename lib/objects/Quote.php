<?php
//
//  Module: Quote.php - G.J. Watson
//    Desc: Quote Object
// Version: 1.00
//

final class Quote {
    private $quote_id;
    private $quote_text;
    private $times_used; // updating handled in DB
    private $added;
    private $used;

    public function __construct($arg1, $arg2, $arg3, $arg4) {
        $this->quote_id   = $arg1;
        $this->quote_text = $arg2;
        $this->times_used = $arg3;
        $this->added      = $arg4;
        $this->used       = FALSE;
    }

    public function getQuoteID() {
        return $this->quote_id;
    }

    public function getQuoteText() {
        return $this->quote_text;
    }

    public function getTimesUsed() {
        return $this->times_used;
    }

    public function getTimeAdded() {
        return $this->added;
    }

    public function setUsed() {
        $this->used = TRUE;
        return;
    }
}
?>
