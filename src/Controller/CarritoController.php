<?php

namespace App\Controller;

use App\Entity\Producto;
use App\Entity\Pedido;
use App\Entity\DetallePedido;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EmailServicio;


class CarritoController extends AbstractController
{
    private $emailServicio;
 

    // Inyección de dependencias para el servicio de correo electrónico y el logger
    public function __construct(EmailServicio $emailServicio)
    {
        $this->emailServicio = $emailServicio;
        
    }

    /**
     * @Route("/añadir-producto/{id}", name="añadir_producto", methods={"POST"})
     */
    public function añadirProducto(int $id, Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Busca el producto por su ID
        $producto = $entityManager->getRepository(Producto::class)->find($id);
        if (!$producto) {
            // Redirige al carrito con un mensaje de error si el producto no existe
            return $this->redirectToRoute('ver_carrito', [
                'mensaje' => 'El producto no existe',
                'mensaje_tipo' => 'error'
            ]);
        }

        // Obtiene la cantidad solicitada, asegurándose de que sea al menos 1
        $cantidad = max(1, (int) $request->request->get('cantidad', 1));
        if ($producto->getStock() < $cantidad) {
            // Redirige al carrito con un mensaje de error si no hay suficiente stock
            return $this->redirectToRoute('ver_carrito', [
                'mensaje' => 'No hay suficiente stock disponible',
                'mensaje_tipo' => 'error'
            ]);
        }

        // Obtiene el carrito del usuario autenticado o anónimo
        $usuario = $this->getUser();
        $carritoKey = $usuario ? 'carrito_' . $usuario->getId() : 'carrito_anonymous';
        $carrito = $session->get($carritoKey, []);

        // Busca si el producto ya está en el carrito y actualiza la cantidad y el total
        $found = false;
        foreach ($carrito as &$item) {
            if ($item['id'] === $producto->getId()) {
                $item['cantidad'] += $cantidad;
                $item['total'] = $item['precio'] * $item['cantidad'];
                $found = true;
                break;
            }
        }

        // Si el producto no está en el carrito, lo agrega
        if (!$found) {
            $carrito[] = [
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'precio' => $producto->getPrecio(),
                'cantidad' => $cantidad,
                'total' => $producto->getPrecio() * $cantidad,
            ];
        }

        // Guarda el carrito actualizado en la sesión
        $session->set($carritoKey, $carrito);

        // Redirige a la vista del carrito
        return $this->redirectToRoute('ver_carrito');
    }

    /**
     * @Route("/eliminar_producto_id/{id}", name="eliminar_producto_id", methods={"POST"})
     */
    public function eliminarProducto($id, Request $request, SessionInterface $session): Response
    {
        // Verifica el token CSRF para seguridad
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('eliminar' . $id, $token)) {
            throw $this->createAccessDeniedException('Token CSRF no válido');
        }

        // Obtiene el carrito del usuario autenticado o anónimo
        $usuario = $this->getUser();
        $carritoKey = $usuario ? 'carrito_' . $usuario->getId() : 'carrito_anonymous';
        $carrito = $session->get($carritoKey, []);

        // Busca y elimina el producto del carrito
        foreach ($carrito as $key => $producto) {
            if ($producto['id'] == $id) {
                unset($carrito[$key]);
                break;
            }
        }

        // Reindexa el carrito y lo guarda en la sesión
        $carrito = array_values($carrito);
        $session->set($carritoKey, $carrito);

        // Redirige a la vista del carrito con un mensaje de éxito
        return $this->redirectToRoute('ver_carrito', [
            'mensaje' => 'Producto eliminado del carrito',
            'mensaje_tipo' => 'success'
        ]);
    }

    /**
     * @Route("/editar-producto/{id}", name="editar_producto", methods={"POST"})
     */
    public function editarProducto(int $id, Request $request, EntityManagerInterface $entityManager, SessionInterface $session): Response
    {
        // Verifica el token CSRF para seguridad
        if (!$this->isCsrfTokenValid('edit' . $id, $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF no válido');
        }

        // Obtiene la nueva cantidad solicitada, asegurándose de que sea al menos 1
        $nuevaCantidad = max(1, (int) $request->request->get('cantidad'));
        $producto = $entityManager->getRepository(Producto::class)->find($id);

        // Verifica si el producto existe
        if (!$producto) {
            return $this->redirectToRoute('ver_carrito', [
                'mensaje' => 'El producto no existe',
                'mensaje_tipo' => 'error'
            ]);
        }

        // Obtiene el carrito del usuario autenticado o anónimo
        $usuario = $this->getUser();
        $carritoKey = $usuario ? 'carrito_' . $usuario->getId() : 'carrito_anonymous';
        $carrito = $session->get($carritoKey, []);

        // Busca el producto en el carrito y actualiza su cantidad y total
        foreach ($carrito as &$item) {
            if ($item['id'] === $id) {
                $cantidadActual = $item['cantidad'];
                $diferencia = $nuevaCantidad - $cantidadActual;
                if ($producto->getStock() < $diferencia) {
                    return $this->redirectToRoute('ver_carrito', [
                        'mensaje' => 'No hay suficiente stock disponible',
                        'mensaje_tipo' => 'error'
                    ]);
                }

                $item['cantidad'] = $nuevaCantidad;
                $item['total'] = $item['precio'] * $nuevaCantidad;
                break;
            }
        }

        // Guarda el carrito actualizado en la sesión
        $session->set($carritoKey, $carrito);

        // Redirige a la vista del carrito con un mensaje de éxito
        return $this->redirectToRoute('ver_carrito', [
            'mensaje' => 'Producto actualizado en el carrito',
            'mensaje_tipo' => 'success'
        ]);
    }

    /**
     * @Route("/ver-carrito", name="ver_carrito")
     */
    public function verCarrito(SessionInterface $session, Request $request): Response
    {
        // Obtiene el carrito del usuario autenticado o anónimo
        $usuario = $this->getUser();
        $carritoKey = $usuario ? 'carrito_' . $usuario->getId() : 'carrito_anonymous';
        $carrito = $session->get($carritoKey, []);
        $totalCarrito = 0;

        // Calcula el total del carrito
        foreach ($carrito as &$item) {
            $item['total'] = $item['precio'] * $item['cantidad'];
            $totalCarrito += $item['total'];
        }

        // Obtiene mensajes opcionales de la URL
        $mensaje = $request->query->get('mensaje', null);
        $mensajeTipo = $request->query->get('mensaje_tipo', null);

        // Renderiza la vista del carrito
        return $this->render('ver.html.twig', [
            'usuario' => $usuario,
            'carrito' => $carrito,
            'totalCarrito' => $totalCarrito,
            'mensaje' => $mensaje,
            'mensaje_tipo' => $mensajeTipo,
        ]);
    }

    /**
     * @Route("/checkout", name="checkout", methods={"POST"})
     */
    public function checkout(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): Response
    {
        // Obtiene el usuario autenticado
        $usuario = $this->getUser();

        if (!$usuario) {
            // Redirige al carrito con un mensaje de error si el usuario no está autenticado
            return $this->redirectToRoute('ver_carrito', [
                'mensaje' => 'Usuario no autenticado',
                'mensaje_tipo' => 'error'
            ]);
        }

        // Obtiene el carrito del usuario
        $carritoKey = 'carrito_' . $usuario->getId();
        $carrito = $session->get($carritoKey, []);

        // Calcula el total del carrito
        $totalCarrito = array_sum(array_map(function($item) {
            return $item['total'];
        }, $carrito));

        // Verifica si el saldo del usuario es suficiente
        if ($usuario->getSaldo() < $totalCarrito) {
            return $this->redirectToRoute('ver_carrito', [
                'mensaje' => 'Saldo insuficiente',
                'mensaje_tipo' => 'error'
            ]);
        }

        // Inicia una transacción para el proceso de checkout
        $entityManager->beginTransaction();
        try {
            $pedido = new Pedido();
            $pedido->setUsuario($usuario);
            $pedido->setFecha(new \DateTime());
            $pedido->setTotal($totalCarrito);
            $entityManager->persist($pedido);

            // Procesa cada ítem en el carrito
            foreach ($carrito as $item) {
                $producto = $entityManager->getRepository(Producto::class)->find($item['id']);
                if (!$producto || $producto->getStock() < $item['cantidad']) {
                    throw new \Exception('Producto no disponible o stock insuficiente');
                }

                $detallePedido = new DetallePedido();
                $detallePedido->setPedido($pedido);
                $detallePedido->setProducto($producto);
                $detallePedido->setCantidad($item['cantidad']);
                $detallePedido->setPrecio($item['precio']);
                $entityManager->persist($detallePedido);

                // Reduce el stock del producto
                $producto->setStock($producto->getStock() - $item['cantidad']);
            }

            // Deduce el total del carrito del saldo del usuario
            $usuario->setSaldo($usuario->getSaldo() - $totalCarrito);
            $entityManager->persist($usuario);

            // Limpia el carrito
            $session->set($carritoKey, []);
            $entityManager->flush();
            $entityManager->commit();

            // Envía un correo de confirmación de compra
            $this->emailServicio->sendPurchaseConfirmationEmail($usuario->getEmail());

            // Redirige a la vista del carrito con un mensaje de éxito
            return $this->redirectToRoute('ver_carrito', [
                'mensaje' => 'Compra realizada con éxito',
                'mensaje_tipo' => 'success'
            ]);
        } catch (\Exception $e) {
            // Realiza un rollback en caso de error y registra el error
            $entityManager->rollback();
            $this->logger->error('Error al procesar la compra: ' . $e->getMessage());
            return $this->redirectToRoute('ver_carrito', [
                'mensaje' => 'Error al procesar la compra. Por favor, inténtalo de nuevo más tarde.',
                'mensaje_tipo' => 'error'
            ]);
        }
    }
}

