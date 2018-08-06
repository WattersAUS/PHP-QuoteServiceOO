<?php
//
//  Module: Author.php - G.J. Watson
//    Desc: Author Object
// Version: 1.03
//

require_once("Quote.php");

final class Author {
    private $author_id;
    private $author_name;
    private $author_period; // updating handled in DB
    private $added;

    private $aliases;
    private $quotes;

    // New quote (we have some data)
    public function __construct($arg1, $arg2, $arg3, $arg4) {
        $this->author_id         = $arg1;
        $this->author_name       = $arg2;
        $this->author_period     = $arg3;
        $this->added             = $arg4;
        $this->aliases           = [];
        $this->quotes            = [];
    }

    public function addAlias($alias) {
        array_push($this->aliases, $alias);
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

    public function getAliasesAsArray() {
        $arr = [];
        foreach ($this->aliases as $alias) {
            $item          = [];
            $item["id"]    = $alias->getAliasID();
            $item["name"]  = $alias->getAliasName();
            $item["added"] = $alias->getTimeAdded();
            array_push($arr, $item);
        }
        return $arr;
    }

    public function getQuotesAsArray() {
        $arr = [];
        foreach ($this->quotes as $quote) {
            $item          = [];
            $item["id"]    = $quote->getQuoteID();
            $item["text"]  = $quote->getQuoteText();
            $item["used"]  = $quote->getTimesUsed();
            $item["added"] = $quote->getTimeAdded();
            array_push($arr, $item);
        }
        return $arr;
    }

    public function getAuthorAsArray() {
        $obj["id"]      = $this->author_id;
        $obj["name"]    = $this->author_name;
        $obj["period"]  = $this->author_period;
        $obj["added"]   = $this->added;
        $obj["aliases"] = $this->getAliasesAsArray();
        return $obj;
    }

    public function getAuthorWithAllQuotesAsArray() {
        $obj["id"]      = $this->author_id;
        $obj["name"]    = $this->author_name;
        $obj["period"]  = $this->author_period;
        $obj["added"]   = $this->added;
        $obj["aliases"] = $this->getAliasesAsArray();
        $obj["quotes"]  = $this->getQuotesAsArray();
        return $obj;
    }

    public function getAuthorWithSelectedQuoteAsArray($selected) {
        $obj = $this->getAuthorAsArray();
        if (sizeof($this->quotes) > $selected) {
            $obj["quote"]["id"]    = $this->quotes[$selected]->getQuoteID();
            $obj["quote"]["text"]  = $this->quotes[$selected]->getQuoteText();
            $obj["quote"]["used"]  = $this->quotes[$selected]->getTimesUsed();
            $obj["quote"]["added"] = $this->quotes[$selected]->getTimeAdded();
        }
        return $obj;       
    }
}
?>
