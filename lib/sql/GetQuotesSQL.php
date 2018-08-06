<?php
//
//  Module: GetQuotesSQL.php - G.J. Watson
//    Desc: Common SQL Statements used for Quotes DB
// Version: 1.00
//

function getBasicAuthorSQL() {
    $sql  = "SELECT au.id AS author_id, au.name AS author_name, au.match_text AS author_match_text, au.period AS author_period, au.added AS author_added_when";
    $sql .= " FROM author au";
    $sql .= " WHERE EXISTS (SELECT 1 FROM quote q WHERE q.author_id = au.id)";
    return $sql;    
}

function getAuthorsSQL() {
    // we're only interested in authors who have quotes
    return getBasicAuthorSQL();
}

function getAuthorByIdSQL($id) {
    // we're only interested in authors who have quotes
    $sql  = getBasicAuthorSQL();
    $sql .= " AND au.id = ".$id;
    return $sql;
}

function getAuthorAliasesSQL($id) {
    $sql  = "SELECT al.id AS alias_id, al.name AS alias_name, al.match_text AS alias_match_text, al.added AS alias_added_when";
    $sql .= " FROM author_alias al";
    $sql .= " WHERE al.author_id = ".$id;
    return $sql;
}

function getAuthorQuotesSQL($id) {
    $sql  = "SELECT q.id AS quote_id, q.quote_text AS quote_text, q.match_text AS quote_match_text, q.times_used AS quote_times_used, q.last_used_by AS quote_last_used_by, q.added AS quote_added_when";
    $sql .= " FROM quote q";
    $sql .= " WHERE q.author_id = ".$id;
    return $sql;
}
?>
