<?php
// src/Controller/ContactController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactController extends AbstractController
{
    /**
     * @Route("/contact", name="contact")
     */
    public function contact(): Response
    {
        // Obtiene el usuario autenticado actualmente
        $usuario = $this->getUser();

        // Renderiza la plantilla 'contact.html.twig' y pasa el usuario autenticado como parámetro
        return $this->render('contact.html.twig', [
            'usuario' => $usuario,
        ]);
    }
}
?>
