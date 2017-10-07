<?php

require_once 'SourceInterface.php';

/**
 * Created by Helpixon.
 * User: Helpix
 * Date: 05.10.2017
 * Time: 21:36
 */
class WordSource implements SourceInterface
{
    public $wordsUrl = 'https://raw.githubusercontent.com/dwyl/english-words/master/words.txt';

    public static $wordsList = [];

    public function __construct($wordsUrl = false)
    {
        if ($wordsUrl) {
            $this->wordsUrl = $wordsUrl;
        }
    }

    public function init()
    {
        $this->getWordList();
    }

    /**
     * Get word list
     * @return array
     */
    public function getWordList()
    {
        if (empty(self::$wordsList)) {
            self::$wordsList = explode("\n", file_get_contents($this->wordsUrl));
        }
        return self::$wordsList;
    }

    /**
     * Main method, which prepare data list for parser
     * @return array
     */
    public function getSearchData()
    {
        return $this->getWordList();
    }


}