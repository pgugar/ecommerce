<?php
//src/Controller/BaseController.php
namespace App\Controller;
class BaseController extends AbstractController
{
    /**
     * @Route("/base", name="base")
     */
    public function base(): Response
    {
        // Obtiene el usuario autenticado actualmente
        $usuario = $this->getUser();

        return $this->render('base.html.twig', [
            'usuario' => $usuario,
        ]);
    }
}
?>
