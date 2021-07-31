<?php
chdir(dirname(__FILE__));
include_once 'cat_ids_consts.php';
// This is a test acc, but please, don't overuse this
const PHPSESSID = 'rnlarqb3bji09g322fam8ydr3k';     //Random id works.. but see no reason to do so. For now
const LOGIN = 'aroomer';
const PASSWORD = 'kladblad';
const USER_NAME = 'Резниченко Анатолий Андреевич';
const USER_MAIL = 'rukinogi111@mail.ru';
const USER_PHONE = '89892899954';
const USER_ADDRESS = 'Бронирование';
const MAX_NEW_PRODUCTS = 60; // max allowed cart products amount. If the count of new IDs is more than this - we will not add to cart at all. Because probably something went wrong
const DAYS_FROM_LAST_CHECK_FOR_NEW_PRODUCTS = 3;
const CAT_IDS_USED_NAME = CAT_IDS_MERC_1ST_CHOICE;
include_once 'script.php';