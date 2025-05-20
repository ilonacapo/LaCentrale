<?php 

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GithubService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications')]
    public function index(GithubService $githubService, SessionInterface $session): Response
    {
        $accessToken = $this->getUser() ? $this->getUser()->getAccessToken() : null;
        $githubNotifications = $accessToken ? $githubService->apiRequest('https://api.github.com/notifications', $accessToken) : [];

        $internalNotifications = $session->get('notifications', []);

        $unreadNotifications = count($internalNotifications);

        $session->set('unread_notifications', $unreadNotifications);

        return $this->render('notifications.html.twig', [
            'githubNotifications' => $githubNotifications,
            'internalNotifications' => $internalNotifications,
            'unread_notifications' => '2',
        ]);
    }

   /* #[Route('/add-notification', name: 'add_notification')]
    public function addNotification(SessionInterface $session): Response
    {
        $notifications = $session->get('notifications', []);
        $notifications[] = [
            'title' => 'Déploiement ok',
            'message' => 'Le projet X a été mis à jour.',
            'time' => (new \DateTime())->format('d/m/Y H:i')
        ];
        $session->set('notifications', $notifications);

        $session->set('unread_notifications', count($notifications));

        return $this->redirectToRoute('notifications');
    }

    #[Route('/clear-notifications', name: 'clear_notifications')]
    public function clearNotifications(SessionInterface $session): Response
    {
        $session->set('notifications', []);
        $session->set('unread_notifications', 0);

        return $this->redirectToRoute('notifications');
    }*/
}
