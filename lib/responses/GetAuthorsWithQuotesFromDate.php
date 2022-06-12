<?php
//
//  Module: getAuthorsWithQuotesFromDate.php - G.J. Watson
//    Desc: Get an author and their quotes to the requestor as a Json response based on a 'newer date time' parameter
// Version: 1.03
//

function getAuthorsWithQuotesFromDate($db, $fromDateTime) {
    $arr = [];
    $authors = $db->select(getAuthorsFromDateSQL($fromDateTime));
    while ($row = $authors->fetch_array(MYSQLI_ASSOC)) {
        $author = new Author($row["author_id"], $row["author_name"], $row["author_period"], $row["author_added_when"]);
        $aliases = $db->select(getAuthorAliasesSQL($author->getAuthorID()));
        while ($row = $aliases->fetch_array(MYSQLI_ASSOC)) {
            $author->addAlias(new Alias($row["alias_id"], $row["alias_name"], $row["alias_added_when"]));
        }
        $quotes = $db->select(getAuthorQuotesFromDate($author->getAuthorID(), $fromDateTime));
        while ($row = $quotes->fetch_array(MYSQLI_ASSOC)) {
            $author->addQuote(new Quote($row["quote_id"], $row["quote_text"], $row["quote_times_used"], $row["quote_added_when"]));
        }
        array_push($arr, $author->getAuthorWithAllQuotesAsArray());
    }
    if (sizeof($arr) == 0) {
        throw new ServiceException(NONEWQUOTESFOUND["message"], NONEWQUOTESFOUND["code"]);        
    }
    return $arr;
}
?>