<?php

namespace App\Controller\Admin;

use App\Repository\ReservationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AgendaController extends AbstractController
{
    #[Route('/admin/agenda', name: 'admin_agenda')]
    public function index(ReservationRepository $reservationRepository): Response
    {
        // Récupérer toutes les réservations confirmées et en attente
        $reservations = $reservationRepository->createQueryBuilder('r')
            ->where('r.status IN (:statuses)')
            ->setParameter('statuses', ['CONFIRMED', 'PENDING_PAYMENT', 'PENDING'])
            ->orderBy('r.dateRdv', 'ASC')
            ->getQuery()
            ->getResult();
        
        // Préparer les événements pour FullCalendar
        $calendarEvents = [];
        foreach ($reservations as $reservation) {
            $start = $reservation->getDateRdv();
            $end = clone $start;
            $end->modify('+' . $reservation->getTotalDuration() . ' minutes');
            
            $calendarEvents[] = [
                'title' => $reservation->getGuestFirstname() . ' ' . $reservation->getGuestLastname(),
                'start' => $start->format('Y-m-d\TH:i:s'),
                'end' => $end->format('Y-m-d\TH:i:s'),
                'extendedProps' => [
                    'address' => $reservation->getVisitAddress(),
                    'services' => $reservation->getServices()->map(fn($s) => $s->getTitle())->toArray(),
                ],
            ];
        }
        
        return $this->render('admin/agenda.html.twig', [
            'reservations' => $reservations,
            'calendar_events' => json_encode($calendarEvents),
        ]);
    }
}
