<?php
chdir(dirname(__FILE__));
include_once 'cat_ids_consts.php';
const LOGIN = 'service101';
const PASSWORD = 'subver22';
const USER_NAME = 'Шварц Михаил Борисович';
const USER_MAIL = 'repairbmw2@gmail.com';
const USER_PHONE = '89531009334';
const USER_ADDRESS = 'Бронирование';
// max allowed cart products amount. If the count of new IDs is more than this - we will not add to cart at all. Because probably something went wrong
const MAX_NEW_PRODUCTS = 60;
const CAT_IDS_USED_NAME = CAT_IDS_BMW_HALF_1;
include_once 'script.php';