<?php

namespace App\Controller;

use App\Entity\History;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HistoryController extends AbstractController
{
    #[Route('/exchange/values', name: 'app_exchange_values', methods: ['post'])]
    public function create(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $first = (int)$request->request->get('first');
        $second = (int)$request->request->get('second');

        if (!$first || !$second) {
            return $this->json('Invalid data', 400);
        }
        $history = new History();
        $history->setFirstIn($first);
        $history->setFirstOut($first);
        $history->setSecondIn($second);
        $history->setSecondOut($second);

        $entityManager->persist($history);
        $entityManager->flush();

        [$first, $second] = [$second, $first];
        $history->setFirstIn($first);
        $history->setFirstOut($first);
        $history->setSecondIn($second);
        $history->setSecondOut($second);

        $history->update();
        $entityManager->flush();

        return $this->json(['message' => 'History created']);
    }

    #[Route('/history', name: 'app_list', methods: ['post', 'get'])]
    public function list(EntityManagerInterface $entityManager, SerializerInterface $serializer, Request $request): Response
    {
        $page = (int)$request->query->get('page', 1);
        $order = $request->query->get('order');
        $direction = $request->query->get('direction');

        $histories = $entityManager->getRepository(History::class)->findOrderedAll($page, $order, $direction);
        $jsonResponse = $serializer->serialize($histories, 'json');
        return new Response($jsonResponse);
    }
}
