<?php
// src/Controller/PerfilUsuarioController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class PerfilUsuarioController extends AbstractController
{
    
    public function perfilUsuario(): Response
    {
        // Se Obtiene el usuario autenticado.
        $usuario = $this->getUser();
        
        

        return $this->render('perfilUsuario.html.twig', [
            'usuario' => $usuario,
            
        ]);
    }
}
?>
