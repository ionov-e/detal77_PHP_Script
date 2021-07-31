<?php
chdir(dirname(__FILE__));
include_once 'cat_ids_consts.php';
const PHPSESSID = '1es3gd2xza2f3f4f6g7h2jojgd';     //Random id works.. but see no reason to do so. For now
const LOGIN = 'service101';
const PASSWORD = 'subver22';
const USER_NAME = 'Шварц Михаил Борисович';
const USER_MAIL = 'repairbmw2@gmail.com';
const USER_PHONE = '89531009334';
const USER_ADDRESS = 'Бронирование';
const MAX_NEW_PRODUCTS = 60; // max allowed cart products amount. If the count of new IDs is more than this - we will not add to cart at all. Because probably something went wrong
const DAYS_FROM_LAST_CHECK_FOR_NEW_PRODUCTS = 3;
const CAT_IDS_USED_NAME = CAT_IDS_BMW_HALF_1;
include_once 'script.php';