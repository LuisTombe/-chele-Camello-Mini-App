# Plan de Implementación — Échele Camello

**Fecha de inicio:** 7 de octubre de 2026  
**Rama de trabajo:** `feature/implementacion-backlog`  
**Línea base contrastada:** Commit `88820c22621bcf3b9435dedc040800c104936871` vs. Auditoría Técnica  

---

## 1. Diagnóstico Actual vs. Auditoría Técnica

Tras la inspección profunda del código fuente (`src/`, configuración y dependencias), se contrasta el estado del repositorio con los hallazgos de la auditoría:

| Elemento | Estado Real en Código | Diagnóstico vs Auditoría |
|---|---|---|
| **Resolución de Dependencias (C04)** | Conflicto `ERESOLVE` reproducido: `esbuild@^0.25.0` en `devDependencies` choca con `vite@8.3.3` (requiere `^0.27.0 \|\| ^0.28.0`). No existe `package-lock.json`. Node no estaba en PATH por defecto. | Confirmado. Requiere remover/actualizar `esbuild`, fijar `engines`, generar y versionar lockfile. |
| **Persistencia y Arquitectura (C01)** | Todo el estado se mantiene en memoria con `useState` en `src/App.tsx` alimentado por `mockData.ts`. No existe API backend, PostgreSQL, Docker Compose ni migraciones en el repositorio. | Confirmado. Las pantallas representan simulaciones locales; no hay persistencia multiusuario compartida. |
| **Identidad y Roles (C02, C03)** | `Header.tsx` ofrece un selector de roles visual ('CLIENTE', 'TRABAJADOR', 'ADMINISTRADOR'). `RequestsManager.tsx` ignora el rol y retorna todas las solicitudes. `w-1` y `cli-me` están cableados en el código. | Confirmado. Es un selector de pruebas sin autenticación real ni control de acceso por recurso. |
| **Monitoreo y Métricas en Admin (C05, I14)** | `AdminView.tsx` muestra "4 / 4 ONLINE" fijos, esquemas ficticios (`schema_usuarios`, etc.) y `WorkerView.tsx` calcula ingresos con una fórmula inventada (`completedJobs * 55000`). | Confirmado. Debe diferenciarse la telemetría real del modo demo y corregir cálculos. |
| **Filtro de Barrio "Cándido" (I05)** | `App.tsx` (L425) ofrece el chip `'Cándido'`, pero los datos en `mockData.ts` registran `'Cándido Leguízamo'` y el filtrado usa igualdad estricta `===`. | Confirmado error funcional: al presionar 'Cándido' devuelve 0 resultados. |
| **Lógica de Solicitudes y Agenda (I01, I02, I08, I09)** | Fechas en texto sin validación de pasado, urgencia suma $15.000 fijo sin validar fecha actual, alta de servicio asigna precio por defecto silencioso (`parseInt(...) \|\| 40000`). | Confirmado. Requiere validación de negocio robusta y máquina de estados coherente. |
| **Archivos y Certificados (I10)** | "Adjuntar foto" alterna un booleano y pone una URL de Unsplash. Verificación SENA es solo un interruptor visual booleano. | Confirmado. Falta manejo real de subida/almacenamiento de archivos y flujo de verificación. |
| **Chat y Calificaciones (I11, I12)** | Chat almacenado en mapa local en memoria; no valida participantes. Reseñas sin verificación de unicidad ni validación de estado completado en servidor. | Confirmado. Requiere persistencia y validaciones. |
| **Responsive y Accesibilidad (Q01, Q02)** | `DeviceFrame.tsx` utiliza un ancho fijo de 425px con media queries de escritorio que desbordan en resoluciones intermedias. Modales carecen de semántica de accesibilidad y foco. | Confirmado. Requiere mejoras en layout responsivo y accesibilidad ARIA/foco. |

---

## 2. Matriz de Tareas del Backlog (Sección 8)

| ID | Prioridad | Tarea Concreta | Dependencias | Criterio de Aceptación | Estado |
|:--:|:---:|:---|:---|:---|:---:|
| **01** | Crítico | Resolver Vite/esbuild, fijar Node/npm y versionar lockfile. | Ninguna | `npm install` limpio sin `--force`, `package-lock.json` generado, `npm run lint` y `npm run build` exitosos, `npm ci` reproducible. | **En Progreso** |
| **02** | Crítico | Crear `README.md`, corregir roadmap y separar modo demo del modo real. | Tarea 01 | Documentación completa y reproducible; indicador explícito y desacoplado de demostración vs. persistencia real. | Pendiente |
| **03** | Crítico | Definir contratos API (OpenAPI/DTOs), entidades de BD, transiciones de estado y permisos. | Tarea 02 | Especificación formal documentada de endpoints, entidades, reglas de negocio y máquina de estados. | Pendiente |
| **04** | Crítico | Construir infraestructura backend, PostgreSQL, migraciones y entorno Docker Compose con healthchecks. | Tarea 03 | Contenedores y/o backend arrancan limpiamente, aplican migraciones, healthchecks responden 200 y datos persisten entre reinicios. | Pendiente |
| **05** | Crítico | Implementar autenticación JWT, registro, login, logout, caducidad de tokens y control RBAC. | Tarea 04 | Autenticación real de clientes y trabajadores; anónimo recibe 401; endpoints administrativos protegidos con 403; sin selector de roles arbitrario en modo real. | Pendiente |
| **06** | Crítico | Integrar frontend con API y autorización por recurso. | Tarea 05 | Solicitudes y chats aislados por propietario/participante; manipulación de ID ajeno rechazada con 403/404. | Pendiente |
| **07** | Importante | Perfil y catálogo de servicios persistentes; corregir pausa, tarifas y búsqueda de Cándido Leguízamo. | Tarea 06 | CRUD propio por trabajador; servicios pausados no contratables; chip 'Cándido' y selector coinciden y retornan resultados exactos. | Pendiente |
| **08** | Importante | Disponibilidad horaria y solicitudes con datos reales y validaciones de negocio. | Tarea 07 | Rechazo de fechas pasadas, validación de zona horaria `America/Bogota`, montos y trabajadores existentes. | Pendiente |
| **09** | Importante | Máquina de estados: programación, concurrencia, reprogramación, cancelación y cierre. | Tarea 08 | Transiciones atómicas y autorizadas; conflicto (409) ante solapamiento de reservas; historial de cambios con actor y timestamp. | Pendiente |
| **10** | Importante | Mensajería y notificaciones reales entre cliente y trabajador. | Tarea 09 | Intercambio persistente entre sesiones aisladas; acceso denegado a terceros; estado de entrega y lectura. | Pendiente |
| **11** | Importante | Subida real de fotos y auditoría de certificados SENA. | Tarea 07, 09 | Carga multipart con validación de tipo/tamaño MIME, almacenamiento seguro y flujo de revisión administrativa de certificados. | Pendiente |
| **12** | Importante | Reputación íntegra y métricas reales. | Tarea 09 | Solo una reseña por trabajo efectivamente finalizado; promedio calculado desde BD; eliminación de ingresos simulados. | Pendiente |
| **13** | Importante | Panel de administración real, categorías, auditoría de servidor y sanciones/suspensiones. | Tarea 05, 12 | Suspensión de cuentas bloquea operaciones; logs de auditoría generados por el servidor (no inyectados por el cliente). | Pendiente |
| **14** | Importante | Navegación por rutas, accesibilidad (a11y), responsive y corrección del simulador móvil. | Tarea 06, 09 | Accesibilidad con teclado y ARIA; vista responsive a 360/390/768/1280px sin desbordamiento horizontal; rutas compartibles. | Pendiente |
| **15** | Importante | Automatización de pruebas (unitarias, integración, E2E) y pipeline de CI. | Tareas 06 a 14 | Suite de pruebas verdes automatizada; prueba de restauración de base de datos documentada. | Pendiente |
| **16** | Alcance condicional | Evaluar/scaffold cliente móvil verificable (PWA / Android). | Tarea 06 | Consumo de la misma API backend con evidencia de ejecución. | Pendiente |
| **17** | Mejora | Optimización de rendimiento, paginación, carga de imágenes y SEO. | Tareas 07 a 14 | Mediciones objetivas de tiempo de respuesta; etiquetas SEO y optimizaciones de assets. | Pendiente |

---

## 3. Registro de Ejecución y Evidencias

### Tarea 01: Resolución de dependencias y base técnica
- **Estado:** Completada. Lockfile generado, engines fijados.

### Tarea 02: Transición a PHP y Conexión MySQL XAMPP (Puerto 3306)
- **Objetivo:** Migrar el proyecto al lenguaje PHP y conectarlo con MySQL en XAMPP (puerto 3306 prioritario).
- **Acciones ejecutadas:**
  1. Configuración de conexión PDO en `config/database.php` con soporte para puerto 3306 y variables de entorno `.env`.
  2. Creación del esquema relacional DDL en `database/schema.sql` (tablas: `categories`, `workers`, `services`, `reviews`, `service_requests`, `chat_messages`, `audit_logs`).
  3. Creación de datos semilla de Neiva en `database/seed.sql` e instalador interactivo/CLI en `database/setup.php`.
  4. Implementación de endpoints REST en PHP (`api/requests.php`, `api/chat.php`, `api/reviews.php`, `api/workers.php`).
  5. Implementación de vistas modulares en PHP (`includes/header.php`, `includes/hero.php`, `includes/tab_explorar.php`, `includes/tab_solicitudes.php`, `includes/tab_trabajador.php`, `includes/tab_admin.php`, `includes/modals.php`).
  6. Creación de `index.php` con renderizado del lado del servidor y enlace a Apache en `C:\xampp\htdocs\echele-camello`.
  7. Creación de `README.md` con manual completo de uso y puesta en marcha.
- **Resultado:** Conexión exitosa a MySQL, creación de 7 tablas con 48 registros semilla, y respuesta HTTP 200 servida directamente por Apache en `http://localhost/echele-camello/`.
