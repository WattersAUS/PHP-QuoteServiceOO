<?php
//
//  Module: AuthorQuotesSQL.php - G.J. Watson
//    Desc: Common SQL Statements used for Quotes DB
// Version: 1.09
//

// Authors

function getBasicAuthorFields() {
    $sql  = " au.id AS author_id, au.name AS author_name, au.md5_text AS author_md5_text";
    $sql .= ", au.period AS author_period, au.added AS author_added_when ";
    return $sql;
}

function getBasicAuthorSQL() {
    $sql  = "SELECT ";
    $sql .= getBasicAuthorFields();
    $sql .= " FROM author au";
    $sql .= " WHERE EXISTS (SELECT 1 FROM quote q WHERE q.author_id = au.id)";
    return $sql;
}

function getAuthorsSQL() {
    return getBasicAuthorSQL();
}

function searchAuthorsSQL($searchString) {
    $sql  = getBasicAuthorSQL();
    $sql .= " AND plaintext(au.name) LIKE CONCAT('%',plaintext('".$searchString."'),'%')";
    return $sql;
}

function getAuthorByIdSQL($id) {
    $sql  = getBasicAuthorSQL();
    $sql .= " AND au.id = ".$id;
    return $sql;
}

function getAuthorFromDateSQL($newTime) {
    $sql  = "SELECT ";
    $sql .= getBasicAuthorFields();
    $sql .= " FROM author au";
    $sql .= " WHERE EXISTS (SELECT 1 FROM quote q WHERE q.author_id = au.id AND q.added > '".$newTime."')";
    return $sql;
}

function getAuthorsFromDateSQL($newTime) {
    return getAuthorFromDateSQL($newTime);
}

// AuthorAliases

function getBasicAuthorAliasesFields() {
    $sql  = " al.id AS alias_id, al.name AS alias_name, al.md5_text AS alias_md5_text, al.added AS alias_added_when ";
    return $sql;
}

function getAuthorAliasesSQL($id) {
    $sql  = "SELECT ";
    $sql .= getBasicAuthorAliasesFields();
    $sql .= " FROM author_alias al";
    $sql .= " WHERE al.author_id = ".$id;
    return $sql;
}

// Quotes

function getBasicQuotesFields() {
    $sql  = " q.id AS quote_id, q.quote_text AS quote_text, q.md5_text AS quote_md5_text";
    $sql .= ", q.times_used AS quote_times_used, q.last_used_by AS quote_last_used_by, q.added AS quote_added_when ";
    return $sql;
}

function getAuthorQuotesSQL($id) {
    $sql  = "SELECT ";
    $sql .= getBasicQuotesFields();
    $sql .= " FROM quote q";
    $sql .= " WHERE q.author_id = ".$id;
    return $sql;
}
function getAuthorQuotesFromDate($id, $newTime) {
    $sql  = "SELECT ";
    $sql .= getBasicQuotesFields();
    $sql .= " FROM quote q";
    $sql .= " WHERE q.author_id = ".$id;
    $sql .= " AND q.added > '".$newTime."'";
    return $sql;
}

// Author - Quotes Joined

function getBasicAuthorQuotesJoinedSQL() {
    $sql  = getBasicAuthorFields() + ", "; 
    $sql .= getBasicQuotesFields();
    $sql .= " FROM author au INNER JOIN quote q ON au.id = q.author_id";
    return $sql;
}

function getAuthorsQuotesJoinedSQL() {
    $sql  = "SELECT ";
    $sql .= getBasicAuthorQuotesJoinedSQL();
    $sql .= " ORDER BY au.id";
    return $sql;
}

function getAuthorQuotesJoinedFromDateSQL($newTime) {
    $sql  = "SELECT ";
    $sql .= getBasicAuthorQuotesJoinedSQL();
    $sql .= " WHERE q.added > '".$newTime."'";
    $sql .= " ORDER BY au.id";
    return $sql;
}
?>