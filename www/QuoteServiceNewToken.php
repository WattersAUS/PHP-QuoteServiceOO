<?php
//
//  Module: QuoteServiceNewToken.php - G.J. Watson
//    Desc: Request for a new accesstoken
// Version: 1.00
//

    // first load up the common project code
    set_include_path("../lib");
    require_once("Common.php");
    require_once("Database.php");
    require_once("JsonBuilder.php");
    require_once("ServiceException.php");
    require_once("Validate.php");
    require_once("UserAccess.php");

    // functions to return json
    require_once("responses/GetNewQuoteServiceToken.php");

    // connection details for database
    require_once("connect/Quotes.php");

    //
    // generate a new access token and buld up the response
    //
    function getNewAccessToken($db, $generated, $ipAddress) {
        $version = "v1.00";
        $jsonObj = new JSONBuilder($version, "GetNewAccessToken", $generated, "token", getNewQuoteServiceToken($db, $ipAddress));
        return $jsonObj->getJson();
    }

    //
    // 1. do we have a valid token, and check it hasn't been abused
    // 2. get a new token
    // 3. log the access
    //

    $db       = new Database($database, $username, $password, $hostname);
    $htmlCode = 200;
    $htmlMess = "200 OK";
    $response = "";
    try {
        $common = new Common();
        $db->connect();
        // 1 - token check
        $check = new Validate();
        $check->variableCheck("token", MALFORMEDREQUEST["message"], MALFORMEDREQUEST["code"], 36, $_GET);
        $access = new UserAccess($_GET["token"]);
        $access->checkAccessAllowed($db);
        // 2 - routing
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                $check->ipAddressVariable("REMOTE_ADDR", MALFORMEDREQUEST["message"], MALFORMEDREQUEST["code"], $_SERVER);
                $response = getNewAccessToken($db, $common->getGeneratedDateTime(), $_SERVER["REMOTE_ADDR"]);
                break;
            case "POST":
            case "PUT":
            case "DELETE":
                throw new ServiceException(HTTPSUPPORTERROR["message"], HTTPSUPPORTERROR["code"]);
                break;
            default:
                throw new ServiceException(HTTPMETHODERROR["message"], HTTPMETHODERROR["code"]);
        }
        // 3 - log req
        $access->logRequest($db, $_SERVER['REMOTE_ADDR']);
        $db->close();
    } catch (ServiceException $e) {
        // set the html code and message depending on Exception
        $htmlCode = $e->getHTMLResponseCode();
        $htmlMess = $e->getHTMLResponseMsg();
        $response = $e->jsonString();
    } catch (Exception $e) {
        throw new ServiceException(UNKNOWNERROR["message"], UNKNOWNERROR["code"]);
    } finally {
        // send the result of the req back
        header_remove();
        http_response_code($htmlCode);
        header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
        header("Content-type: application/json;charset=utf-8");
        header("Status: ".$htmlMess);
        echo $response;
    }
?>
