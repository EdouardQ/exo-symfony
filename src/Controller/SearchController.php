<?php

namespace App\Controller;

use App\Form\SearchFormType;
use App\Service\BallDontLieService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    private BallDontLieService $ballDontLieService;

    public function __construct(BallDontLieService $ballDontLieService)
    {
        $this->ballDontLieService = $ballDontLieService;
    }

    #[Route('/search/{keyword}', name: 'search.index')]
    public function index(string $keyword, Request $request): Response
    {
        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search.index', ['keyword' => $form->getData()['keyword']]);
        }

        $response = $this->ballDontLieService->search($keyword);
        $data = json_decode($response, true)['data'];

        return $this->render('search/index.html.twig', [
            'data' => $data,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/compare', name: 'search.compare')]
    public function compare(Request $request): Response
    {
        if (!$request->request->has('checkboxArray') || count($request->request->all()['checkboxArray']) == 1) {
            // if no form is submitted or no comparison is done  => redirect to the previous page
            return $this->redirect($request->headers->get('referer'));
        }

        $form = $this->createForm(SearchFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('search.index', ['keyword' => $form->getData()['keyword']]);
        }

        $playersName = [];
        $playersParameters = "";
        foreach ($request->request->all()['checkboxArray'] as $playerName => $playerId) {
            $playersName[$playerId] = $playerName;
            $playersParameters = $playersParameters . "&player_ids[]=" . $playerId;
        }
        // order the array by key
        ksort($playersName);

        return $this->render('search/compare.html.twig', [
            'form' => $form->createView(),
            'playersParameters' => $playersParameters,
            'playersName' => $playersName
        ]);
    }
}
