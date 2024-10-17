<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class LivToursController extends AbstractController
{
    #[Route('/state', name: 'app_state')]
    public function getAction(Request $request): Response
    {
        $response = new Response();

        $response->setContent($this->getJsonInMemory($request));

        $response->setStatusCode(Response::HTTP_OK);

        $response->headers->set('Content-Type', 'text/plain');

        return $response->send();
    }

    #[Route('/move/{from}/{to}', name: 'app_move')]
    public function postAction(Request $request, string $from, string $to): Response
    {
        $response = new Response();

        $response->setContent($this->getChangedJsonInMemory($request, $response, $from, $to));

        $response->headers->set('Content-Type', 'text/plain');

        return $response->send();
    }

    #[Route('/reset', name: 'app_reset')]
    public function resetAction(Request $request): Response
    {
        $response = new Response();

        $response->setContent(json_encode(['message' => 'ok']));

        $response->setStatusCode(Response::HTTP_OK);

        $response->headers->set('Content-Type', 'text/plain');

        $request->getSession()->set('LivTours', ['message' => 'ok', 'pegA' => ['disk1','disk2','disk3','disk4','disk5','disk6','disk7'], 'pegB' => null, 'pegC' => null]);

        return $response->send();
    }

    private function getJsonInMemory(Request $request): string
    {
        if (!$request->getSession()->get('LivTours')) return json_encode(['message' => 'ok', 'pegA' => ['disk1','disk2','disk3','disk4','disk5','disk6','disk7'], 'pegB' => null, 'pegC' => null]);
        return json_encode($request->getSession()->get('LivTours'));
    }

    private function getChangedJsonInMemory(Request $request, Response $response, string $from = null, string $to = null): string
    {
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        $allPegs = ['pegA', 'pegB', 'pegC'];

        if (!in_array($from, $allPegs)) return json_encode(['message' => 'The starting peg does not exist.']);
        if (!in_array($to, $allPegs)) return json_encode(['message' => 'The arrival peg does not exist.']);
        if ($from == $to) return json_encode(['message' => 'The starting peg cannot be the same as the destination peg.']);

        $puzzle = $this->getPuzzle($request);

        if (!$puzzle[$from]) return json_encode(['message' => 'The starting peg cannot be empty.']);
        if ($this->diskIsSmaller($puzzle[$from], $puzzle[$to])) return json_encode(['message' => 'A larger disk cannot be placed on top of a smaller one.']);

        $response->setStatusCode(Response::HTTP_OK);
        $puzzle[$to] = $this->newStateToPeg($puzzle[$from], $puzzle[$to]);
        $puzzle[$from] = $this->newStateFromPeg($puzzle[$from]);
        $result = array_merge(['message' => 'ok'], $puzzle);
        $request->getSession()->set('LivTours', $result);

        return json_encode($result);
    }

    private function diskIsSmaller(array $from, ?array $to): bool
    {
        if (!$to) return false;
        if (end($from) > end($to)) return false;
        return true;
    }

    private function newStateToPeg(array $from, ?array $to): array
    {
        if (!$to) return [end($from)];
        return array_merge($to, [end($from)]);
    }

    private function newStateFromPeg(array $from): ?array
    {
        array_pop($from);
        if (0 === count($from)) return null;
        return $from;
    }

    private function getPuzzle(Request $request): array
    {
        if (!$request->getSession()->get('LivTours')) return ['pegA' => ['disk1','disk2','disk3','disk4','disk5','disk6','disk7'], 'pegB' => null, 'pegC' => null];

        $puzzleWithMessage = $request->getSession()->get('LivTours');
        unset($puzzleWithMessage['message']);

        return $puzzleWithMessage;
    }
}
