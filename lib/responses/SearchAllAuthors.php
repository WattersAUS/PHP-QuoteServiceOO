<?php
//
//  Module: SearchAllAuthors.php - G.J. Watson
//    Desc: Return all authors matching search as an array object
// Version: 1.00
//

function searchAllAuthors($db, $searchString) {
    $arr = [];
    $authors = $db->select(searchAuthorsSQL($searchString));
    while ($row = $authors->fetch_array(MYSQLI_ASSOC)) {
        $author = new Author($row["author_id"], $row["author_name"], $row["author_period"], $row["author_added_when"]);
        $aliases = $db->select(getAuthorAliasesSQL($author->getAuthorID()));
        while ($row = $aliases->fetch_array(MYSQLI_ASSOC)) {
            $alias = new Alias($row["alias_id"], $row["alias_name"], $row["alias_added_when"]);
            $author->addAlias($alias);
        }
        array_push($arr, $author->getAuthorAsArray());
    }
    if (sizeof($arr) == 0) {
        throw new ServiceException(ACTIVEAUTHORNOTFOUND["message"], ACTIVEAUTHORNOTFOUND["code"]);        
    }
    return $arr;
}
?>
