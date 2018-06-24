<?php
//
//  Module: GetRandomAuthorWithQuote.php - G.J. Watson
//    Desc: Get a random quote to the requestor as a Json response
// Version: 1.00
//

function updateRandomQuoteTimesUsed($db, $accessId, $quoteId) {
    try {
        $sql = "UPDATE quote_access SET times_used = times_used + 1 WHERE access_ident = ".$accessId." AND quote_id = ".$quoteId;
        $db->update($sql);
    } catch (mysqli_sql_exception $e) {
        throw new ServiceException(DBQUERYERROR["message"], DBQUERYERROR["code"]);
    }
    return;
}

function getRandomAuthorWithQuote($db, $access) {
    $arr = "";
    // first get the minimum quote used times
    $sql  = "SELECT min(qa.times_used) AS min_times_used";
    $sql .= " FROM quote q";
    $sql .= " INNER JOIN quote_access qa ON q.id = qa.quote_id";
    $sql .= " WHERE qa.access_ident = ".$access->getUserID();
    $min = $db->select($sql);
    if ($row = $min->fetch_array(MYSQLI_ASSOC)) {
        $sql  = "SELECT au.id AS author_id, au.name AS author_name, au.match_text AS author_match_text, au.period AS author_period, au.added AS author_added_when";
        $sql .= ", q.id AS quote_id, q.quote_text AS quote_text, q.match_text AS quote_match_text, q.times_used AS quote_times_used, q.last_used_by AS quote_last_used_by, q.added AS quote_added_when";
        $sql .= " FROM author au";
        $sql .= " INNER JOIN quote q ON q.author_id = au.id";
        $sql .= " INNER JOIN quote_access qa ON qa.quote_id = q.id";
        $sql .= " WHERE qa.times_used = ".$row["min_times_used"];
        $sql .= " AND qa.access_ident = ".$access->getUserID();
        $sql .= " LIMIT 50";
        $recs = [];
        $quotes = $db->select($sql);
        while ($row = $quotes->fetch_array(MYSQLI_ASSOC)) {
            array_push($recs, $row);
        }
        if (sizeof($recs) == 0) {
            throw new ServiceException(ACTIVEAUTHORNOTFOUND["message"], ACTIVEAUTHORNOTFOUND["code"]);
        }
        $select = rand(0, sizeof($recs) - 1);
        $item   = $recs[$select];
        $author = new Author($item["author_id"], $item["author_name"], $item["author_period"], $item["author_added_when"]);
        $author->addQuote(new Quote($item["quote_id"], $item["quote_text"], $item["quote_times_used"], $item["quote_added_when"]));
        $arr = $author->getAuthorWithSelectedQuoteAsArray(0);
        // we've selected the random quote, now update times_used
        updateRandomQuoteTimesUsed($db, $access->getUserID(), $item["quote_id"]);
    }
    return $arr;
}
?>