# ecommerce
DESCRIPCIÓN GENERAL:  
eCommerce para la compra y venta de productos en línea. 

TECNOLOGÍAS, LENGUAJES DE PROGRAMACIÓN Y FRAMEWORKS: 
- PHP
- HTML5
- CSS3
- BootStrap
- Symfony
- MySQL
- SQL

CARACTERÍSTICAS: 
1.- USUARIOS: 
a) Definición: 
La aplicación contempla dos tipos de usuarios:
- Usuarios generales: son aquellos, que navegan de forman general por la aplicación, no tienen que estar registrados ni haber iniciado sesión.
- Usuarios autenticados: son que se han registrado y han inciado sesión.
b) Acciones:
- Usuarios generales: podrán visualizar los productos, registrarse y añadir los productos al carrito.
- Usuarios autenticados: podrán realizar las gestiones de los productos en el carrito (incluyendo comprar), iniciar sesión, ver su perfil, ver su historial de pedidos y cerrar sesión.

2.-SISTEMA DE PAGO:
En esta aplicación cada usuario tiene asociado un saldo que se modifica a través de la BBDD. Además, como promoción de registro contempla que los usuarios de "nuevo registro" comiencen con un saldo de 100 euros.

3.-PRODUCTOS:
a) Filtros:
La aplicación filtra atendiendo a dos parámetros de los productos:
- Marca: se pueden filtrar los productos atendiendo a la marca de los mismos.
- Categoría: se pueden filtrar los productos atendiendo a la categoría de los mismos.
Según la lógica de negocio de la aplicación estas clasificaciones son independientes entre sí.

CARPETAS Y ARCHIVOS:

Es una aplicación realizada con Symfony. En este repositorio se incluyen las carpetas fundamentales para el funcionamiento y personalización del proyecto. 

a) src
- Controller.
- Entity.
- Form.
- Templates.





