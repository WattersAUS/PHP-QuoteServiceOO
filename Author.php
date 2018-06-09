<?php
//
//  Module: Author.php - G.J. Watson
//    Desc: Author Object
// Version: 1.00
//

require_once("ServiceException.php");
require_once("Quote.php");

final class Author {
    private $author_id;
    private $author_name;
    private $author_period; // updating handled in DB
    private $added;

    private $quotes;

    // New quote (we have some data)
    public function __construct($arg1, $arg2, $arg3, $arg4) {
        $this->author_id         = $arg1;
        $this->author_name       = $arg2;
        $this->author_period     = $arg3;
        $this->added             = $arg4;
        $this->quotes            = [];
    }

    public function addQuote($quote) {
        array_push($this->quotes, $quote);
    }

    public function getAuthorID() {
        return $this->author_id;
    }

    public function getAuthorName() {
        return $this->author_name;
    }

    public function getAuthorPeriod() {
        return $this->author_period;
    }

    public function getTimeAdded() {
        return $this->added;
    }

    public function getQuotesAsArray() {
        $arr = [];
        foreach ($this->quotes as $quote) {
            $item               = [];
            $item["quote_id"]   = $quote->getQuoteID();
            $item["quote_text"] = $quote->getQuoteText();
            $item["times_used"] = $quote->getTimesUsed();
            $item["added"]      = $quote->getTimeAdded();
            array_push($arr, $item);
        }
        return $arr;
    }

    public function getRandomQuoteAsArray() {
        if (sizeof($this->quotes) == 0) {
            throw new ServiceException(AUTHORNOQUOTES["message"], AUTHORNOQUOTES["code"]);
        }
        // find the lowest times used count, and store those quotes
        $lowest = -1;
        $arr    = [];
        foreach ($this->quotes as $quote) {
            if ($lowest == -1) {
                $lowest = $quote->getTimesUsed();
                array_push($arr, $quote);
            } elseif ($lowest > $quote->getTimesUsed()) {
                $lowest = $quote->getTimesUsed();
                $arr    = [];
                array_push($arr, $quote);
            } elseif ($lowest == $quote->getTimesUsed()) {
                array_push($arr, $quote);
            }
        }
        // select a random quote to use
        $select = rand(0, sizeof($arr) - 1);
        $item   = $arr[$select]->getQuoteAsArray();
        // mark the quote as used
        foreach ($this->quotes as $quote) {
            if ($item["quote_id"] == $quote->getQuoteID()) {
                $quote->setUsed();
            }
        }
        return $item;
    }

    public function getAuthorAsArray() {
        $obj["author_id"]     = $this->author_id;
        $obj["author_name"]   = $this->author_name;
        $obj["author_period"] = $this->author_period;
        $obj["added"]         = $this->added;
        return $obj;
    }

    public function getAuthorWithAllQuotesAsArray() {
        $obj["author_id"]     = $this->author_id;
        $obj["author_name"]   = $this->author_name;
        $obj["author_period"] = $this->author_period;
        $obj["added"]         = $this->added;
        $obj["quotes"]        = $this->getQuotesAsArray();
        return $obj;
    }

    public function getAuthorWithRandomQuoteAsArray() {
        $obj["author_id"]     = $this->author_id;
        $obj["author_name"]   = $this->author_name;
        $obj["author_period"] = $this->author_period;
        $obj["added"]         = $this->added;
        $obj["quote"]         = $this->getRandomQuoteAsArray();
        return $obj;
    }
}
?>
