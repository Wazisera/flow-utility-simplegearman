<?php
namespace Wazisera\Utility\SimpleGearman\Service;

/*
 * This file is part of the Wazisera.Utility.SimpleGearman package.
 */

use Neos\Flow\Annotations as Flow;


/**
 * @Flow\Scope("singleton")
 */
class GearmanClientService {

    /**
     * @var string
     */
    protected $serverHost = '127.0.0.1';

    /**
     * @var int
     */
    protected $serverPort = 4730;

    /**
     * @var \GearmanClient
     */
    protected $client = null;

    /**
     * @return \GearmanClient
     */
    public function getClient() {
        if($this->client === null) {
            $this->client = new \GearmanClient();
            $this->client->addServer($this->serverHost, $this->serverPort);
            //$this->client->setCompleteCallback(array());
            //$this->client->setFailCallback(array());
        }
        return $this->client;
    }

    /**
     *
     */
    public function run() {
        $client = $this->getClient();
    }

}

?>