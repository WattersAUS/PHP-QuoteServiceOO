<?php
//
//  Module: UpdateRandomQuoteTimesUsed.php - G.J. Watson
//    Desc: For the selected quote / access update the times used
// Version: 1.00
//

function updateRandomQuoteTimesUsed($db, $accessId, $quoteId) {
    try {
        $sql  = "UPDATE quote_access SET times_used = times_used + 1";
        $sql .= " WHERE access_ident = ".$accessId." AND quote_id = ".$quoteId;
        $db->update($sql);
    } catch (mysqli_sql_exception $e) {
        throw new ServiceException(DBQUERYERROR["message"], DBQUERYERROR["code"]);
    }
    return;
}
?>