<?php

declare(strict_types=1);

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile')]
    public function index(): JsonResponse
    {
        // TODO Make this endpoint only available with Bearer token (token from login)

        dd($this->getUser());
    }
}
