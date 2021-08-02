<?php
chdir(dirname(__FILE__));
include_once 'cat_ids_consts.php';
const LOGIN = 'southgarage61';
const PASSWORD = 'Boost2atm';
const USER_NAME = 'Резниченко Андрей Анатольевич';
const USER_MAIL = 'x651xe123@gmail.com';
const USER_PHONE = '89182606667';
const USER_ADDRESS = 'Бронирую!';
// max allowed cart products amount. If the count of new IDs is more than this - we will not add to cart at all. Because probably something went wrong
const MAX_NEW_PRODUCTS = 60;
const CAT_IDS_USED_NAME = CAT_IDS_BMW_HALF_2;
include_once 'script.php';