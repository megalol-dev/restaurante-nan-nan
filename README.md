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

## 🏗️ Arquitectura del proyecto

[CAPTURA 1 - Estructura general de carpetas del proyecto]

### Organización

```text
api/
assets/
components/
database/
docs/
pages/
```

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

[DIAGRAMA ER DE LA BASE DE DATOS]

### Relaciones

[EXPLICAR RELACIONES PRINCIPALES ENTRE TABLAS]

---

## 🔐 Sistema de autenticación

El sistema implementa autenticación basada en sesiones PHP.

Características:

* Contraseñas cifradas mediante password_hash().
* Control de acceso por rol.
* Protección de páginas privadas.
* Cierre seguro de sesión.

[CAPTURA LOGIN]

---

## 📅 Sistema de reservas

El módulo de reservas permite:

* Selección de fecha.
* Selección de turno (comida o cena).
* Gestión automática de disponibilidad.
* Asignación de mesas.
* Cancelación de reservas.

[CAPTURA PANEL CLIENTE]
[CAPTURA RESERVA CREADA]

---

## 🍴 Gestión del menú diario

El administrador puede:

* Crear menús diarios.
* Gestionar primeros platos.
* Gestionar segundos platos.
* Gestionar postres.
* Definir bebidas incluidas.

Los clientes pueden consultar el menú actualizado desde la página principal.

[CAPTURA MENÚ DEL DÍA]

---

## ⭐ Sistema de reseñas

Los usuarios registrados pueden publicar reseñas sobre su experiencia.

Características:

* Puntuación de 1 a 5 estrellas.
* Comentarios personalizados.
* Visualización pública.
* Gestión administrativa.

[CAPTURA RESEÑAS]

---

## 🎨 Interfaz de usuario

### Página principal

* Hero dinámico.
* Carrusel de platos.
* Menú del día.
* Reseñas destacadas.

[CAPTURA HOME]

### Responsive

La parte pública de la aplicación está adaptada para dispositivos móviles.

* Home responsive.
* Login responsive.
* Registro responsive.
* Panel cliente responsive.

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
