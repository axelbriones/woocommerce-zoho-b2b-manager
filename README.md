# WooCommerce Zoho B2B Manager

**Extiende WooCommerce con funcionalidades B2B/B2C y se integra con Zoho, complementando el plugin existente WooCommerce Zoho Integration.**

Este plugin proporciona un conjunto de herramientas para empresas que gestionan ventas B2B y B2C a través de WooCommerce, con una integración diseñada para trabajar con el ecosistema Zoho. Está pensado para funcionar junto al plugin [WooCommerce Zoho Integration de axelbriones](https://github.com/axelbriones/woocommerce-zoho-integration) (o similar), mejorando sus capacidades con características específicas para B2B.

## Características Principales (Fase Actual)

*   **Gestión de Solicitudes B2B:**
    *   Formulario de solicitud personalizable (`[wc_zoho_b2b_application_form]`) para que los clientes soliciten una cuenta B2B.
    *   Panel de administración para ver, aprobar y rechazar solicitudes.
    *   Creación automática de usuarios de WordPress o asignación de roles a usuarios existentes tras la aprobación.
*   **Roles de Usuario B2B:**
    *   Roles personalizados (`B2B Customer (Pending)`, `B2B Customer`) para gestionar el acceso y los permisos.
*   **Precios Dinámicos B2B:**
    *   Aplicación de descuentos porcentuales basados en el rol del usuario B2B.
    *   Los precios se actualizan en las páginas de producto, catálogo y carrito/checkout.
*   **Lista de Deseos (Wishlist):**
    *   Funcionalidad de lista de deseos para usuarios registrados (`[wc_zoho_b2b_wishlist]`).
    *   Botones para añadir/eliminar de la lista de deseos en páginas de producto (con AJAX).
*   **Integración con Zoho CRM:**
    *   Sincronización de nuevos clientes B2B aprobados a Zoho CRM como Cuentas (empresas) y Contactos.
    *   Posibilidad de reutilizar la configuración de API del plugin principal `woocommerce-zoho-integration` o usar credenciales específicas para B2B.
    *   Flujo de autorización OAuth2 para la conexión con Zoho API.
*   **Panel de Administración Dedicado:**
    *   Menú "Zoho B2B" en el admin de WordPress con submenús para:
        *   Configuración General (modos de registro, visibilidad de precios, rol B2B por defecto, etc.).
        *   Gestión de Aplicaciones (tabla para aprobar/rechazar).
        *   Configuración de Roles y Precios (método de precios, descuentos por rol).
        *   Configuración de la Integración con Zoho (credenciales, opciones de sincronización).
*   **Internacionalización:** Preparado para traducción con text domain `wc-zoho-b2b`.
*   **Documentación:** Incluye guías de instalación, configuración y referencia API/hooks.

## Requisitos

*   WordPress 6.0+
*   WooCommerce 9.0+
*   PHP 7.4+
*   (Recomendado) Plugin `woocommerce-zoho-integration` si se desea reutilizar su configuración de API Zoho.

## Instalación

1.  Descarga la última versión del plugin (archivo ZIP) desde la sección de [Releases](URL_A_RELEASES_CUANDO_EXISTA) (o clona el repositorio).
2.  En tu panel de WordPress, ve a **Plugins > Add New > Subir plugin**.
3.  Sube el archivo ZIP e instálalo.
4.  Activa el plugin "WooCommerce Zoho B2B Manager".
5.  Ve a **Zoho B2B > General Settings** para configurar el plugin.

## Uso

1.  **Configuración General:** Ajusta los modos de registro, roles por defecto, etc.
2.  **Configuración de Precios:** Define el método de precios B2B y los descuentos por rol.
3.  **Integración con Zoho:** Configura las credenciales de la API de Zoho (o habilita el uso de la configuración del plugin principal) y autoriza la conexión. Habilita la sincronización de usuarios.
4.  **Formulario de Solicitud:** Añade el shortcode `[wc_zoho_b2b_application_form]` a una página para permitir que los usuarios envíen solicitudes B2B.
5.  **Lista de Deseos:** Añade el shortcode `[wc_zoho_b2b_wishlist]` a una página para mostrar la lista de deseos del usuario.
6.  **Gestión de Aplicaciones:** Ve a **Zoho B2B > Applications** para aprobar o rechazar nuevas solicitudes.

## Estructura del Plugin

El plugin sigue la estructura estándar de WordPress:

*   `woocommerce-zoho-b2b-manager.php`: Archivo principal.
*   `includes/`: Clases principales (Admin, Frontend, Managers para User, Pricing, Product, Order, Wishlist, Zoho Integration, Compatibility, Installer).
*   `admin/`: Lógica y parciales para el panel de administración.
*   `public/`: Lógica y parciales para el frontend.
*   `assets/`: CSS, JavaScript, imágenes.
*   `templates/`: Plantillas de WooCommerce que pueden ser sobrescritas por temas.
*   `languages/`: Archivos de traducción (.pot, .po, .mo).
*   `docs/`: Documentación detallada.

## Contribuir

¡Las contribuciones son bienvenidas! Si deseas contribuir:

1.  Haz un fork del repositorio.
2.  Crea una nueva rama para tu funcionalidad (`git checkout -b feature/nueva-funcionalidad`).
3.  Realiza tus cambios y haz commit (`git commit -am 'Añade nueva funcionalidad'`).
4.  Haz push a tu rama (`git push origin feature/nueva-funcionalidad`).
5.  Abre un Pull Request.

Por favor, asegúrate de seguir los estándares de codificación de WordPress.

## Licencia

Este plugin está licenciado bajo la GPL-2.0+. Ver el archivo `LICENSE` para más detalles.
```
