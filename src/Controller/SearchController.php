<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Service\BallDontLieService;
use App\Service\ComparisonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/search/{keyword}', name: 'search.index')]
    public function index(string $keyword, Request $request, BallDontLieService $ballDontLieService): Response
    {
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search.index', ['keyword' => $form->getData()['keyword']]);
        }

        $response = $ballDontLieService->search($keyword);
        $data = json_decode($response, true)['data'];

        return $this->render('search/index.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/add/{id}/{name}', name: 'search.add')]
    public function add(int $id, string $name, ComparisonService $comparisonService, Request $request): Response
    {
        $comparisonService->addPlayerToComparison($id, $name);
        // redirect to the previous research and update cookie
        $response = $this->redirect($request->headers->get('referer'));
        $response->headers->setCookie($comparisonService->getComparisonNumberCookie());
        return $response;
    }
}
