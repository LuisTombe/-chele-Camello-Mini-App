<?php
/**
 * Échele Camello - Plataforma de Mano de Obra Técnica Calificada
 * Neiva, Huila, Colombia
 * Backend PHP 8.2 + MySQL (XAMPP Puerto 3306)
 */

require_once __DIR__ . '/config/database.php';

$activeDbPort = 3306;
$dbConnected = false;
$dbError = null;

$categories = [];
$workers = [];
$allRequests = [];
$auditLogs = [];

$neighborhoods = [
    'Cándido Leguízamo',
    'Las Granjas',
    'Ipanema',
    'Prado Alto',
    'Centro',
    'Quirinal',
    'Santa Inés',
    'Canaima',
    'Los Andes',
    'Las Palmas',
    'El Altico',
    'Chicalá',
    'Gualanday',
];

try {
    $pdo = Database::getConnection();
    $activeDbPort = Database::getActivePort();
    $dbConnected = true;

    // 1. Obtener Categorías
    $stmtCats = $pdo->query("SELECT * FROM `categories` ORDER BY `name` ASC");
    $categories = $stmtCats->fetchAll();

    // 2. Obtener Trabajadores y sus relaciones
    $stmtWorkers = $pdo->query("SELECT * FROM `workers` ORDER BY `rating` DESC");
    $rawWorkers = $stmtWorkers->fetchAll();

    foreach ($rawWorkers as $w) {
        $w['secondary_trades'] = json_decode($w['secondary_trades'] ?? '[]', true) ?: [];
        $w['portfolio'] = json_decode($w['portfolio'] ?? '[]', true) ?: [];
        $w['sena_certified'] = (bool)$w['sena_certified'];
        $w['is_available'] = (bool)$w['is_available'];

        // Servicios del trabajador
        $stmtSvc = $pdo->prepare("SELECT * FROM `services` WHERE `worker_id` = ? ORDER BY `created_at` DESC");
        $stmtSvc->execute([$w['id']]);
        $w['services'] = $stmtSvc->fetchAll();

        // Reseñas del trabajador
        $stmtRev = $pdo->prepare("SELECT * FROM `reviews` WHERE `worker_id` = ? ORDER BY `created_at` DESC");
        $stmtRev->execute([$w['id']]);
        $w['reviews'] = $stmtRev->fetchAll();

        $workers[] = $w;
    }

    // 3. Obtener Solicitudes de Servicio
    $stmtReqs = $pdo->query("SELECT * FROM `service_requests` ORDER BY `id` DESC");
    $allRequests = $stmtReqs->fetchAll();

    // 4. Obtener Registros de Auditoría
    $stmtAudit = $pdo->query("SELECT * FROM `audit_logs` ORDER BY `id` DESC LIMIT 25");
    $auditLogs = $stmtAudit->fetchAll();

} catch (Exception $e) {
    $dbError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Échele Camello — Mano de Obra Técnica Verificada en Neiva | PHP & MySQL</title>
  <meta name="description" content="Contrata plomeros, electricistas, técnicos de aire y cerrajeros de confianza en Neiva, Huila. Mano de obra verificada por el SENA.">
  <link rel="icon" href="https://fav.farm/🔥">

  <!-- Tailwind CSS CDN con configuración Fluent Design -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['"Segoe UI"', '-apple-system', 'BlinkMacSystemFont', 'Roboto', 'sans-serif'],
          },
          colors: {
            fluent: {
              bg: '#F3F3F3',
              card: '#FFFFFF',
              subtle: '#FAFAFA',
              accent: '#0078D4',
              'accent-hover': '#106EBE',
              'accent-light': '#EFF6FC',
              text: '#242424',
              secondary: '#616161',
              stroke: 'rgba(0, 0, 0, 0.08)',
            },
            brand: {
              orange: '#D96528',
              'orange-hover': '#C25319',
              amber: '#E5A93C',
            }
          },
          borderRadius: {
            'fluent-sm': '4px',
            'fluent-md': '6px',
            'fluent-lg': '10px',
            'fluent-xl': '14px',
          }
        }
      }
    }
  </script>

  <!-- Lucide Icons CDN -->
  <script src="https://unpkg.com/lucide@latest"></script>

  <!-- Fluent Design System CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen bg-[#F3F3F3] text-[#242424] selection:bg-[#0078D4] selection:text-white flex flex-col antialiased">

  <!-- Wrapper para el simulador móvil opcional -->
  <div id="app-main-wrapper" class="flex-1 flex flex-col transition-all duration-300">
    <div id="mobile-notch" class="mobile-notch hidden"></div>

    <!-- Cabecera de Navegación -->
    <?php require __DIR__ . '/includes/header.php'; ?>

    <!-- Alerta en caso de error de conexión con MySQL -->
    <?php if (!$dbConnected): ?>
      <div class="max-w-7xl mx-auto px-4 py-4 w-full">
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3">
          <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5"></i>
          <div>
            <strong class="font-bold block text-sm">Alerta de conexión MySQL (XAMPP):</strong>
            <span><?= htmlspecialchars($dbError) ?></span>
            <div class="mt-2">
              <a href="database/setup.php" class="inline-block px-3 py-1.5 bg-rose-600 text-white font-bold rounded-lg hover:bg-rose-700 transition">
                Ejecutar Instalador de Base de Datos &rarr;
              </a>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Contenido Principal -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8">
      
      <!-- TAB 1: EXPLORAR -->
      <section id="tab-explorar" class="tab-content">
        <?php require __DIR__ . '/includes/tab_explorar.php'; ?>
      </section>

      <!-- TAB 2: SOLICITUDES -->
      <section id="tab-solicitudes" class="tab-content hidden">
        <?php require __DIR__ . '/includes/tab_solicitudes.php'; ?>
      </section>

      <!-- TAB 3: TRABAJADOR / MI TALLER -->
      <section id="tab-trabajador" class="tab-content hidden">
        <?php require __DIR__ . '/includes/tab_trabajador.php'; ?>
      </section>

      <!-- TAB 4: ADMINISTRADOR SENA -->
      <section id="tab-admin" class="tab-content hidden">
        <?php require __DIR__ . '/includes/tab_admin.php'; ?>
      </section>

    </main>

    <!-- Pie de Página Fluent -->
    <footer class="bg-white border-t border-[rgba(0,0,0,0.08)] text-[#616161] text-xs py-6 mt-auto">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2.5">
          <div class="w-6 h-6 rounded-md bg-[#0078D4] flex items-center justify-center text-white shadow-sm">
            <i data-lucide="flame" class="w-3.5 h-3.5"></i>
          </div>
          <span class="text-[#242424] font-semibold">Échele Camello — Neiva, Huila</span>
          <span class="text-slate-300">•</span>
          <span class="fluent-badge fluent-badge-blue">MySQL XAMPP :<?= $activeDbPort ?></span>
        </div>
        <p class="text-[#616161] text-[11px]">
          Microsoft Fluent Design System 2 • Acreditación laboral SENA Regional Huila.
        </p>
      </div>
    </footer>
  </div>

  <!-- Diálogos Modales -->
  <?php require __DIR__ . '/includes/modals.php'; ?>

  <!-- Contenedor Global de Notificaciones Toast -->
  <div id="toast-container" class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none"></div>

  <!-- Lógica JavaScript de la Aplicación -->
  <script src="assets/js/app.js"></script>
</body>
</html>
