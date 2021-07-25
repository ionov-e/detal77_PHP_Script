<?php

//------------
//-------  User Data (To edit)
//------------

/*
 * // This the acc for real usage
 *
const PHPSESSID = 'Cookie: PHPSESSID=rkicg4b2fofqnm01qqd5jp3d57';     //Random id works.. but see no reason to do so. For now
const LOGININFO = 'login=bmwm&password=bmwm77';
const CHECKOUTSUFFIX = '&dostavka_metod=1&mail=Bmw.mpack"%"40bk.ru&name_person="%"D5"%"E0"%"F0"%"E8"%"F2"%"EE"%"ED"%"EE"%"E2+"%"C0"%"ED"%"E4"%"F0"%"E5"%"E9+"%"C8"%"E2"%"E0"%"ED"%"EE"%"E2"%"E8"%"F7&org_name=&org_inn=&org_kpp=&tel_code="%"2B7910&tel_name=4287486&dos_ot=&dos_do=&adr_name="%"C7"%"E0"%"E1"%"E5"%"F0"%"F3+"%"E1"%"E5"%"E7+"%"EE"%"F2"%"EA"%"E0"%"E7"%"ED"%"EE+100"%"25&order_metod=1&send_to_order=ok&d=1&nav=done';
const MAXBUYS = 60; // max allowed cart products amount. If the count of new IDs is more than this - we will not add to cart at all. Because probably something went wrong*/



// This is a test acc, but please, don't overuse this

const PHPSESSID = 'sd1fmgsdf3hllafr5g8c73k511';     //Random id works.. but see no reason to do so. For now
const LOGIN = 'aroomer';
const PASSWORD = 'kladblad';
const CHECKOUTSUFFIX = '&dostavka_metod=1&mail=i0n0ff"%"40live.ru&name_person="%"D0"%"E5"%"E7"%"ED"%"E8"%"F7"%"E5"%"ED"%"EA"%"EE+"%"C0"%"ED"%"E0"%"F2"%"EE"%"EB"%"E8"%"E9+"%"C0"%"ED"%"E4"%"F0"%"E5"%"E5"%"E2"%"E8"%"F7&org_name=&org_inn=&org_kpp=&tel_code=&tel_name=89892899914&dos_ot=&dos_do=&adr_name="%"C1"%"F0"%"EE"%"ED"%"E8"%"F0"%"EE"%"E2"%"E0"%"ED"%"E8"%"E5&order_metod=1&send_to_order=ok&d=1&nav=done';
const MAXBUYS = 60; // max allowed cart products amount. If the count of new IDs is more than this - we will not add to cart at all. Because probably something went wrong
const DAYS_FROM_LAST_CHECK_FOR_NEW_PRODUCTS = 3;
//-------

const URL = 'https://detal77.ru/';
const COOKIEPHPSESSID = 'Cookie: PHPSESSID=' . PHPSESSID;
const LOGININFOSUFFIX= "&safe_users=1&user_enter=1";
const LOGININFO = "login=" . LOGIN . "&password=" . PASSWORD . LOGININFOSUFFIX;
const CONTYPEAPP = "Content-Type: application/x-www-form-urlencoded";
const CONTYPEUTF = "Content-Type: text/plain;charset=UTF-8";
const LOGFOLDERROOT = "log";
const FAKE_PROD_ID = 'z';

$logFolderMonth = LOGFOLDERROOT . "/" .date('Y-m');

/*
//const LOGFOLDREMONTH = LOGFOLDERROOT . generateMonthFolder ();
function generateMonthFolder ()
{
    return "/" .date('Y-m');
}*/

//------------
//-------  Functions
//------------
//-------  Log Functions

function logDef($logMsg) // This is for default logging {$date - message \n}
{
    logBeg($logMsg . "\n");
}

function logBeg($logMsg)  // This is for logging without break line (to be continued) {$date - message part1 ...}
{
    logWrite( date('Y-m-d H:i:s (T)').' - '.$logMsg );
}

function logEnd($logMsg) // This is for continuing previous logging function {... -> message part2 \n}
{
    logWrite(" -> " . $logMsg . "\n");
}

function logError($logMsg) // This is for errors (maybe for smth else to be implemented)
{
    logBeg('!!! ERROR !!! : '.$logMsg . "\n");
}

function logWrite($logMsg)
{
    global $logFolderMonth;
    $logFileAddress = $logFolderMonth . '/' . date('Y-m-d') . '.log';
    checkLogFolder ();
    file_put_contents($logFileAddress, $logMsg, FILE_APPEND);
}

//-------  File Functions

function checkLogFolder () { // creates folders if there are none
    global $logFolderMonth;
    if (!is_dir(LOGFOLDERROOT))
    {
        mkdir(LOGFOLDERROOT, 0777, true);
    }
    if (!is_dir($logFolderMonth))
    {
        mkdir($logFolderMonth, 0777, true);
    }
}

function writeToIdList($idList)
{
    logBeg('Writing IDs we have got from crawling:');
    $idListFileAddress = generateIdListFileAddress ();
//    file_put_contents($idListFileAddress, implode("\n" , $idList));
    file_put_contents( $idListFileAddress, json_encode ($idList) );
    logEnd('Done - product count is ' . count($idList));
}

function generateIdListFileAddress (): string
{
    return generateFileAddress (date('Y-m-d') . '_allProdIds.txt');
}

function generatePreviousIdListFileAddress ($daysAgo): string //todo combine with generateIdListFileAddress
{
    global $logFolderMonth;
    checkLogFolder ();
    $time = new DateTime ();
    $selectedDay = $time->modify("-{$daysAgo} days")->format("Y-m-d");
    return $logFolderMonth . DIRECTORY_SEPARATOR . $selectedDay . '_allProdIds.txt';
}
/*
function generatePreviousIdListFileAddress ($daysAgo): string
{
    return generatePreviousIdListFileAddress ().'.log'
}*/

function generateFileAddress ($name)
{
    global $logFolderMonth;
    checkLogFolder();
    return $logFolderMonth . DIRECTORY_SEPARATOR. $name;
}


//-------  curl function





function universalCurl($urlPage, $postBool, $headerArray, $postfieldString = '')
{
    if ($postBool)  //somehow adding to an array 2 elements wasn't an option, since keys for some reason were not as numbers, not like 'CURLOPT_HEADER'
    {
        $options = [CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,  //don't return headers
            CURLOPT_AUTOREFERER => true,  //set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 180,  //timeout on connect
            CURLOPT_TIMEOUT => 180,  //timeout on response
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTPHEADER => $headerArray,

            CURLOPT_POST => 1,  //I am sending post data
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $postfieldString,
        ];
    } else
    {
        $options = [CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,  //don't return headers
            CURLOPT_AUTOREFERER => true,  //set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 180,  //timeout on connect
            CURLOPT_TIMEOUT => 180,  //timeout on response
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTPHEADER => $headerArray,
        ];
    }
    $ch = curl_init(URL . $urlPage);
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


/*
function universalCurl($urlPage, $isPost, $headerArray, $postfieldString = '')//todo $isPost to default
{
    $options = [CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,  //don't return headers
        CURLOPT_AUTOREFERER => true,  //set referrer on redirect
        CURLOPT_CONNECTTIMEOUT => 180,  //timeout on connect
        CURLOPT_TIMEOUT => 180,  //timeout on response
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_HTTPHEADER => $headerArray,
    ];
    if ($isPost) {  //somehow adding to an array 2 elements wasn't an option, since keys for some reason were not as numbers, not like 'CURLOPT_HEADER'
        $options [CURLOPT_POST] = 1;
        $options [CURLOPT_CUSTOMREQUEST] = "POST";
        $options [CURLOPT_POSTFIELDS] = $postfieldString;
    }

    $ch = curl_init(URL . $urlPage);
    curl_setopt_array($ch, $options);
    $result = curl_exec($ch);
//    $phpRes = curl_getinfo();
    curl_close($ch);
    return $result;
}*/


function loginCurl() : string
{
    return universalCurl("order/", true, array(CONTYPEAPP, COOKIEPHPSESSID), LOGININFO);
}

/**
 * checks if we are logined
 * @description checks through curl
 */
function checkLoginCurl () : void
{
    $result = universalCurl("users/", false, array(COOKIEPHPSESSID));

    if (str_contains($result, 'rosette.gif'))//Made same function for php version < 8
    {
        logDef("Login was successful. Didn't check if it is the right account, but that seems to be an overkill");
    } else {
        logError("Login failure");
    }
}

function clearCartCurl()  : void   // clear cart
{
    universalCurl("order/?cart=clean",false,array(COOKIEPHPSESSID));
}

function addToCartCurl($prodID) :string // returns number of products in cart (as string)
{
    $postfields = "xid=".$prodID."&num=1&xxid=0&addname=&same=0&test=303";
    $result = universalCurl("phpshop/ajax/cartload.php", true, array(CONTYPEUTF, COOKIEPHPSESSID), $postfields);
    $result = substr($result, strpos($result, "num': '") + 7);
    $result = strstr($result, "','sum", true);
    return $result;
}

function getCartPageCurl() //returns cart page html
{
    return universalCurl("order/", false, array(COOKIEPHPSESSID));
}

function checkoutCurl() // returns number of products in cart (as string)
{
    $postfields = 'ouid=' . getCartId() . CHECKOUTSUFFIX;
    $result = universalCurl("done/", true, array(CONTYPEAPP, COOKIEPHPSESSID), $postfields);
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
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}

//-------   Data processing functions

function getProdIdsFromCat ($idCategory) { //returns only array of prod IDs from Category page

    $url = "shop/CID_".$idCategory."_ALL.html";
    $catData = universalCurl($url,false,array(COOKIEPHPSESSID));
//    $catData = iconv('CP1251', 'UTF-8', $catData); // no reason to encode since we're getting IDs
    $catData = strstr($catData, '<table cellpadding="0" cellspacing="0" border="0">');
    $catData = strstr($catData, '<div class="page_nava', true);
    preg_match_all('~"center"><A href="\/shop\/UID_(\d{4,}).html~', $catData, $prodIdsFromCat);//not fully understand my regex expression
    return $prodIdsFromCat[1];
}

function getIdsOfAllCurProducts (): array
{
    $catIdsAndNames = array_map('str_getcsv', file('cat_ids.csv'));  //returns array with all cat ids
    $idsOfAllCurrentProducts = [];
    logBeg("Categories product count crawling start:\n");
    foreach ($catIdsAndNames as $idAndName) {
        $idsFromCurCat = getProdIdsFromCat($idAndName[0]);
        $idsOfAllCurrentProducts = array_merge($idsOfAllCurrentProducts, $idsFromCurCat);
        logEnd("{$idAndName[1]} has ". count($idsFromCurCat));
    }
    logDef("Categories product count crawling ends with: ". count($idsOfAllCurrentProducts) . " products");
    return $idsOfAllCurrentProducts;
}

function getCartId()
{
    $htmlCartPageUnedited = getCartPageCurl(); // I wanna keep unedited in case of failing of getting the id
//    $htmlCartPageUnedited = iconv('CP1251', 'UTF-8', $htmlCartPageUnedited); // no reason to encode since we're getting cart ID
    $htmlCartPage = strstr($htmlCartPageUnedited, '<input type="text" name=ouid');
    $htmlCartPage = strstr($htmlCartPage, '"  readonly="1">', true);
    $htmlCartPage = substr($htmlCartPage, strpos($htmlCartPage, 'value="') + 7);
    if (strlen($htmlCartPage) > 20 || strlen($htmlCartPage) < 6) {
        logError('Func (getCartId): Failed to get ID from cart page. Here is the unedited page-html:' . $htmlCartPageUnedited);
        return '24003-' . rand (100, 989); // returns kinda random id in case of failing this function, but it would still work with any id, even no id at all
    }
    logDef ('Func: Cart ID search seams to be successful. We have got ID: ' . $htmlCartPage);
    return $htmlCartPage; //returns id inside of: '...;" value="24068-110" readonly="1">'
}

//returns new product ids in array (difference between scraped prod ids vs previous scraping)
function findNewProducts () : array
{
    $fileProdIdAddress = generateIdListFileAddress ();
    if (!file_exists($fileProdIdAddress) )
    {
        for ($i = 1; $i <= DAYS_FROM_LAST_CHECK_FOR_NEW_PRODUCTS; $i++)
        {
            $fileProdIdAddress = generatePreviousIdListFileAddress($i);
            if (file_exists($fileProdIdAddress) )
            {
                logDef("Today's product id file is empty, but we've used it from $i days ago");
                break;
            }
            if ($i === DAYS_FROM_LAST_CHECK_FOR_NEW_PRODUCTS)
            {
                logError("Previous product id file is not found (checked last few days)");
                return array();
            }
        }
    }
    $previousProductIds = json_decode(file_get_contents($fileProdIdAddress), true);
    logDef('Previous product count is ' . count ($previousProductIds) );
    $currentProductIds = getIdsOfAllCurProducts();
    writeToIdList($currentProductIds); //writes (or overwrites) ids of all current products in file
    return array_diff($currentProductIds, $previousProductIds);
}

function addToCartNewProducts ()
{
    $newProducts = findNewProducts(); //returns array with new prods (difference between cur vs last ids)
    $countOfNewProducts = count($newProducts);
    logBeg("Count of new products found: $countOfNewProducts");
    if ($countOfNewProducts == 0 )
    {
        logEnd('No new products found. Gonna end this session now');
        return false;
    } else if ($countOfNewProducts > MAXBUYS) {
        logError("There were too much of new products. Max limit is " . MAXBUYS . ". Gonna end this session now");
        return false;
    } else {
        logEnd('Starting adding to cart');
        $cartCountOld = 0;
        foreach ($newProducts as $newProduct)
        {
            logEnd("Adding to cart product id: $newProduct");
            $cartCountNew = addToCartCurl($newProduct);
            if ($cartCountNew == $cartCountOld + 1)
            {
                logEnd("Success. Cart count now is $cartCountNew");
            } else
            {
                logError(sprintf("Cart adding went wrong. %d was not added. Old cart amount was %d. New is %d", $newProduct, $cartCountOld, $cartCountNew) );
            }
            $cartCountOld = $cartCountNew;
        }
    }
    return true;
}

function makeSureCartIsEmptyOrExit () {
    if (addToCartCurl(FAKE_PROD_ID) != 0 ) {
        logDef("Minor thing: for some reason cart is not empty after login. Attempting to clear cart");
        clearCartCurl();
        if (addToCartCurl(FAKE_PROD_ID) != 0) {
            logError("Cart is not empty after login and even after trying to clear it out");
            return false;
        }
    } else {
        logDef("Cart is empty after login (Good)");
    }
    return true;
}

//------------
//-------  All we do is:
//------------

$start = new DateTime();
logDef('-------------NEW-----------------');
loginCurl();
checkLoginCurl();
//sleep(5);
if (makeSureCartIsEmptyOrExit()) {
    if (addToCartNewProducts()) {
        echo checkoutCurl();
    }
}
logDef ("Program Runtime:" . (new DateTime())->diff($start)->format("%h:%i:%s") );
logDef('-------------END-----------------');
?>