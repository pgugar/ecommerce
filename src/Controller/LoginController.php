<?php
// src/Controller/LoginController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    
    public function login(AuthenticationUtils $authenticationUtils, SessionInterface $session)
    {
        $usuario = $this->getUser();

        // Si el usuario ya está autenticado, redirigir a la página principal
        if ($usuario) {
            // Cargar el carrito de la sesión del usuario si existe
            $carritoGuardado = $session->get('carrito_guardado_' . $usuario->getId(), []);
            if (!empty($carritoGuardado)) {
                $session->set('carrito', $carritoGuardado);
            }

            return $this->redirectToRoute('base');
        }

        // Obtener el error de inicio de sesión si existe
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    
    public function logout(SessionInterface $session)
    {
        //Se realizó modificación posterior, por errores que surgieron con el carrito. De esta forma, Symfony maneja automáticamente esta parte.
    }
}
?>
