<?php
/**
 * Created by Helpixon
 * User: Helpix
 * Date: 05.10.2017
 * Time: 21:19
 */

require_once 'Parser.php';
require_once 'WordSource.php';
require_once 'MongoProvider.php';

$parser = new Parser(new WordSource());
echo 'Started at: ' . date('Y-m-d h:i:s') . PHP_EOL;
if (isset($argv[1]) && substr_count($argv[1], 'search=')) {
    $search = explode('=', $argv[1]);
    if (isset($search[1]) && $search[1]) {
        $mongoProvider = MongoProvider::getInstance('synonyms');
        echo $mongoProvider->findById($search[1]) . PHP_EOL;
    }
} else {
    //$parser->searchDataAndStoreByAllOptions();
    $parser->searchDataAndStoreBySynonymsOnly();
}

echo 'Ended at: ' . date('Y-m-d h:i:s') . PHP_EOL;
die;
