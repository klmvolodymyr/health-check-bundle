<?php

namespace VolodymyrKlymniuk\HealthCheckBundle\Check;

//use ZendDiagnostics\Check\CheckCollectionInterface;
use Laminas\Diagnostics\Check\CheckCollectionInterface;

class MongoDbConnection implements CheckCollectionInterface
{
    /**
     * @var array
     */
    private $checks = [];

    /**
     * @param array $configs
     */
    public function __construct(array $configs)
    {
        foreach ($configs as $name => $config) {
            $check = new Mongo($config['uri'], $config['database']);
            $check->setLabel(sprintf('Mongo "%s"', $name));

            $this->checks[sprintf('mongo_%s', $name)] = $check;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getChecks()
    {
        return $this->checks;
    }
}
