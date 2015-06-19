<?php

namespace MongoDB\Operation;

use MongoDB\Driver\Command;
use MongoDB\Driver\Server;
use MongoDB\Exception\InvalidArgumentException;
use MongoDB\Exception\RuntimeException;

/**
 * Operation for the dropIndexes command.
 *
 * @api
 * @see MongoDB\Collection::dropIndexes()
 * @see http://docs.mongodb.org/manual/reference/command/dropIndexes/
 */
class DropIndexes implements Executable
{
    private $databaseName;
    private $collectionName;
    private $indexName;

    /**
     * Constructs a dropIndexes command.
     *
     * @param string $databaseName   Database name
     * @param string $collectionName Collection name
     * @param string $indexName      Index name (use "*" to drop all indexes)
     * @throws InvalidArgumentException
     */
    public function __construct($databaseName, $collectionName, $indexName)
    {
        $indexName = (string) $indexName;

        if ($indexName === '') {
            throw new InvalidArgumentException('$indexName cannot be empty');
        }

        $this->databaseName = (string) $databaseName;
        $this->collectionName = (string) $collectionName;
        $this->indexName = $indexName;
    }

    /**
     * Execute the operation.
     *
     * @see Executable::execute()
     * @param Server $server
     * @return object Command result document
     */
    public function execute(Server $server)
    {
        $cmd = array(
            'dropIndexes' => $this->collectionName,
            'index' => $this->indexName,
        );

        $cursor = $server->executeCommand($this->databaseName, new Command($cmd));
        $cursor->setTypeMap(array('document' => 'stdClass'));
        $result = current($cursor->toArray());

        // TODO: Remove this once PHPC-318 is implemented
        is_array($result) and $result = (object) $result;

        if (empty($result->ok)) {
            throw new RuntimeException(isset($result->errmsg) ? $result->errmsg : 'Unknown error');
        }

        return $result;
    }
}
