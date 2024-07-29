<?php
//src/Controller/AboutUsController.php
namespace App\Controller;
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
