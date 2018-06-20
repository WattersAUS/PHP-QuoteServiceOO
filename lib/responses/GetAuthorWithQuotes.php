<?php
//
//  Module: GetAuthorWithQuotes.php - G.J. Watson
//    Desc: Get an author and their quotes to the requestor as an array
// Version: 1.00
//

function getAuthorWithQuotes($db, $common, $author_id) {
	$arr = [];
	// we're only interested in authors who have quotes
	$sql  = "SELECT au.id AS author_id, au.name AS author_name, au.match_text AS author_match_text, au.period AS author_period, au.added AS author_added_when";
	$sql .= ", q.id AS quote_id, q.quote_text AS quote_text, q.match_text AS quote_match_text, q.times_used AS quote_times_used, q.last_used_by AS quote_last_used_by, q.added AS quote_added_when";
	$sql .= " FROM author au";
	$sql .= " INNER JOIN quote q ON q.author_id = au.id";
	$sql .= " WHERE au.id = ".$author_id;
	$sql .= " ORDER BY au.id ASC, q.id ASC";
	$author = NULL;
	$quotes = $db->select($sql);
	if ($row = $quotes->fetch_array(MYSQLI_ASSOC)) {
		$common->logINFOMessage("Adding Author (".$row["author_name"].") to results");
		$author = new Author($row["author_id"], $row["author_name"], $row["author_period"], $row["author_added_when"]);
		$author->addQuote(new Quote($row["quote_id"], $row["quote_text"], $row["quote_times_used"], $row["quote_added_when"]));
		array_push($arr, $author->getAuthorWithAllQuotesAsArray());
	}
	return $arr;
}
?>