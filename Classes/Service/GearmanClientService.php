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
     * @Flow\InjectConfiguration(path="serverIP")
     * @var string
     */
    protected $serverIP = '127.0.0.1';

    /**
     * @Flow\InjectConfiguration(path="serverPort")
     * @var int
     */
    protected $serverPort = 4730;

    /**
     * @var \GearmanClient
     */
    protected $client = null;

    /**
     * @var callable[]
     */
    protected $completeCallbacks = array();

    /**
     * @var callable[]
     */
    protected $failCallbacks = array();

    /**
     * @return \GearmanClient
     */
    public function getClient() {
        if($this->client === null) {
            $this->client = new \GearmanClient();
            $this->client->addServer($this->serverIP, $this->serverPort);
            $this->client->setCompleteCallback(array($this, 'completeCallback'));
            $this->client->setFailCallback(array($this, 'failCallback'));
        }
        return $this->client;
    }

    /**
     * @param string $functionName
     * @param string $workload
     * @return \GearmanTask|bool
     */
    public function addBackgroundTask($functionName, $workload) {
        return $this->getClient()->addTaskBackground($functionName, $workload);
    }

    /**
     * @return bool
     */
    public function run() {
        return $this->getClient()->runTasks();
    }

    /**
     * @param callable $callback
     */
    public function addCompleteCallback(callable $callback) {
        $this->completeCallbacks[] = $callback;
    }

    /**
     * @param callable $callback
     */
    public function addFailCallback(callable $callback) {
        $this->failCallbacks[] = $callback;
    }

    /**
     * @param \GearmanTask $task
     */
    protected function completeCallback(\GearmanTask $task) {
        foreach($this->completeCallbacks as $completeCallback) {
            call_user_func($completeCallback, $task);
        }
    }

    /**
     * @param \GearmanTask $task
     */
    protected function failCallback(\GearmanTask $task) {
        foreach($this->failCallbacks as $failCallback) {
            call_user_func($failCallback, $task);
        }
    }

}

?>