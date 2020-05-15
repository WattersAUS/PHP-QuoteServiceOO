<?php
//
//  Module: QuoteServiceNewToken.php - G.J. Watson
//    Desc: Request for a new accesstoken
// Version: 1.01
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
        $version = "v1.01";
        $jsonObj = new JSONBuilder($version, "GetNewAccessToken", $generated, "token", getNewQuoteServiceToken($db, $ipAddress));
        return $jsonObj->getJson();
    }

    //
    // we need the user access token if it's valid.
    //
    function getAuthorisationTokenFromHeaders($check) {
        $authArray = getallheaders();
        if (! $check->checkVariableExistsInArray("Authorization", $authArray)) {
            throw new ServiceException(ACCESSTOKENMISSING["message"], ACCESSTOKENMISSING["code"]);
        }
        if (strlen($authArray["Authorization"]) <> 43) {
            throw new ServiceException(INCORRECTTOKENSUPPLIED["message"], INCORRECTTOKENSUPPLIED["code"]);
        }
        list($authType, $token) = explode(" ", $authArray["Authorization"], 2);
        if (strcasecmp($authType, "Bearer") <> 0) {
            throw new ServiceException(AUTHORISATIONFAILURE["message"], AUTHORISATIONFAILURE["code"]);
        }
        if (! $check->isValidGUID($token)) {
            throw new ServiceException(INCORRECTTOKENSUPPLIED["message"], INCORRECTTOKENSUPPLIED["code"]);
        }
        return $token;
    }

    //
    // 1. do we have a valid token, and check it hasn't been abused
    // 2. get a new token
    // 3. log the access
    //

    $db = new Database($database, $username, $password, $hostname);
    $htmlCode = 200;
    $htmlMess = "200 OK";
    $response = "";
    try {
        $common = new Common();
        $db->connect();
        //
        // 1 - token authorisation exists in https headers and is the right length/format, hasn't been abused etc
        //
        $check = new Validate();
        $token = getAuthorisationTokenFromHeaders($check);
        $access = new UserAccess($_GET["token"]);
        $access->checkAccessAllowed($db);
        // 2 - routing
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                if (! $check->isValidIpAddress($_SERVER['REMOTE_ADDR'])) {
                    throw new ServiceException(MALFORMEDREQUEST["message"], MALFORMEDREQUEST["code"]);
                }
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
