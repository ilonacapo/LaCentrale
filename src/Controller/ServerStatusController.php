<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ServerStatusController extends AbstractController
{
    #[Route('/server-status', name: 'server_status')]
    public function index(): JsonResponse
    {
        $diskFree = disk_free_space("/") / 1_073_741_824;
        $diskTotal = disk_total_space("/") / 1_073_741_824; 
        $memoryUsage = round(memory_get_usage() / 1_048_576, 2);
        $memoryLimit = ini_get('memory_limit');

        $cpuLoad = sys_getloadavg()[0]; 

        return new JsonResponse([
            'disk' => [
                'free' => round($diskFree, 2),
                'total' => round($diskTotal, 2)
            ],
            'memory' => [
                'usage' => $memoryUsage,
                'limit' => $memoryLimit
            ],
            'cpu' => [
                'load' => $cpuLoad
            ]
        ]);
    }
}
