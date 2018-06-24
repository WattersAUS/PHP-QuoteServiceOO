<?php
//
//  Module: GetAllAuthors.php - G.J. Watson
//    Desc: Return all authors as an array object
// Version: 1.01
//

function getAllAuthors($db) {
    $arr = [];
    // we're only interested in authors who have quotes
    $sql  = "SELECT au.id AS author_id, au.name AS author_name, au.match_text AS author_match_text, au.period AS author_period, au.added AS author_added_when";
    $sql .= " FROM author au";
    $sql .= " WHERE EXISTS (SELECT 1 FROM quote q WHERE q.author_id = au.id)";
    $authors = $db->select($sql);
    while ($row = $authors->fetch_array(MYSQLI_ASSOC)) {
        $author = new Author($row["author_id"], $row["author_name"], $row["author_period"], $row["author_added_when"]);
        array_push($arr, $author->getAuthorAsArray());
    }
    if (sizeof($arr) == 0) {
        throw new ServiceException(ACTIVEAUTHORNOTFOUND["message"], ACTIVEAUTHORNOTFOUND["code"]);        
    }
    return $arr;
}
?>
