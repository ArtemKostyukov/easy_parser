<?php

require_once 'MongoProvider.php';

/**
 * Created by Helpixon
 * User: Helpix
 * Date: 05.10.2017
 * Time: 21:06
 */
class Parser
{
    const PAGES_FOLDER = 'pages';
    const SYNONYMS = 'synonyms';

    public $sourceUrl = 'http://www.wordreference.com';
    public $optionsUrl = 'http://www.wordreference.com/2012/scripts/allOptions.min.js';

    private $sourceObject;
    private $optionsList = [];

    public function __construct($sourceObject = null)
    {
        $this->sourceObject = $sourceObject;
    }

    /**
     * Define search options
     * @return array
     */
    public function defineAllOptions()
    {
        if (!$this->optionsList) {
            $optionsData = $this->makeRequest($this->optionsUrl);
            preg_match_all('|<option [^>]+>(.*)</option>|U', $optionsData, $match);
            foreach ($match[0] as $k => $item) {
                preg_match('|value="(.*)"|U', $item, $matchOption);
                $this->optionsList[$matchOption[1]] = $match[1][$k];
            }
        }
        return $this->optionsList;
    }

    /**
     * Search all data and store to files (parse drop down params on page)
     */
    public function searchDataAndStoreByAllOptions()
    {
        $this->defineAllOptions();
        foreach ($this->optionsList as $value => $name) {
            foreach ($this->sourceObject->getSearchData() as $word) {
                $this->saveData($this->makeRequest($this->sourceUrl . '/' . $value . '/' . urlencode($word)), $value, $word);
            }
        }
    }

    /**
     * Search all data and store to files only
     */
    public function searchDataAndStoreBySynonymsOnly()
    {
        foreach ($this->sourceObject->getSearchData() as $word) {
            $this->saveData($this->makeRequest($this->sourceUrl . '/' . self::SYNONYMS . '/' . urlencode($word)), self::SYNONYMS, $word);
        }
    }

    /**
     * Make request
     * @param $url
     * @return mixed
     */
    public function makeRequest($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.4 (KHTML, like Gecko) Chrome/5.0.375.125 Safari/533.4");
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * Get content from page
     * @param string $data
     * @return DOMDocument
     */
    public function getContentFromPage($data = '')
    {
        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($data);
        libxml_clear_errors();
        $contentBlock = $this->findHtmlElement($dom, 'content', 'class');

        $dom = new DOMDocument();
        $dom->loadHTML('<html><body>' . $contentBlock . '</body></html>');

        return $this->findHtmlElement($dom, 'article', 'id', 'div');
    }

    public function saveData($data = '', $folderName = '', $fileName = '')
    {
        $collectionName = $folderName;
        $folderName = self::PAGES_FOLDER . '/' . $folderName;
        if (!is_dir($folderName)) {
            mkdir($folderName);
        }

        //Store to the mongoDB
        $mongoProvider = MongoProvider::getInstance($collectionName, true);
        $mongoProvider->addData([['_id' => $fileName, 'value' => $this->getContentFromPage($data)]]);

        //Store to the file
        $fileName = str_replace(['/', '\\'], '', $fileName);
        file_put_contents($folderName . '/' . $fileName . '.html', $data);
    }

    /**
     * Find html element by attributes
     * @param $dom
     * @param string $class
     * @param string $tagName
     * @return DOMNodeList
     */
    private function findHtmlElement($dom, $tagSearchLabel = '', $tagSearchType = '', $tagName = '*')
    {
        $finder = new DomXPath($dom);
        $xpathResult = $finder->query("//" . $tagName . "[contains(concat(' ', @$tagSearchType, ' '), ' $tagSearchLabel ')]");
        return $dom->saveHTML($xpathResult->item(0));
    }


}