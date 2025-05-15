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

    public function deploy(string $name, string $version, string $dir): array
    {

        $scriptPath = '../src/deploy_me.sh';
        $process = new Process([$scriptPath, $name, $version, $dir]);
        $process->run();

        dd($process->getOutput());

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            $this->logger->error("Erreur lors de l'exécution du script : {$process->getErrorOutput()}");
            return ['success' => false, 'message' => "Échec du déploiement. Vérifie les logs."];
        }
return ['success' => true, 'message' => "Déploiement terminé"];

    }
}
