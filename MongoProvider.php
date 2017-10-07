<?php


/**
 * Created by Helpixon
 * User: Helpix
 * Date: 07.10.2017
 * Time: 21:55
 */
class MongoProvider
{
    const DB_NAME = 'easy_parser';

    public $db;
    private $collectionName;

    private static $_instance = null;


    public function addData($rows = [])
    {
        $bulk = new MongoDB\Driver\BulkWrite;
        foreach ($rows as $row) {
            try {
                $bulk->insert($row);
                echo 'Row inserted: "' . array_shift($row) . '"' . PHP_EOL;
            } catch (MongoDB\Driver\Exception\BulkWriteException $e) {
                echo 'Row duplicated: "' . implode(', ', $row) . '"' . PHP_EOL;
            }
        }

        $this->db->executeBulkWrite(self::DB_NAME . '.' . $this->collectionName, $bulk);
    }

    public function dropCollection()
    {
        $this->db->executeCommand(self::DB_NAME, new \MongoDB\Driver\Command(["drop" => $this->collectionName]));
    }

    public function findById($id)
    {
        $result = 'No result.';

        $query = new MongoDB\Driver\Query(['_id' => $id], ['limit' => 1]);

        $rows = $this->db->executeQuery(self::DB_NAME . '.' . $this->collectionName, $query);

        foreach ($rows as $document) {
            $result = $document->value;
            break;
        }
        return $result;
    }

    public function connect()
    {
        $this->db = new MongoDB\Driver\Manager("mongodb://localhost:27017");
    }

    private function __construct($collectionName = null, $removeOldCollection = false)
    {
        // open connection to MongoDB server
        $this->connect();
        $this->collectionName = $collectionName;
        if ($removeOldCollection) {
            $this->dropCollection();
        }
    }

    protected function __clone()
    {
    }

    /**
     * Get MongoDB instance
     */
    static public function getInstance($collectionName = null, $removeOldCollection = false)
    {
        if (!isset(self::$_instance[$collectionName]) && is_null(self::$_instance[$collectionName])) {
            self::$_instance[$collectionName] = new self($collectionName, $removeOldCollection);
        }
        return self::$_instance[$collectionName];
    }

}
