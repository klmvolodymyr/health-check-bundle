<?php

namespace VolodymyrKlymniuk\HealthCheckBundle\Check;

//use ZendDiagnostics\Result\Success;
//use ZendDiagnostics\Result\Failure;
//use ZendDiagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\Success;

class Mongo extends AbstractCheck
{
    /**
     * @var string
     */
    private $connectionUri;

    /**
     * @var string
     */
    private $database;

    /**
     * @param string $connectionUri
     * @param string $database
     */
    public function __construct(string $connectionUri, string $database)
    {
        $this->connectionUri = $connectionUri;
        $this->database = $database;
    }

    /**
     * {@inheritDoc}
     */
    public function check()
    {
        try {
            $this->getListDBCollections();
        } catch (\Exception $e) {
            return new Failure(sprintf('Failed to connect to MongoDB server. Reason: `%s`', $e->getMessage()));
        }

        return new Success();
    }

    /**
     * @return array|\Iterator
     *
     * @throws \RuntimeException
     */
    private function getListDBCollections()
    {
        if (class_exists('\MongoDB\Client')) {
            return (new \MongoDB\Client($this->connectionUri))->selectDatabase($this->database)->listCollections();
        }

        throw new \RuntimeException('Neither the mongo extension or mongodb are installed');
    }
}