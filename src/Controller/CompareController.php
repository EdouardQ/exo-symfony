<?php

namespace App\Controller;

use App\Entity\History;
use App\Form\SearchFormType;
use App\Repository\HistoryRepository;
use App\Service\ComparisonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompareController extends AbstractController
{
    #[Route('/compare', name: 'compare.index')]
    public function index(ComparisonService $comparisonService, Request $request): Response
    {
        $formattedArray = $comparisonService->getFormattedArray();
        if (empty($formattedArray['names']) || empty($formattedArray['parameters'])) {
            return $this->redirectToRoute('homepage.index');
        }
        $comparisonService->addComparisonToDb($formattedArray);

        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search.index', ['keyword' => $form->getData()['keyword']]);
        }

        return $this->render('compare/index.html.twig', [
            'form' => $form->createView(),
            'data' => $formattedArray,
        ]);
    }

    #[Route('/remove/{id}', name: 'compare.remove')]
    public function remove(int $id, ComparisonService $comparisonService): Response
    {
        $comparisonService->removePlayerToComparison($id);
        // redirect to the comparison page and update cookie
        $response = $this->redirectToRoute('compare.index');
        $response->headers->setCookie($comparisonService->getComparisonNumberCookie());
        return $response;
    }

    #[Route('/purge', name: 'compare.purge')]
    public function add(ComparisonService $comparisonService): Response
    {
        $comparisonService->purgeComparison();
        // redirect to the homepage and update cookie
        $response = $this->redirectToRoute('homepage.index');
        $response->headers->setCookie($comparisonService->getComparisonNumberCookie());
        return $response;
    }

    #[Route('/history', name: 'compare.history')]
    public function history(ComparisonService $comparisonService, HistoryRepository $historyRepository, Request $request): Response
    {
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search.index', ['keyword' => $form->getData()['keyword']]);
        }

        $history = $historyRepository->findBy(['sessionId' => $comparisonService->getSessionId()], ['id' => 'DESC']);

        return $this->render('compare/history.html.twig', [
            'form' => $form->createView(),
            'history' => $history,
        ]);
    }

    #[Route('/history/{id}', name: 'compare.compare_from_history')]
    public function compareFromHistory(History $history, Request $request): Response
    {
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search.index', ['keyword' => $form->getData()['keyword']]);
        }

        return $this->render('compare/index.html.twig', [
            'form' => $form->createView(),
            'data' => $history->getData(),
        ]);
    }
}