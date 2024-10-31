<?php

use OfficeGest\ArraySearcher;
use OfficeGest\Controllers\Documents;
use OfficeGest\Controllers\OrderProduct;
use OfficeGest\Controllers\PendingOrders;
use OfficeGest\Controllers\Product;
use OfficeGest\Controllers\ProductsList;
use OfficeGest\Error;
use OfficeGest\Log;
use OfficeGest\Notice;
use OfficeGest\OfficeGestCurl;
use OfficeGest\OfficeGestDBModel;
use OfficeGest\OfficegestProduct;
use OfficeGest\Start;
use OfficeGest\Tools;

global $wpdb;
ini_set( 'memory_limit',-1);
ini_set( 'max_execution_time',-1);

Tools::debug(\OfficeGest\OfficeGestDBModel::clearEcoAutoParts($offset = 0, $limit = 1000));
