<?php
// src/Controller/AboutUsController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AboutUsController extends AbstractController
{
    /**
     * @Route("/aboutUs", name="aboutUs")
     */
    public function contact(): Response
    {
        // Obtiene el usuario autenticado actualmente
        $usuario = $this->getUser();

        return $this->render('aboutUs.html.twig', [
            'usuario' => $usuario,
        ]);
    }
}
?>

