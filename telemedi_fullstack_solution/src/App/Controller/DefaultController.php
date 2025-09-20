<?php
declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /** @Route("/api/setup-check", name="setup_check", methods={"GET"}) */
    public function setupCheck(): JsonResponse
    {
        return $this->json([
            'ok' => true,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    /** @Route("/{wildcard}", name="app_index", requirements={"wildcard"=".*"}) */
    public function index(Request $request): Response
    {
        return $this->render('app-root.html.twig');
    }
}
