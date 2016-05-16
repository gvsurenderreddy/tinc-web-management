<?php

/**
 * Created by PhpStorm.
 * User: tknapp
 * Date: 08.05.16
 * Time: 22:03
 */

namespace AppBundle\Service;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
     *
     * @throws ProcessFailedException
     *
     * @return Process
     */
    public function invite($nodeName) {
        $process = new Process("tinc -n {$this->networkName} invite {$nodeName}");
        $process->mustRun();

        return $process;
    }

    /**
     * Writes 'ConnectTo' statements to new node's invitation file.
     *
     * @param $nodeName
     * @param $arNodeNames array of string of node names
     */
    public function writeConnectToNodes($nodeName, $arNodeNames){

        $pathInvitations = $this->pathTinc . $this->networkName . DIRECTORY_SEPARATOR . 'invitations';

        $grepProc = new Process("grep -F -L -e {$nodeName} -d recurse -l", $pathInvitations);
        $grepProc->mustRun();
        if(!$grepProc->getExitCode()) {
            $fileName = $pathInvitations . '/' .trim($grepProc->getOutput());

            // Load config
            $arLines = explode(PHP_EOL, file_get_contents($fileName));

            // Backup values
            $valueName = array_shift($arLines);
            $valueNetwork = array_shift($arLines);

            // Write 'ConnectTo' statements
            foreach ($arNodeNames as $nodeName) {
                array_unshift($arLines, "ConnectTo = {$nodeName['name']}");
            }

            // Restore values
            array_unshift($arLines, $valueNetwork);
            array_unshift($arLines, $valueName);

            // Save edited file
            file_put_contents($fileName, implode(PHP_EOL, $arLines));
        } else {
            throw new ProcessFailedException($grepProc);
        }
    }

    /**
     * Returns full hostfile path for nodes name
     *
     * @param $nodeName
     * @return string Path to host file
     */
    public function getHostFileName($nodeName){
        return $this->pathTinc . $this->networkName . '/hosts/' . $nodeName;
    }
}
