<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DeploymentService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function deploy(string $name, string $version, string $dir): bool
    {


        $scriptPath = '../src/deploy_me.sh';
        $process = new Process([$scriptPath, $name, $version, $dir]);
        $process->setTimeout(0);

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                echo json_encode(['type' => 'error', 'message' => nl2br($buffer)]) . "\n";
            } else {
                echo json_encode(['type' => 'log', 'message' => nl2br($buffer)]) . "\n";
            }
            ob_flush();
            flush();
        });
        return true;
    }
}
