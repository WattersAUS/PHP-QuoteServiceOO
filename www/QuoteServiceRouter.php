<?php
//
//  Module: QuoteServiceRouter.php - G.J. Watson
//    Desc: Route to appropriate response
// Version: 1.13
//

    // first load up the common project code
    set_include_path("../lib");
    require_once("Common.php");
    require_once("Database.php");
    require_once("JsonBuilder.php");
    require_once("ServiceException.php");
    require_once("Validate.php");
    require_once("UserAccess.php");

    // objects
    require_once("objects/Author.php");
    require_once("objects/Alias.php");
    require_once("objects/Quote.php");

    // common SQL statements
    require_once("sql/AuthorsQuotesSQL.php");

    // support functions
    require_once("support/UpdateRandomQuoteTimesUsed.php");

    // functions to return json
    require_once("responses/GetAllAuthors.php");
    require_once("responses/GetAuthorWithQuotes.php");
    require_once("responses/GetAllAuthorsWithQuotes.php");
    require_once("responses/GetRandomAuthorWithQuote.php");

    // search functions
    require_once("responses/SearchAllAuthors.php");
    require_once("responses/SearchAllAuthorsWithQuotes.php");

    // get 'new' quotes
    require_once("responses/GetNewAuthorsWithQuotes.php");

    // connection details for database
    require_once("connect/Quotes.php");

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
    // check it's a request we can deal with and then process appropriately
    //
    function routeRequest($check, $db, $access, $generated, $arr) {
        $version = "v1.13";
        switch ($arr["request"]) {
            case "authors":
                $jsonObj = new JSONBuilder($version, "GetAllAuthors", $generated, "authors", getAllAuthors($db));
                break;
            case "quotes":
                $jsonObj = new JSONBuilder($version, "GetAllAuthorsWithQuotes", $generated, "authors", getAllAuthorsWithQuotes($db));
                break;
            case "author":
                if (! $check->checkVariableExistsInArray("id", $arr) || ! $check->isValidNumeric($arr["id"])) {
                    throw new ServiceException(ILLEGALAUTHORID["message"], ILLEGALAUTHORID["code"]);
                }
                $jsonObj = new JSONBuilder($version, "GetAuthorWithQuotes", $generated, "author", getAuthorWithQuotes($db, $arr["id"]));
                break;
            case "random":
                $jsonObj = new JSONBuilder($version, "GetRandomAuthorWithQuote", $generated, "author", getRandomAuthorWithQuote($db, $access));
                break;
            case "srchauthors":
                if (! $check->checkVariableExistsInArray("search", $arr)) {
                    throw new ServiceException(NOSEARCHVARSFOUND["message"], NOSEARCHVARSFOUND["code"]);
                }
                $jsonObj = new JSONBuilder($version, "SearchAllAuthors", $generated, "authors", searchAllAuthors($db, $arr["search"]));
                break;
            case "srchquotes":
                $jsonObj = new JSONBuilder($version, "SearchAllAuthorsWithQuotes", $generated, "authors", searchAllAuthorsWithQuotes($db, $arr["search"]));
                break;
            case "newquotes":
                if (! $check->isValidDateTime($arr["startdate"])) {
                    throw new ServiceException(ILLEGALDATE["message"], ILLEGALDATE["code"]);
                }
                $jsonObj = new JSONBuilder($version, "GetNewAuthorsWithQuotes", $generated, "authors", getNewAuthorsWithQuotes($db, $arr["startdate"]));
                break;
            default:
                throw new ServiceException(HTTPROUTINGERROR["message"], HTTPROUTINGERROR["code"]);
        }
        return $jsonObj->getJson();
    }

    //
    // 1. do we have a valid token
    // 2. route the request
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
        $access = new UserAccess($token);
        $access->checkAccessAllowed($db);
        // 2 - routing
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                if (! $check->checkVariableExistsInArray("request", $_GET)) {
                    throw new ServiceException(MALFORMEDREQUEST["message"], MALFORMEDREQUEST["code"]);
                }
                $response = routeRequest($check, $db, $access, $common->getGeneratedDateTime(), $_GET);
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
