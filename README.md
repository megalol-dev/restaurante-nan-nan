# 🍽️ Restaurante Ñan Ñan - Sistema de Gestión y Reservas

## 📖 Descripción

Restaurante Ñan Ñan es una aplicación web desarrollada como proyecto completo de desarrollo Full Stack para la gestión integral de un restaurante.

La aplicación permite a los clientes consultar el menú diario, realizar reservas online, publicar reseñas y gestionar sus propias reservas.

Además, incorpora un sistema de administración para la gestión de clientes, empleados, platos, menús diarios y reservas.

---

## 🎯 Objetivos del proyecto

Este proyecto nace con el objetivo de simular una aplicación real utilizada por un restaurante para centralizar toda su operativa diaria.

Entre los objetivos principales destacan:

* Gestión de reservas online.
* Administración de clientes.
* Gestión de empleados.
* Publicación de menús diarios.
* Gestión de carta de platos.
* Sistema de reseñas verificadas.
* Paneles diferenciados según el rol del usuario.
* Diseño responsive para usuarios finales.

---

## 🚀 Funcionalidades principales

### Cliente

* Registro de cuenta.
* Inicio de sesión seguro.
* Reserva de mesas online.
* Cancelación de reservas.
* Consulta de reservas activas.
* Publicación de reseñas.
* Consulta de carta.
* Consulta de menú diario.

### Administrador

* Gestión completa de clientes.
* Gestión completa de empleados.
* Gestión completa de platos.
* Gestión completa de menús diarios.
* Gestión de reservas.
* Moderación de reseñas.
* Configuración de contenidos de la página principal.

### Sistema

* Control de sesiones.
* Gestión de roles.
* Validación cliente y servidor.
* APIs REST internas.
* Arquitectura modular.
* Sistema de mensajes y avisos personalizados.

---

### Organización

## 🏗️ Arquitectura del proyecto

```text
BarApp/
│
├── api/
│   ├── login_api.php
│   ├── reservas_api.php
│   ├── menu_diario_api.php
│   ├── clientes_api.php
│   ├── empleados_api.php
│   ├── resenas_api.php
│   └── ...
│
├── assets/
│   ├── css/
│   ├── js/
│   └── img/
│
├── components/
│   ├── header.php
│   └── footer.php
│
├── database/
│   ├── db.php
│   └── gastroreservas.sql
│
├── docs/
│   ├── capturas/
│   └── diagramas/
│
└── pages/
    ├── index.php
    ├── login.php
    ├── registro.php
    ├── panel_cliente.php
    ├── panel_admin.php
    └── ...
```

### 📂 Organización de carpetas

#### api/

Contiene todos los endpoints de la aplicación.

La lógica de negocio se encuentra separada de la interfaz de usuario mediante APIs PHP que reciben peticiones AJAX desde JavaScript.

Ejemplos:

- Gestión de reservas.
- Gestión de clientes.
- Gestión de empleados.
- Gestión de menús diarios.
- Gestión de reseñas.
- Autenticación.

---

#### assets/

Recursos estáticos de la aplicación.

##### css/

Hojas de estilo globales y responsive.

##### js/

Lógica cliente desarrollada en JavaScript ES6.

Incluye:

- Gestión de reservas.
- Login y registro.
- Carrusel dinámico.
- Menú diario.
- Paneles de administración.

##### img/

Imágenes utilizadas por la aplicación.

- Platos.
- Carrusel.
- Elementos gráficos.
- Recursos de interfaz.

---

#### components/

Componentes reutilizables compartidos por toda la aplicación.

Actualmente incluye:

- Header.
- Footer.

Esta estructura evita duplicar código entre páginas.

---

#### database/

Configuración de acceso a base de datos y scripts relacionados con MariaDB/MySQL.

Incluye:

- Conexión PDO.
- Configuración centralizada.
- Scripts de creación de tablas.

---

#### docs/

Documentación técnica del proyecto.

Aquí se almacenan:

- Capturas de pantalla.
- Diagramas de base de datos.
- Recursos para el README.
- Documentación complementaria.

---

#### pages/

Interfaces visibles por los usuarios.

Incluye tanto las páginas públicas como los paneles privados.

Ejemplos:

- Página principal.
- Login.
- Registro.
- Zona cliente.
- Panel de administración.
- Gestión de reservas.
- Gestión de empleados.
- Gestión de clientes.

### Componentes principales

* API REST interna desarrollada en PHP.
* Frontend HTML + CSS + JavaScript.
* Base de datos MySQL/MariaDB.
* Componentes reutilizables (header/footer).
* Sistema de sesiones PHP.

---

## 🗄️ Base de datos

### Entidades principales

* Clientes
* Empleados
* Reservas
* Mesas
* Menú diario
* Platos
* Reseñas

## 🗄️ Modelo de datos

```text
CLIENTES
   │
   ├── 1:N ── RESERVAS
   │
   └── 1:N ── RESEÑAS

RESERVAS
   │
   └── N:M ── MESAS

MENU_DIARIO
   │
   └── 1:N ── MENU_DIARIO_ITEMS
                 │
                 └── N:1 ── CARTA_PLATOS

TRABAJADORES
   │
   ├── Gestionan reservas
   ├── Gestionan clientes
   ├── Gestionan menús diarios
   ├── Gestionan carta de platos
   ├── Moderan reseñas
   └── Configuran el contenido dinámico de la página principal
```

### 📋 Descripción

La base de datos está organizada en torno a tres áreas principales:

- Gestión de clientes y reservas.
- Gestión de menús y carta del restaurante.
- Gestión administrativa realizada por trabajadores autorizados.

Las relaciones permiten gestionar reservas, asignación de mesas, publicación de reseñas y configuración dinámica de contenidos mostrados en la página principal.

---

## 🔐 Sistema de autenticación

La aplicación implementa un sistema de autenticación basado en sesiones PHP para garantizar el acceso seguro a las zonas privadas.

### Características

* Contraseñas almacenadas de forma segura mediante `password_hash()`.
* Verificación de credenciales mediante `password_verify()`.
* Gestión de sesiones mediante `$_SESSION`.
* Control de acceso basado en roles (cliente y trabajador).
* Protección de páginas privadas frente a accesos no autorizados.
* Cierre seguro de sesión mediante destrucción de la sesión activa.
* Validación de formularios tanto en cliente (JavaScript) como en servidor (PHP).
* Redirección automática según el perfil autenticado:

  * Cliente → Zona Cliente.
  * Trabajador → Panel de Administración.

### Seguridad aplicada

* Validación y saneamiento de datos de entrada.
* Verificación de sesiones en páginas protegidas.
* Restricción de funcionalidades según el rol del usuario.
* Protección frente a contraseñas almacenadas en texto plano.


![Login](assets/img/readme/LOGIN.png)

---

## 📅 Sistema de reservas

El módulo de reservas permite:

* Selección de fecha.
* Selección de turno (comida o cena).
* Gestión automática de disponibilidad.
* Asignación de mesas.
* Cancelación de reservas.

![Reseña](assets/img/readme/2Cliente_resena.png)
![Reserva](assets/img/readme/3Cliente_reserva.png)

---

## 🍴 Gestión del menú diario

El administrador puede:

* Crear menús diarios.
* Gestionar primeros platos.
* Gestionar segundos platos.
* Gestionar postres.
* Definir bebidas incluidas.

Los clientes pueden consultar el menú actualizado desde la página principal o ver toda la carta del restaurante.

![Menu](assets/img/readme/4Menu_dia.png)
![Carta](assets/img/readme/5Carta_restaurante.png)
---

## ⭐ Sistema de reseñas

Los usuarios registrados pueden publicar reseñas sobre su experiencia.

Características:

* Puntuación de 1 a 5 estrellas.
* Comentarios personalizados.
* Visualización pública.
* Gestión administrativa.

[CAPTURA RESEÑAS web]
[CAPTURA RESEÑAS menu]

---

## 🎨 Interfaz de usuario

### Página principal

* Hero dinámico.
* Carrusel de platos.
* Menú del día.
* Reseñas destacadas.

[CAPTURA HOME]

### Responsive

### 📱 Responsive

La parte pública de la aplicación ha sido adaptada para dispositivos móviles, permitiendo consultar información del restaurante, registrarse, iniciar sesión y gestionar reservas desde cualquier dispositivo.

La zona de gestión también incluye adaptación responsive para garantizar su funcionamiento en pantallas pequeñas. No obstante, debido al volumen de información y herramientas administrativas disponibles, se recomienda su uso desde un ordenador para una mejor experiencia de usuario.

#### Funcionalidades adaptadas

* Home responsive.
* Login responsive.
* Registro responsive.
* Panel cliente responsive.
* Gestión de reservas responsive.
* Panel de administración responsive.
* Tablas adaptadas para dispositivos móviles.


[CAPTURAS MÓVIL]

---

## 🛠️ Tecnologías utilizadas

### Backend

* PHP 8
* PDO
* MySQL / MariaDB

### Frontend

* HTML5
* CSS3
* JavaScript ES6

### Herramientas

* XAMPP
* phpMyAdmin
* Git
* GitHub
* Visual Studio Code

---

## 📂 Instalación

### Requisitos

* PHP 8+
* MariaDB o MySQL
* Apache
* XAMPP (recomendado)

### Pasos

1. Clonar repositorio.
2. Importar base de datos.
3. Configurar conexión en:

```text
database/db.php
```

4. Ejecutar servidor Apache y MySQL.
5. Acceder mediante:

```text
http://localhost/BarApp/pages/index.php
```

---

## 🧪 Pruebas realizadas

Se han realizado pruebas manuales sobre:

* Registro de usuarios.
* Inicio de sesión.
* Gestión de reservas.
* Cancelación de reservas.
* Gestión de empleados.
* Gestión de clientes.
* Gestión de platos.
* Gestión de menús.
* Gestión de reseñas.
* Responsive móvil.

---

## 📈 Posibles mejoras futuras

* Notificaciones por correo electrónico.
* Recuperación de contraseña.
* Panel estadístico avanzado.
* Gestión de pedidos online.
* Integración con TPV.
* Dashboard analítico.
* Sistema de promociones.

---

## 👨‍💻 Autor

Desarrollado por:

**Miguel Ángel ("Megalol")**

GitHub:

https://github.com/megalol-dev

---

## 📸 Galería

[CAPTURA HOME]

[CAPTURA CARTA]

[CAPTURA PANEL CLIENTE]

[CAPTURA ADMINISTRACIÓN]

[CAPTURA MENÚ DEL DÍA]

[CAPTURA RESEÑAS]

---

## 📜 Licencia

Proyecto desarrollado con fines educativos y de portfolio.
