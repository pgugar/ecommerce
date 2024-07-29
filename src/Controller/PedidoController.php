<?php

namespace App\Controller;

use App\Entity\Pedido;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class PedidoController extends AbstractController
{
    /**
     * @Route("/mis-pedidos", name="mis_pedidos")
     */
    public function verPedidos(UserInterface $usuario): Response
    {
        // Se obtiene el repositorio de la entidad adecuada y se buscan los pedidos del usuario autenticado.
        $pedidos = $this->getDoctrine()
                        ->getRepository(Pedido::class)
                        ->findBy(['usuario' => $usuario]);

        // Se renderiza la plantilla para que el usuario pueda visualizar sus pedidos.
        return $this->render('misPedidos.html.twig', [
            'usuario' => $usuario,
            'pedidos' => $pedidos,
        ]);
    }
}
?>
