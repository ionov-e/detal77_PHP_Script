<?php
chdir(dirname(__FILE__));
include_once 'cat_ids_consts.php';
const LOGIN = 'aroomer';
const PASSWORD = 'kladblad';
const USER_NAME = 'Резниченко Анатолий Андреевич';
const USER_MAIL = 'rukinogi111@mail.ru';
const USER_PHONE = '89892899954';
const USER_ADDRESS = 'Бронирование';
// max allowed cart products amount. If the count of new IDs is more than this - we will not add to cart at all. Because probably something went wrong
const MAX_NEW_PRODUCTS = 60;
const CAT_IDS_USED_NAME = CAT_IDS_MERC_1ST_CHOICE;
include_once 'script.php';