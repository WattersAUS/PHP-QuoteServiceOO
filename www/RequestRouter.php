<?php
//
//  Module: RequestRouter.php - G.J. Watson
//    Desc: Route to appropriate response
// Version: 1.00
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
    // check it's a request we can deal with
    function routeRequest($db, $access, $common, $request) {
        switch ($request) {
            case "authors":
                $arr["service"] = "GetAllAuthors";
                $arr["authors"] = getAllAuthors($db, $common);
                break;
            case "quotes":
                $arr["service"] = "GetAllAuthorsWithQuotes";
                $arr["authors"] = getAllAuthorsWithQuotes($db, $common);
                break;
            case "author":
                if (! in_array("author", $_GET)) {
                    throw new ServiceException(ILLEGALAUTHORID["message"], ILLEGALAUTHORID["code"]);
                }
                if (empty($_GET["author"]) || !is_numeric($_GET["author"])) {
                    throw new ServiceException(ILLEGALAUTHORID["message"], ILLEGALAUTHORID["code"]);
                }
                $arr["service"] = "GetAuthorWithQuotes";
                $arr["author"]  = getAuthorWithQuotes($db, $common, $_GET["author"]);
                break;
            case "quote":
                $arr["service"] = "GetRandomAuthorWithQuote";
                $arr["author"]  = getRandomAuthorWithQuote($db, $common);
                break;
            default:
                throw new ServiceException(HTTPROUTINGERROR["message"], HTTPROUTINGERROR["code"]);
        }
        $arr["generated"] = $common->getGeneratedDateTime();
        return $arr;
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
    $response = "";
    try {
        $db->connect();
        // 1 - token check
        if (! $_GET["token"] || empty($_GET["token"])) {
            throw new ServiceException(ACCESSTOKENMISSING["message"], ACCESSTOKENMISSING["code"]);
        }
        $access = new UserAccess($_GET["token"]);
        $access->checkAccessAllowed($db);
        $common = new Common();
        // 2 - routing
        switch ($_SERVER['REQUEST_METHOD']) {
            case "GET":
                if (!isset($_GET["request"])) {
                    throw new ServiceException(HTTPROUTINGERROR["message"], HTTPROUTINGERROR["code"]);
                }
                $response = json_encode(routeRequest($db, $access, $common, $_GET["request"]), JSON_NUMERIC_CHECK);
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
        // here we control the rethrown errors...interrogate the internal error to map to a http code and setup up a response
        $response = $e->jsonString();
    } catch (Exception $e) {
        // stuff we haven't caught and recycled 500 error
    } finally {
        // send the result of the req back
        header("Content-type: application/json;charset=utf-8");
        echo $response;
    }
?>
