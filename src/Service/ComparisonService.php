<?php

namespace App\Service;

use App\Entity\History;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\String\ByteString;

class ComparisonService
{
    private EntityManagerInterface $entityManager;
    private RequestStack $requestStack;

    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function getCurrentComparison(): array
    {
        $comparisonArray = $this->getSession()->get('compare');

        if (!$comparisonArray) {
            $this->getSession()->set('compare', $comparisonArray);
        }

        if ($this->getSessionId() == null) {
            $this->getSession()->set('id', ByteString::fromRandom());
        }

        return $comparisonArray;
    }

    public function getFormattedArray(): array
    {
        $array = $this->getCurrentComparison();
        $formattedArray = [];
        $formattedArray['parameters'] = "";
        foreach ($array as $player) {
            foreach ($player as $id => $name)
            {
                $formattedArray['names'][$id] = $name;
                $formattedArray['parameters'] = $formattedArray['parameters'] . "&player_ids[]=" . $id;
            }
        }

        return $formattedArray;
    }

    public function addPlayerToComparison(int $id, string $name): void
    {
        $comparisonArray = $this->getCurrentComparison();
        if (!$this->ifDuplicateComparison($comparisonArray, $id)) {
            $comparisonArray[] = [$id => $name];
            $this->setCurrentComparison($comparisonArray);
        }
    }

    public function removePlayerToComparison(int $idToRemove): void
    {
        $comparisonArray = $this->getCurrentComparison();
        foreach ($comparisonArray as $key => $player) {
            foreach ($player as $id => $name) {
                if ($id == $idToRemove) {
                    unset($comparisonArray[$key]);
                    break 2; // exit the two foreach loop
                }
            }
        }
        $this->setCurrentComparison($comparisonArray);
    }

    public function purgeComparison(): void
    {
        $this->getSession()->set('compare', []);
    }

    public function getComparisonNumberCookie(): Cookie
    {
        return Cookie::create('comparison')
            ->withValue($this->getComparisonNumber())
            ->withExpires(time() + 3600) // 1h
            ->withSecure(false)
            ->withHttpOnly(false)
            ;
    }

    public function addComparisonToDb(array $formattedArray): void
    {
        $history = new History();
        $history->setData($formattedArray)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setSessionId($this->getSessionId())
        ;
        if (!$this->ifDuplicateHistory($history)) {
            $this->entityManager->persist($history);
            $this->entityManager->flush();
        }
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    private function setCurrentComparison(array $comparisonArray): void
    {
        $this->getSession()->set('compare', $comparisonArray);
    }

    public function getSessionId(): ?string
    {
        return $this->getSession()->get('id');
    }

    private function getComparisonNumber(): int
    {
        $n = 0;
        foreach ($this->getCurrentComparison() as $player) {
            $n+=1;
        }
        return $n;
    }

    private function ifDuplicateComparison(array $comparisonArray, int $id): bool
    {
        $verif = false;
        foreach ($comparisonArray as $player) {
            foreach ($player as $key => $value) {
                if ($key === $id) {
                    $verif = true;
                }
            }
        }
        return $verif;
    }

    private function ifDuplicateHistory(History $entity): bool
    {
        $verif = false;
        $historyFromSession = $this->entityManager->getRepository(History::class)->getHistoryFromSession($entity->getSessionId());
        foreach ($historyFromSession as $history) {
            if ($entity->getData() === $history->getData()) {
                $verif = true;
                break;
            }
        }
        return $verif;
    }
}
