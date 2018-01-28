<?php
namespace Wazisera\Utility\SimpleGearman\Service;

/*
 * This file is part of the Wazisera.Utility.SimpleGearman package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Reflection\ReflectionService;
use Wazisera\Utility\SimpleGearman\Annotations\GearmanFunction;
use Wazisera\Utility\SimpleGearman\Annotations\GearmanWorker;
use Wazisera\Utility\SimpleGearman\Exception;


/**
 * @Flow\Scope("singleton")
 */
class GearmanWorkerService {

    /**
     * @var string
     */
    protected $serverHost = '127.0.0.1';

    /**
     * @var int
     */
    protected $serverPort = 4730;

    /**
     * @var ReflectionService
     * @Flow\Inject
     */
    protected $reflectionService;

    /**
     * @var ObjectManager
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var \GearmanWorker
     */
    protected $worker = null;

    /**
     * @return \GearmanWorker
     */
    public function getWorker() {
        if($this->worker === null) {
            $this->worker = new \GearmanWorker();
            $this->worker->addServer($this->serverHost, $this->serverPort);
        }
        return $this->worker;
    }

    /**
     *
     */
    public function getAllFunctions() {
        $result = array();

        $gmWorkerAnnotationClassName = GearmanWorker::class;
        $gmFunctionAnnotationClassName = GearmanFunction::class;

        $classNames = $this->reflectionService->getClassNamesByAnnotation($gmWorkerAnnotationClassName);
        foreach($classNames as $className) {
            $methodNames = $this->reflectionService->getMethodsAnnotatedWith($className, $gmFunctionAnnotationClassName);

            foreach($methodNames as $methodName) {
                $functionAnnotation = $this->reflectionService->getMethodAnnotation($className, $methodName, $gmFunctionAnnotationClassName);

                if($functionAnnotation instanceof GearmanFunction) {
                    $functionName = $functionAnnotation->name;

                    if(strlen($functionName) === 0) {
                        $functionName = $methodName;
                    }

                    $result[$functionName] = array($className, $methodName);
                }
            }
        }
        return $result;
    }

    /**
     *
     */
    public function startWorker() {
        $worker = $this->getWorker();

        $functions = $this->getAllFunctions();
        $functionsAdded = false;

        foreach($functions as $functionName => $functionInfo) {
            $className = $functionInfo[0];
            $methodName = $functionInfo[1];
            $object = $this->objectManager->get($className);

            if($object != null) {
                $successful = $worker->addFunction($functionName, array($object, $methodName));
                if ($successful === false) {
                    throw new Exception(sprintf('Function %s could not be added.', $functionName), 1517167168);
                }
                $functionsAdded = true;
            }
        }
        if($functionsAdded === true) {
            while ($worker->work());
        }
    }

}

?>