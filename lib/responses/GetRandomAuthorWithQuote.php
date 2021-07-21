<?php
//
//  Module: GetRandomAuthorWithQuote.php - G.J. Watson
//    Desc: Get a random quote to the requestor as a Json response
// Version: 1.06
//

function getRandomAuthorWithQuote($db, $access) {
    $arr = "";
    // first get the minimum quote used times
    $sql  = "SELECT MIN(qa.times_used) AS min_times_used";
    $sql .= " FROM quote q";
    $sql .= " INNER JOIN quote_access qa ON q.id = qa.quote_id";
    $sql .= " WHERE qa.access_ident = ".$access->getUserID();
    $min = $db->select($sql);
    if ($row = $min->fetch_array(MYSQLI_ASSOC)) {
        // now a set of quotes only having being used 'min_times_used' only
        $sql  = "SELECT au.id AS author_id, au.name AS author_name, au.md5_text AS author_md5_text, au.period AS author_period, au.added AS author_added_when";
        $sql .= ", q.id AS quote_id, q.quote_text AS quote_text, q.md5_text AS quote_md5_text, q.times_used AS quote_times_used, q.last_used_by AS quote_last_used_by, q.added AS quote_added_when";
        $sql .= " FROM author au";
        $sql .= " INNER JOIN quote q ON q.author_id = au.id";
        $sql .= " INNER JOIN quote_access qa ON qa.quote_id = q.id";
        $sql .= " WHERE qa.times_used = ".$row["min_times_used"];
        $sql .= " AND qa.access_ident = ".$access->getUserID();
        $sql .= " ORDER BY q.md5_text ASC";
        $sql .= " LIMIT 50";
        $recs = [];
        $quotes = $db->select($sql);
        while ($row = $quotes->fetch_array(MYSQLI_ASSOC)) {
            array_push($recs, $row);
        }
        if (sizeof($recs) == 0) {
            throw new ServiceException(ACTIVEAUTHORNOTFOUND["message"], ACTIVEAUTHORNOTFOUND["code"]);
        }
        // select a random one and build up the author / quote
        $select = rand(0, sizeof($recs) - 1);
        $item   = $recs[$select];
        $author = new Author($item["author_id"], $item["author_name"], $item["author_period"], $item["author_added_when"]);
        $author->addQuote(new Quote($item["quote_id"], $item["quote_text"], $item["quote_times_used"], $item["quote_added_when"]));
        // now add the author aliases        
        $aliases = $db->select(getAuthorAliasesSQL($author->getAuthorID()));
        while ($row = $aliases->fetch_array(MYSQLI_ASSOC)) {
            $alias = new Alias($row["alias_id"], $row["alias_name"], $row["alias_added_when"]);
            $author->addAlias($alias);
        }
        $arr = $author->getAuthorWithSelectedQuoteAsArray(0);
        // we've selected the random quote, now update times_used
        updateRandomQuoteTimesUsed($db, $access->getUserID(), $item["quote_id"]);
    }
    return $arr;
}
?>