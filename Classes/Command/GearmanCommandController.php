<?php
namespace Wazisera\Utility\SimpleGearman\Command;

/*
 * This file is part of the Wazisera.Utility.SimpleGearman package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Wazisera\Utility\SimpleGearman\Service\GearmanWorkerService;


/**
 * Class NotificationCommandController
 * @Flow\Scope("singleton")
 */
class GearmanCommandController extends CommandController {

    /**
     * @var GearmanWorkerService
     * @Flow\Inject
     */
    protected $gearmanWorkerService;

    /**
     * Starts the Gearman worker with all available functions and keep it running.
     */
    public function startCommand() {
        $this->outputLine('Starting gearman worker...');
        $this->gearmanWorkerService->startWorker();
    }

    /**
     * Lists all available gearman functions
     */
    public function listCommand() {
        $functions = $this->gearmanWorkerService->getAllFunctions();
        $rows = array();

        foreach($functions as $functionName => $functionInfo) {
            $className = $functionInfo[0];
            $methodName = $functionInfo[1];

            $rows[] = array($functionName, $className . ' -> ' . $methodName);
        }
        $this->output->outputTable($rows, array('Name', 'Method'));
    }

}

?>