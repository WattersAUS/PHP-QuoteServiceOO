<?php
//
//  Module: getAllAuthorsWithQuotes.php - G.J. Watson
//    Desc: Get an author and their quotes to the requestor as a Json response
// Version: 1.01
//

function getAllAuthorsWithQuotes($db) {
    $arr = [];
    // we're only interested in authors who have quotes
    $sql  = "SELECT au.id AS author_id, au.name AS author_name, au.match_text AS author_match_text, au.period AS author_period, au.added AS author_added_when";
    $sql .= ", q.id AS quote_id, q.quote_text AS quote_text, q.match_text AS quote_match_text, q.times_used AS quote_times_used, q.last_used_by AS quote_last_used_by, q.added AS quote_added_when";
    $sql .= " FROM author au";
    $sql .= " INNER JOIN quote q ON q.author_id = au.id";
    $sql .= " ORDER BY au.id ASC, q.id ASC";
    // retrieve the quotes, remember to break when the author changes
    $old_id = -1;
    $author = NULL;
    $quotes = $db->select($sql);
    while ($row = $quotes->fetch_array(MYSQLI_ASSOC)) {
        if ($old_id != $row["author_id"]) {
            if ($old_id != -1) {
                array_push($arr, $author->getAuthorWithAllQuotesAsArray());
            }
            $old_id = $row["author_id"];
            $author = new Author($row["author_id"], $row["author_name"], $row["author_period"], $row["author_added_when"]);
        }
        $author->addQuote(new Quote($row["quote_id"], $row["quote_text"], $row["quote_times_used"], $row["quote_added_when"]));
    }
    if ($old_id == -1) {
        throw new ServiceException(ACTIVEAUTHORNOTFOUND["message"], ACTIVEAUTHORNOTFOUND["code"]);
    }
    array_push($arr, $author->getAuthorWithAllQuotesAsArray());
    return $arr;
}
?>