<?php
//
//  Module: GetNewQuoteServiceToken.php - G.J. Watson
//    Desc: Request a new token from the system
// Version: 1.01
//

function getNewQuoteServiceToken($db, $ipAddress) {
    $query = "SELECT newaccesstoken('tokenrequest".$ipAddress."') AS nt";
    $newtoken = $db->select($query);
    if ($row = $newtoken->fetch_array(MYSQLI_ASSOC)) {
        return $row["nt"];
    }
    throw new ServiceException(TOKENALLOCFAILURE["message"], TOKENALLOCFAILURE["code"]);        
}
?>
