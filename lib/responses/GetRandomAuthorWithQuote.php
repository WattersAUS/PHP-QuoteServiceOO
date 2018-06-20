<?php
//
//  Module: GetRandomAuthorWithQuote.php - G.J. Watson
//    Desc: Get a random quote to the requestor as a Json response
// Version: 1.00
//

function getRandomAuthorWithQuote($db, $common) {
	$arr = "";
	// first get the minimum quote used times
	$sql = "SELECT min(times_used) AS min_times_used FROM quote";
	$min = $db->select($sql);
	if ($row = $min->fetch_array(MYSQLI_ASSOC)) {
		$sql  = "SELECT au.id AS author_id, au.name AS author_name, au.match_text AS author_match_text, au.period AS author_period, au.added AS author_added_when";
		$sql .= ", q.id AS quote_id, q.quote_text AS quote_text, q.match_text AS quote_match_text, q.times_used AS quote_times_used, q.last_used_by AS quote_last_used_by, q.added AS quote_added_when";
		$sql .= " FROM author au";
		$sql .= " INNER JOIN quote q ON q.author_id = au.id";
		$sql .= " WHERE q.times_used = ".$row["min_times_used"];
		$sql .= " LIMIT 50";
		$recs = [];
		$quotes = $db->select($sql);
	    while ($row = $quotes->fetch_array(MYSQLI_ASSOC)) {
	    	array_push($recs, $row);
	    }
	    if (sizeof($recs) > 0) {
		    $select = rand(0, sizeof($recs) - 1);
    		$item   = $recs[$select];
			$common->logINFOMessage("Adding Author (".$item["author_name"].") to results");
			$author = new Author($item["author_id"], $item["author_name"], $item["author_period"], $item["author_added_when"]);
			$author->addQuote(new Quote($item["quote_id"], $item["quote_text"], $item["quote_times_used"], $item["quote_added_when"]));
			$arr = $author->getAuthorWithSelectedQuoteAsArray(0);
	    }
	}
	return $arr;
}
?>