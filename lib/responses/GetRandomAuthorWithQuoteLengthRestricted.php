<?php
//
//  Module: GetRandomAuthorWithQuoteLengthRestricted.php - G.J. Watson
//    Desc: Get a random quote to the requestor as a Json response
//          where the combined text length (author / quote) is XX
// Version: 1.01
//

function getMinimumTimesUsedForQuotesForUser($db, $accessId, $maxLength) {
    $sql  = "SELECT min(qa.times_used) AS min_times_used";
    $sql .= " FROM quote q";
    $sql .= " INNER JOIN quote_access qa ON q.id = qa.quote_id";
    $sql .= " WHERE qa.access_ident = ".$accessId;
    $min = $db->select($sql);
    if ($row = $min->fetch_array(MYSQLI_ASSOC)) {
        return $row["min_times_used"];
    }
    throw new ServiceException(DBQUERYERROR["message"], DBQUERYERROR["code"]);
}

function getQuotesMatchingMinumumTimesUsed($db, $accessId, $timesUsed, $maxLength) {
    $sql  = "SELECT au.id AS author_id, au.name AS author_name, au.md5_text AS author_md5_text, au.period AS author_period, au.added AS author_added_when";
    $sql .= ", q.id AS quote_id, q.quote_text AS quote_text, q.md5_text AS quote_md5_text, q.times_used AS quote_times_used, q.last_used_by AS quote_last_used_by, q.added AS quote_added_when";
    $sql .= " FROM author au";
    $sql .= " INNER JOIN quote q ON q.author_id = au.id";
    $sql .= " INNER JOIN quote_access qa ON qa.quote_id = q.id";
    $sql .= " WHERE qa.times_used = ".$timesUsed;
    $sql .= " AND qa.access_ident = ".$accessId;
    $sql .= " LIMIT 50";
    $records = [];
    $quotes = $db->select($sql);
    while ($row = $quotes->fetch_array(MYSQLI_ASSOC)) {
        array_push($records, $row);
    }
    if (sizeof($records) == 0) {
        throw new ServiceException(AUTHORNOQUOTES["message"], AUTHORNOQUOTES["code"]);
    }
    return $records;
}

function getRandomAuthorWithQuote($db, $access) {
    $timesUsed = getMinimumTimesUsedForQuotesForUser($db, $access->getUserID());
    $quotes = getQuotesMatchingMinumumTimesUsed($db, $access->getUserID(), $timesUsed);
    // select a random quote and build up the author obj
    $random = rand(0, sizeof($quotes) - 1);
    $quote = $quotes[$random];
    $author = new Author($quote["author_id"], $quote["author_name"], $quote["author_period"], $quote["author_added_when"]);
    $author->addQuote(new Quote($quote["quote_id"], $quote["quote_text"], $quote["quote_times_used"], $quote["quote_added_when"]));
    // now add the author aliases        
    $aliases = $db->select(getAuthorAliasesSQL($author->getAuthorID()));
    while ($row = $aliases->fetch_array(MYSQLI_ASSOC)) {
        $alias = new Alias($row["alias_id"], $row["alias_name"], $row["alias_added_when"]);
        $author->addAlias($alias);
    }
    // we've selected the random quote, now update times_used
    updateRandomQuoteTimesUsed($db, $access->getUserID(), $quote["quote_id"]);
    return $author->getAuthorWithSelectedQuoteAsArray(0);
}
?>