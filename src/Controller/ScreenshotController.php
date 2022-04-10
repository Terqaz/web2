<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// use App\Repository\ScreenshotRepository;

class ScreenshotController extends AbstractController
{
    #[Route('/screenshot/all', name: 'app_screenshots_all', methods: ['GET'])]
    public function getAll(ScreenshotRepository $screenshotRepository)
    {
        $screenshots = screenshotRepository->findAll();

        return $this->render('screenshot/all.html.twig', [
            'screenshots' => $screenshots,
        ]);
    }
}
