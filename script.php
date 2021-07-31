<?php

//require_once 'classes/UserData.php';
//
//$userData = new UserData;

const URL = 'https://detal77.ru/';
const COOKIEPHPSESSID = 'Cookie: PHPSESSID=' . PHPSESSID;
const LOGININFOSUFFIX = "&safe_users=1&user_enter=1";
const LOGININFO = "login=" . LOGIN . "&password=" . PASSWORD . LOGININFOSUFFIX;
const CONTYPEAPP = "Content-Type: application/x-www-form-urlencoded";
const CONTYPEUTF = "Content-Type: text/plain;charset=UTF-8";
const LOGFOLDERROOT = "log";
const NO_PREVIOUS_ID_LIST_MESSAGE = 'No previous ID list';
const ERROR_WITH_CAT_IDS_CSV = 'No CSV File';
const CAT_IDS_USED_ADDRESS = CAT_IDS_FOLDER . DIRECTORY_SEPARATOR . CAT_IDS_USED_NAME . '.csv';

$logFolderMonth = LOGFOLDERROOT . DIRECTORY_SEPARATOR . date('Y') . DIRECTORY_SEPARATOR . date('m');

/*
//const LOGFOLDREMONTH = LOGFOLDERROOT . generateMonthFolder ();
function generateMonthFolder ()
{
    return DIRECTORY_SEPARATOR . date('Y-m');
}*/

//------------
//-------  Functions
//------------

//-------  Log Functions

function logDef($logMsg) // This is for default logging {$date - message \n}
{
    logBeg($logMsg . PHP_EOL);
}

function logBeg($logMsg)  // This is for logging without break line (to be continued) {$date - message part1 ...}
{
    logWrite(date('Y-m-d H:i:s (T)') . ' - ' . $logMsg);
}

function logEnd($logMsg) // This is for continuing previous logging function {... -> message part2 \n}
{
    logWrite(" -> " . $logMsg . PHP_EOL);
}

function logError($logMsg) // This is for errors (maybe for smth else to be implemented)
{
    logBeg('!!! ERROR !!! : ' . $logMsg . PHP_EOL);
}

function logWrite($logMsg)
{
    global $logFolderMonth;
    $logFileAddress = $logFolderMonth . DIRECTORY_SEPARATOR . date('Y-m-d') . '.log';
    checkLogFolder();
    file_put_contents($logFileAddress, $logMsg, FILE_APPEND);
}

//-------  File Functions

function checkLogFolder()
{ // creates folders if there are none
    global $logFolderMonth;
    if (!is_dir(LOGFOLDERROOT)) {
        mkdir(LOGFOLDERROOT, 0777, true);
    }
    if (!is_dir($logFolderMonth)) {
        mkdir($logFolderMonth, 0777, true);
    }
}

function checkCsvCatFile(): bool
{ // creates folders if there are none
    if (!is_dir(CAT_IDS_FOLDER)) {
        logError('There is no folder with category ids named: ' . CAT_IDS_FOLDER);
        return false;
    }
    if (!is_file(CAT_IDS_USED_ADDRESS)) {
        logError('There is no file with category ids in: ' . CAT_IDS_USED_NAME);
        return false;
    }
    logDef('Found and used csv with categories ids: ' . CAT_IDS_USED_NAME);
    return true;
}

function writeToIdList($idList)
{
    logBeg('Writing new IDs (from crawling) to file:');
    $idListFileAddress = generateIdListFileAddress();
    file_put_contents($idListFileAddress, json_encode($idList));
    logEnd('Done - file product count now is ' . count($idList));
}

function generateIdListFileAddress(): string
{
    return generateFileAddress(date('Y-m-d') . '_' . CAT_IDS_USED_NAME . '.txt');
}

function generatePreviousIdListFileAddress($daysAgo): string //todo combine with generateIdListFileAddress
{
    global $logFolderMonth;
    checkLogFolder();
    $time = new DateTime ();
    $selectedDay = $time->modify("-{$daysAgo} days")->format("Y-m-d");
    return $logFolderMonth . DIRECTORY_SEPARATOR . $selectedDay . CAT_IDS_USED_NAME . '.txt';
}

/*
function generatePreviousIdListFileAddress ($daysAgo): string
{
    return generatePreviousIdListFileAddress ().'.log'
}*/

function generateFileAddress($name): string
{
    global $logFolderMonth;
    checkLogFolder();
    return $logFolderMonth . DIRECTORY_SEPARATOR . $name;
}

//-------  curl function

function universalCurl($urlPage, $isPost, $headerArray, $postfieldString = '')//todo $isPost to default
{
    $options = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => false,  //don't return headers
        CURLOPT_AUTOREFERER => true,  //set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 180,  //timeout on connect
        CURLOPT_TIMEOUT => 180,  //timeout on response
        CURLOPT_MAXREDIRS => 10, CURLOPT_HTTPHEADER => $headerArray,//        CURLOPT_USERAGENT => "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:90.0) Gecko/20100101 Firefox/90.0", //Not needed at all
    ];
    if ($isPost) {  //somehow adding to an array 2 elements wasn't an option, since keys for some reason were not as numbers, not like 'CURLOPT_HEADER'
        $options [CURLOPT_POST] = 1;
        $options [CURLOPT_CUSTOMREQUEST] = "POST";
        $options [CURLOPT_POSTFIELDS] = $postfieldString;
    }

    $ch = curl_init(URL . $urlPage);
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


function loginCurl(): string
{
    logDef("Attempting to log in. Account: " . LOGIN);
    return universalCurl("order/", true, [CONTYPEAPP, COOKIEPHPSESSID], LOGININFO);
}

/**
 * checks if we are logined
 * @description checks through curl
 */

function checkLoginCurl(): void
{
    $result = universalCurl("users/", false, [COOKIEPHPSESSID]);

    if (str_contains($result, 'rosette.gif'))//Made same function for php version < 8
    {
        logBeg("Logged in some account");
        if (str_contains($result, LOGIN)) {
            logEnd("(Good) Found login name on user page (" . LOGIN . ")");
        } else {
            logError("Did not found word" . LOGIN . "on user page");
        }
    } else {
        logError("Login failure");
    }
}

function clearCartCurl(): void   // clear cart
{
    universalCurl("order/?cart=clean", false, [COOKIEPHPSESSID]);
}

function addToCartCurl($prodID): string // returns number of products in cart (as string)
{
    $postfields = "xid=" . $prodID . "&num=1&xxid=0&addname=&same=0&test=303";
    $result = universalCurl("phpshop/ajax/cartload.php", true, [CONTYPEUTF, COOKIEPHPSESSID], $postfields);
    $result = substr($result, strpos($result, "num': '") + 7);
    $result = strstr($result, "','sum", true);
    return $result;
}

function checkCartCount(): string // returns number of products in cart (as string)
{
    $result = universalCurl("phpshop/ajax/cartload.php", false, [CONTYPEUTF, COOKIEPHPSESSID]);
    $result = substr($result, strpos($result, "num': '") + 7);
    $result = strstr($result, "','sum", true);
    return $result;
}

function getCartPageCurl() //returns cart page html
{
    return universalCurl("order/", false, [COOKIEPHPSESSID]);
}

function checkoutCurl() // returns number of products in cart (as string)
{
    $postfields = 'ouid=' . getCartId() . '&dostavka_metod=1&mail=' . encodeToUrl(USER_MAIL) . '&name_person=' . encodeToUrl(USER_NAME) . '&org_name=&org_inn=&org_kpp=&tel_code=&tel_name=' . encodeToUrl(USER_PHONE) . '&dos_ot=&dos_do=&adr_name=' . encodeToUrl(USER_ADDRESS) . '&order_metod=1&send_to_order=ok&d=1&nav=done';
    $result = universalCurl("done/", true, [CONTYPEAPP, COOKIEPHPSESSID], $postfields);
    $result = iconv('CP1251', 'UTF-8', $result);
    logDef('Checkout seems to be complete');
    //Little final search:
    if (str_contains($result, 'Спасибо за Ваш заказ'))//Made same function for php version < 8
    {
        logDef("'Thank you for your order' is found");
    } else {
        logError("'Thank you for your order' is not found");
    }
    return $result;
}

//-------

// based on original work from the PHP Laravel framework // Copied from internet for php version < 8
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

//-------   Data processing functions

function encodeToUrl($string): string
{
    return urlencode(iconv("UTF-8", "windows-1251", $string));
}

function getProdIdsFromCat($idCategory)
{ //returns only array of prod IDs from Category page  //todo implement php_query

    $url = "shop/CID_" . $idCategory . "_ALL.html";
    $catData = universalCurl($url, false, [COOKIEPHPSESSID]);
    //    $catData = iconv('CP1251', 'UTF-8', $catData); // no reason to encode since we're getting IDs
    $catData = strstr($catData, '<table cellpadding="0" cellspacing="0" border="0">');
    $catData = strstr($catData, '<div class="page_nava', true);
    preg_match_all('~"center"><A href="\/shop\/UID_(\d{4,}).html~', $catData, $prodIdsFromCat);//not fully understand my regex expression
    return $prodIdsFromCat[1];
}

function getIdsOfAllCurProducts(): array
{
    $catIdsAndNames = array_map('str_getcsv', file(CAT_IDS_USED_ADDRESS));  //returns array with all cat ids
    $idsOfAllCurrentProducts = [];
    logDef("There are " . count($catIdsAndNames) . " categories in file. Starting crawling product ids from categories:");
    foreach ($catIdsAndNames as $idAndName) {
        $idsFromCurCat = getProdIdsFromCat($idAndName[0]);
        $idsOfAllCurrentProducts = array_merge($idsOfAllCurrentProducts, $idsFromCurCat);
        logEnd("{$idAndName[1]} has " . count($idsFromCurCat));
    }
    logDef("Categories product count crawling ends with: " . count($idsOfAllCurrentProducts) . " products");
    return $idsOfAllCurrentProducts;
}

function getCartId()    //todo implement php_querry
{
    $htmlCartPageUnedited = getCartPageCurl(); // I wanna keep unedited in case of failing of getting the id
    //    $htmlCartPageUnedited = iconv('CP1251', 'UTF-8', $htmlCartPageUnedited); // no reason to encode since we're getting cart ID
    $htmlCartPage = strstr($htmlCartPageUnedited, '<input type="text" name=ouid');
    $htmlCartPage = strstr($htmlCartPage, '"  readonly="1">', true);
    $htmlCartPage = substr($htmlCartPage, strpos($htmlCartPage, 'value="') + 7);
    if (strlen($htmlCartPage) > 20 || strlen($htmlCartPage) < 6) {
        logError('Func (getCartId): Failed to get ID from cart page. Here is the unedited page-html:' . $htmlCartPageUnedited);
        return '24003-' . rand(100, 989); // returns kinda random id in case of failing this function, but it would still work with any id, even no id at all
    }
    logDef('Func: Cart ID search seams to be successful. We have got ID: ' . $htmlCartPage);
    return $htmlCartPage; //returns id inside of: '...;" value="24068-110" readonly="1">'
}

//returns new product ids in array (difference between scraped prod ids vs previous scraping)
function findNewProducts(): array
{
    if (!checkCsvCatFile()) {
        return [0 => ERROR_WITH_CAT_IDS_CSV]; // returns this value to stop script
    }//TODO !!!
    $currentProductIds = getIdsOfAllCurProducts();
    $fileProdIdAddress = generateIdListFileAddress();
    if (!file_exists($fileProdIdAddress)) {
        for ($i = 1; $i <= DAYS_FROM_LAST_CHECK_FOR_NEW_PRODUCTS; $i++) {
            $fileProdIdAddress = generatePreviousIdListFileAddress($i);
            if (file_exists($fileProdIdAddress)) {
                logDef("Today's product id file is empty, but we've used it from $i days ago");
                break;
            }
            if ($i === DAYS_FROM_LAST_CHECK_FOR_NEW_PRODUCTS) {
                logError("Previous product id-list file is not found (checked " . DAYS_FROM_LAST_CHECK_FOR_NEW_PRODUCTS . " days). Last check address ({$fileProdIdAddress}) Gonna end this session after writing new IDs");
                writeToIdList($currentProductIds); //writes (or overwrites) ids of all current products in file
                return [0 => NO_PREVIOUS_ID_LIST_MESSAGE]; // returns this value to stop script
            }
        }
    }

    $previousProductIds = json_decode(file_get_contents($fileProdIdAddress), true);

    logDef("{$fileProdIdAddress} : Previous product count is " . count($previousProductIds));

    writeToIdList($currentProductIds); //writes (or overwrites) ids of all current products in file
    return array_diff($currentProductIds, $previousProductIds);
}

function addToCartNewProducts()
{
    $newProducts = findNewProducts(); //returns array with new prods (difference between cur vs last ids)
    $countOfNewProducts = count($newProducts);
    if ($countOfNewProducts == 1) {
        if ($newProducts[0] == NO_PREVIOUS_ID_LIST_MESSAGE) { // If there are no previous ID lists (last few days) to compare new ID list view
            return false;
        } elseif ($newProducts[0] == ERROR_WITH_CAT_IDS_CSV) { // If there is no previous csv with cat ID list (or folder csv)
            return false;
        }
    }
    logDef("Count of new products found: $countOfNewProducts");
    if ($countOfNewProducts == 0) {
        logDef('No new products found. Gonna end this session now');
        return false;
    } else if ($countOfNewProducts > MAX_NEW_PRODUCTS) {
        logError("There were too much of new products. Max limit is " . MAX_NEW_PRODUCTS . ". Gonna end this session now");
        return false;
    } else {
        logDef('-> -> -> -> -> -> Starting adding to cart <- <- <- <- <- <-');
        $cartCountOld = 0;
        foreach ($newProducts as $newProduct) {
            logEnd("Adding to cart product id: $newProduct");
            $cartCountNew = addToCartCurl($newProduct);
            if ($cartCountNew == $cartCountOld + 1) {
                logEnd("Success. Cart count now is $cartCountNew");
            } else {
                logError(sprintf("Cart adding went wrong. %d was not added. Old cart amount was %d. New is %d", $newProduct, $cartCountOld, $cartCountNew));
            }
            $cartCountOld = $cartCountNew;
        }
    }
    return true;
}

function makeSureCartIsEmptyOrExit()
{
    if (checkCartCount() != 0) {
        logDef("Minor thing: for some reason cart is not empty after login. Attempting to clear cart");
        clearCartCurl();
        if (checkCartCount() != 0) {
            logError("Cart is not empty after login and even after trying to clear it out");
            return false;
        } else {
            logDef("Successfully cleared the cart");
        }
    } else {
        logDef("Cart is empty after login (Good)");
    }
    return true;
}

//------------
//-------  All we do is:
//------------
logDef('-------------NEW-----------------');


$isError = true;
$isError = false;//todo wtf with defined and included?
/*
echo LOGIN . "\n";
if (LOGIN) {
    echo 'Hello!!!!!!!!!';
}

if (!defined(LOGIN)) {
    logError("Script file was run without user-php. LOGIN is not defined " . LOGIN);
} elseif (!defined(PASSWORD)) {
    logError("PASSWORD is not defined");
} elseif (!defined(USER_NAME)) {
    logError("USER_NAME is not defined");
} elseif (!defined(USER_MAIL)) {
    logError("USER_MAIL is not defined");
} elseif (!defined(USER_PHONE)) {
    logError("USER_PHONE is not defined");
} elseif (!defined(USER_ADDRESS)) {
    logError("USER_ADDRESS is not defined");
} elseif (!defined(PHPSESSID)) {
    logError("PHPSESSID is not defined");
} elseif (!defined(MAX_NEW_PRODUCTS)) {
    logError("MAX_NEW_PRODUCTS is not defined");
} elseif (!defined(CAT_IDS_USED_NAME)) {
    logError("CAT_IDS_USED_NAME is not defined");
} else {
    $isError = false;
    logDef("Script is run using this account: " . LOGIN);
}*/

if ($isError) {
    logError("Script will no be run");
} else {
    $start = new DateTime();
    loginCurl();
    checkLoginCurl();
    if (makeSureCartIsEmptyOrExit()) {
        if (addToCartNewProducts()) {
                    checkoutCurl();
        }
    }
    logDef("Program Runtime: " . (new DateTime())->diff($start)->format("%h:%i:%s"));
    logDef('-------------END-----------------');
}
//$request = urlencode(iconv("UTF-8", "windows-1251", "i0n0ff@live.ru"));
//echo $request;
?>