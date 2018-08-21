<?php
//
//  Module: SearchAllAuthorsWithQuotes.php - G.J. Watson
//    Desc: Get an author and their quotes to the requestor as a Json response
// Version: 1.00
//

function SearchAllAuthorsWithQuotes($db, $searchString) {
    $arr = [];
    $authors = $db->select(searchAuthorsSQL($searchString));
    while ($row = $authors->fetch_array(MYSQLI_ASSOC)) {
        $author = new Author($row["author_id"], $row["author_name"], $row["author_period"], $row["author_added_when"]);
        $aliases = $db->select(getAuthorAliasesSQL($author->getAuthorID()));
        while ($row = $aliases->fetch_array(MYSQLI_ASSOC)) {
            $author->addAlias(new Alias($row["alias_id"], $row["alias_name"], $row["alias_added_when"]));
        }
        $quotes = $db->select(getAuthorQuotesSQL($author->getAuthorID()));
        while ($row = $quotes->fetch_array(MYSQLI_ASSOC)) {
            $author->addQuote(new Quote($row["quote_id"], $row["quote_text"], $row["quote_times_used"], $row["quote_added_when"]));
        }
        array_push($arr, $author->getAuthorWithAllQuotesAsArray());
    }
    if (sizeof($arr) == 0) {
        throw new ServiceException(ACTIVEAUTHORNOTFOUND["message"], ACTIVEAUTHORNOTFOUND["code"]);        
    }
    return $arr;
}
?>
