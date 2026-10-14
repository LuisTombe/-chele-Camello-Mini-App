# 🔥 Échele Camello — Plataforma de Mano de Obra Técnica Calificada

**Ubicación:** Neiva, Huila, Colombia  
**Stack de Tecnología:** PHP 8.2+ • MySQL (XAMPP Puerto 3306) • PDO • Tailwind CSS • Lucide Icons • JavaScript Moderno

---

## 📌 1. Descripción del Proyecto

**Échele Camello** es una plataforma web desarrollada en **PHP y MySQL** diseñada para conectar a los habitantes de Neiva con técnicos, plomeros, electricistas, cerrajeros y mecánicos de confianza de sus barrios (Cándido Leguízamo, Las Granjas, Ipanema, Prado Alto, Centro, Quirinal, etc.), con verificación técnica acreditada por el **SENA Regional Huila**.

---

## ⚙️ 2. Conexión con XAMPP (MySQL Puerto 3306)

El proyecto está configurado para conectarse directamente a MySQL en el puerto estándar **3306**.

### Parámetros de Conexión (`.env` / `config/database.php`):
```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USER=root
DB_PASS=
DB_NAME=echele_camello
APP_URL=http://localhost/echele-camello
```

> **Nota de Resiliencia:** `config/database.php` prioriza el puerto **3306** configurado. Si tu instalación local de XAMPP corre temporalmente en el puerto 3308, el adaptador cuenta con detección inteligente de respaldo para que la plataforma no interrumpa su funcionamiento.

---

## 🚀 3. Puesta en Marcha Rápida con XAMPP

### Paso 1: Iniciar XAMPP
1. Abre el **XAMPP Control Panel**.
2. Inicia el módulo **Apache**.
3. Inicia el módulo **MySQL**.

### Paso 2: Configuración Automática de la Base de Datos
El proyecto incluye un instalador automático que crea la base de datos `echele_camello`, las 7 tablas relacionales y los datos semilla de Neiva.

Ejecútalo desde el navegador abriendo:
👉 **`http://localhost/echele-camello/database/setup.php`**

O desde la terminal:
```bash
php database/setup.php
```

### Paso 3: Abrir la Aplicación
Accede directamente a la aplicación en Apache:
👉 **`http://localhost/echele-camello/`**

*(También puedes ejecutar el servidor embebido de PHP si lo prefieres: `php -S localhost:8000`)*.

---

## 🗄️ 4. Estructura de Base de Datos MySQL

La base de datos `echele_camello` incluye 7 tablas normalizadas:

1. **`categories`**: Catálogo de oficios técnicos (Plomería, Electricidad, Aires Acondicionados, Cerrajería, Pintura, Albañilería, Mecánica de Motos, Carpintería).
2. **`workers`**: Perfiles de técnicos y camelladores con datos de contacto, barrio, geolocalización relativa, acreditación SENA, experiencia y portafolio en JSON.
3. **`services`**: Catálogo de servicios por trabajador con tarifas fijas o base en pesos colombianos (COP).
4. **`reviews`**: Reseñas, calificaciones de 1 a 5 estrellas y comentarios de clientes verificados.
5. **`service_requests`**: Solicitudes de servicio con máquina de estados (`PENDIENTE` ➔ `ACEPTADA` ➔ `PROGRAMADA` ➔ `EN_PROCESO` ➔ `FINALIZADA` / `CANCELADA` / `RECHAZADA`).
6. **`chat_messages`**: Mensajería en tiempo real y notificaciones automáticas de cambio de estado.
7. **`audit_logs`**: Trazabilidad y auditoría de eventos del sistema (creación de servicios, transiciones de estado, acreditaciones SENA).

---

## 🌐 5. Endpoints de la API REST en PHP

La aplicación provee APIs REST asíncronas para operaciones dinámicas:

- **`GET /api/workers.php`**: Listado y búsqueda de camelladores con filtros por categoría y barrio.
- **`GET /api/workers.php?id={workerId}`**: Perfil detallado de un trabajador con sus servicios y reseñas.
- **`POST /api/workers.php?action=toggle_availability`**: Alterna disponibilidad laboral (Disponible / Ocupado).
- **`POST /api/workers.php?action=toggle_service`**: Pausa o activa un servicio técnico en el catálogo.
- **`POST /api/workers.php?action=add_service`**: Agrega un nuevo servicio al catálogo del trabajador.
- **`POST /api/workers.php?action=toggle_verification`**: Acredita o revoca la insignia SENA (Administrador).
- **`GET /api/requests.php`**: Consulta de solicitudes con filtros por estado, cliente o técnico.
- **`POST /api/requests.php`**: Creación de nueva solicitud de servicio con cálculo de urgencia.
- **`POST /api/requests.php?action=status`**: Transición de estado de la solicitud y notificación automática en chat.
- **`GET /api/chat.php?requestId={id}`**: Obtiene el historial de mensajes de una solicitud.
- **`POST /api/chat.php`**: Envío de un nuevo mensaje de chat entre cliente y técnico.
- **`POST /api/reviews.php`**: Registro de calificación (1 a 5 estrellas) y recálculo automático del promedio del trabajador.

---

## 👥 6. Roles y Vistas Disponibles

La cabecera incluye un selector de rol interactivo:

1. **Cliente:**
   - Exploración de técnicos por barrio y especialidad.
   - Búsqueda en vivo y ordenamiento por calificación, cercanía y tarifa.
   - Solicitud de servicio con cotización base y recargo de urgencia inmediata (+ $15.000 COP).
   - Chat interactivo con el técnico.
   - Calificación por estrellas y testimonio al finalizar el trabajo.

2. **Trabajador (Mi Taller):**
   - Panel de control para el técnico (`w-1` Jairo Hernán Trujillo).
   - Interruptor de disponibilidad laboral en Neiva.
   - Gestión de servicios: pausar, activar y registrar nuevos servicios.
   - Contador de ingresos acumulados y camellos completados.

3. **Administrador SENA (ADSO):**
   - Métricas globales de la plataforma.
   - Directorio de validación técnica con botón para acreditar o revocar la insignia oficial del SENA.
   - Visualizador de la bitácora de auditoría del servidor (`audit_logs`).

4. **Simulador Móvil:**
   - Botón en la barra superior para alternar la visualización en marco de smartphone (390-425px) con notch dinámico.
