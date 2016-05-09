<?php

/**
 * Created by PhpStorm.
 * User: tknapp
 * Date: 08.05.16
 * Time: 22:03
 */

namespace AppBundle\Service;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TincService
{
    private $pathTinc;
    private $networkName;

    public function __construct($pathTinc, $networkName) {
        $this->pathTinc = $pathTinc;
        $this->networkName = $networkName;
        // TODO: Check if $networkName exists
    }

    /**
     * @param $nodeName
     * @return null|Process
     */
    public function invite($nodeName) {
        $process = new Process("tinc -n {$this->networkName} invite {$nodeName}");
        try {
            $process->mustRun();
            return $process;
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }

        return null;
    }
}
