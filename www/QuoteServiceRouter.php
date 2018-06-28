<?php
//
//  Module: QuoteServiceRouter.php - G.J. Watson
//    Desc: Route to appropriate response
// Version: 1.02
//

    // first load up the common project code
    set_include_path("../lib");
    require_once("Common.php");
    require_once("Database.php");
    require_once("JsonBuilder.php");
    require_once("ServiceException.php");
    require_once("UserAccess.php");

    // objects
    require_once("objects/Author.php");
    require_once("objects/Quote.php");

    // functions to return json
    require_once("responses/GetAllAuthors.php");
    require_once("responses/GetAuthorWithQuotes.php");
    require_once("responses/GetAllAuthorsWithQuotes.php");
    require_once("responses/GetRandomAuthorWithQuote.php");

    //
    // check array item supplied as expected
    //
    function validateURLVariableExists($key, $message, $code, $array) {
        if (! array_key_exists($key, $array)) {
            throw new ServiceException($message, $code);
        }
        if (empty($array[$key])) {
            throw new ServiceException($message, $code);
        }
        return;
    }

    function validateNumericURLVariable($key, $message, $code, $array) {
        validateURLVariableExists($key, $message, $code, $array);
        if (!is_numeric($array[$key])) {
            throw new ServiceException($message, $code);
        }
        return;
    }

    //
    // check it's a request we can deal with
    //
    function routeRequest($db, $access, $generated, $arr) {
        $version = "v1.00";
        switch ($arr["request"]) {
            case "authors":
                $jsonObj = new JSONBuilder($version, "GetAllAuthors", $generated, "authors", getAllAuthors($db));
                break;
            case "quotes":
                $jsonObj = new JSONBuilder($version, "GetAllAuthorsWithQuotes", $generated, "authors", getAllAuthorsWithQuotes($db));
                break;
            case "author":
                validateNumericURLVariable("id", ILLEGALAUTHORID["message"], ILLEGALAUTHORID["code"], $arr);
                $jsonObj = new JSONBuilder($version, "GetAuthorWithQuotes", $generated, "author", getAuthorWithQuotes($db, $arr["id"]));
                break;
            case "random":
                $jsonObj = new JSONBuilder($version, "GetRandomAuthorWithQuote", $generated, "author", getRandomAuthorWithQuote($db, $access));
                break;
            default:
                throw new ServiceException(HTTPROUTINGERROR["message"], HTTPROUTINGERROR["code"]);
        }
        return $jsonObj->getJson();
    }

    //
    // 1. do we have a valid token, and check it hasn't been abused
    // 2. route the request
    // 3. log the access
    //

    $database = "";
    $username = "";
    $password = "";
    $hostname = "";
    $db       = new Database($database, $username, $password, $hostname);
    $htmlCode = 200;
    $htmlMess = "200 OK";
    $response = "";
    try {
        $db->connect();
        // 1 - token check
        validateURLVariableExists("token", ACCESSTOKENMISSING["message"], ACCESSTOKENMISSING["code"], $_GET);
        $access = new UserAccess($_GET["token"]);
        $access->checkAccessAllowed($db);
        $common = new Common();
        // 2 - routing
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                validateURLVariableExists("request", HTTPROUTINGERROR["message"], HTTPROUTINGERROR["code"], $_GET);
                $response = routeRequest($db, $access, $common->getGeneratedDateTime(), $_GET);
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
