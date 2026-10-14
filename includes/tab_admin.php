<?php
/**
 * Échele Camello - Pestaña Panel Administrador SENA & Auditoría (Microsoft Fluent Design 2)
 */

$verifiedCount = 0;
foreach ($workers as $w) {
    if (!empty($w['sena_certified'])) {
        $verifiedCount++;
    }
}
?>

<div class="space-y-4">
  <!-- Cabecera Admin Fluent -->
  <div class="fluent-card-static p-5 sm:p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-l-4 border-l-[#0078D4]">
    <div class="space-y-1">
      <div class="inline-flex items-center gap-1.5 fluent-badge fluent-badge-blue text-xs">
        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#0078D4]"></i>
        <span>Panel ADSO • SENA Regional Huila</span>
      </div>
      <h2 class="text-lg sm:text-xl font-bold text-[#242424] font-fluent-title">
        Supervisión Técnica & Verificación Laboral
      </h2>
      <p class="text-xs text-[#616161]">
        Acreditación de camelladores y trazabilidad de eventos del sistema en MySQL (XAMPP).
      </p>
    </div>

    <div class="flex items-center gap-2">
      <a
        href="database/setup.php"
        target="_blank"
        class="fluent-btn text-xs"
      >
        <i data-lucide="database" class="w-3.5 h-3.5 text-[#0078D4]"></i>
        <span>Estado Base de Datos</span>
      </a>
    </div>
  </div>

  <!-- Métricas Generales Fluent -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="fluent-card-static p-4 space-y-1">
      <span class="text-xs text-[#616161] font-medium">Camelladores Registrados</span>
      <h4 class="text-xl font-bold text-[#242424] font-fluent-title"><?= count($workers) ?></h4>
      <span class="text-[11px] text-[#107C41] font-semibold"><?= $verifiedCount ?> acreditados SENA</span>
    </div>

    <div class="fluent-card-static p-4 space-y-1">
      <span class="text-xs text-[#616161] font-medium">Solicitudes Totales</span>
      <h4 class="text-xl font-bold text-[#0078D4] font-fluent-title"><?= count($allRequests) ?></h4>
      <span class="text-[11px] text-[#8A8A8A]">Registradas en Neiva</span>
    </div>

    <div class="fluent-card-static p-4 space-y-1">
      <span class="text-xs text-[#616161] font-medium">Categorías Activas</span>
      <h4 class="text-xl font-bold text-[#D96528] font-fluent-title"><?= count($categories) ?></h4>
      <span class="text-[11px] text-[#8A8A8A]">Especialidades de oficio</span>
    </div>

    <div class="fluent-card-static p-4 space-y-1">
      <span class="text-xs text-[#616161] font-medium">Eventos de Auditoría</span>
      <h4 class="text-xl font-bold text-[#616161] font-fluent-title"><?= count($auditLogs) ?></h4>
      <span class="text-[11px] text-[#8A8A8A]">En base de datos</span>
    </div>
  </div>

  <!-- Tabla de Acreditación de Técnicos SENA Fluent -->
  <div class="fluent-card-static p-5 space-y-3">
    <div class="flex items-center justify-between">
      <h3 class="text-sm sm:text-base font-bold text-[#242424] font-fluent-title">
        Directorio y Acreditación de Técnicos
      </h3>
      <span class="text-xs text-[#616161]">Control de insignias oficiales</span>
    </div>

    <div class="overflow-x-auto rounded-lg border border-[rgba(0,0,0,0.06)]">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#FAFAFA] text-[#616161] font-semibold border-b border-[rgba(0,0,0,0.06)]">
          <tr>
            <th class="p-3">Técnico</th>
            <th class="p-3">Especialidad</th>
            <th class="p-3">Barrio</th>
            <th class="p-3">Calificación</th>
            <th class="p-3">Insignia SENA</th>
            <th class="p-3 text-right">Acción</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[rgba(0,0,0,0.06)]">
          <?php foreach ($workers as $wk): ?>
            <tr class="hover:bg-[#F9F9F9] transition">
              <td class="p-3 font-semibold text-[#242424] flex items-center gap-2">
                <img src="<?= htmlspecialchars($wk['avatar']) ?>" class="w-7 h-7 rounded-md object-cover shrink-0 border border-[rgba(0,0,0,0.08)]" />
                <span><?= htmlspecialchars($wk['name']) ?></span>
              </td>
              <td class="p-3 text-[#0078D4] font-semibold"><?= htmlspecialchars($wk['specialty']) ?></td>
              <td class="p-3 text-[#616161]"><?= htmlspecialchars($wk['neighborhood']) ?></td>
              <td class="p-3 font-bold text-amber-600">★ <?= number_format($wk['rating'], 1) ?> (<?= $wk['total_reviews'] ?>)</td>
              <td class="p-3">
                <?php if ($wk['sena_certified']): ?>
                  <span class="fluent-badge fluent-badge-green">
                    Acreditado
                  </span>
                <?php else: ?>
                  <span class="fluent-badge fluent-badge-neutral">
                    Pendiente
                  </span>
                <?php endif; ?>
              </td>
              <td class="p-3 text-right">
                <button
                  type="button"
                  onclick="toggleWorkerVerification('<?= htmlspecialchars($wk['id']) ?>')"
                  class="fluent-btn text-xs py-0.5 px-2.5 <?= $wk['sena_certified'] ? 'text-rose-700 hover:bg-rose-50' : 'text-emerald-700 hover:bg-emerald-50' ?>"
                >
                  <?= $wk['sena_certified'] ? 'Revocar' : 'Acreditar' ?>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Registro de Auditoría del Servidor Fluent -->
  <div class="fluent-card-static p-5 space-y-3">
    <div class="flex items-center justify-between">
      <h3 class="text-sm sm:text-base font-bold text-[#242424] font-fluent-title flex items-center gap-2">
        <i data-lucide="shield" class="w-4 h-4 text-[#0078D4]"></i>
        <span>Bitácora de Auditoría en Servidor (MySQL)</span>
      </h3>
      <span class="text-xs text-[#616161]"><?= count($auditLogs) ?> eventos auditados</span>
    </div>

    <div class="overflow-x-auto rounded-lg border border-[rgba(0,0,0,0.06)]">
      <table class="w-full text-left text-xs">
        <thead class="bg-[#FAFAFA] text-[#616161] font-semibold border-b border-[rgba(0,0,0,0.06)]">
          <tr>
            <th class="p-2.5">Fecha y Hora</th>
            <th class="p-2.5">Acción</th>
            <th class="p-2.5">Actor</th>
            <th class="p-2.5">Esquema</th>
            <th class="p-2.5">Detalles</th>
            <th class="p-2.5">Severidad</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-[rgba(0,0,0,0.06)] font-mono text-[11px]">
          <?php foreach ($auditLogs as $log): ?>
            <tr class="hover:bg-[#F9F9F9] transition">
              <td class="p-2.5 text-[#616161] whitespace-nowrap"><?= htmlspecialchars($log['timestamp']) ?></td>
              <td class="p-2.5 font-bold text-[#242424]"><?= htmlspecialchars($log['action']) ?></td>
              <td class="p-2.5 text-[#616161]"><?= htmlspecialchars($log['user']) ?></td>
              <td class="p-2.5 text-[#8A8A8A]"><?= htmlspecialchars($log['schema_name']) ?></td>
              <td class="p-2.5 text-[#242424] font-sans text-xs"><?= htmlspecialchars($log['details']) ?></td>
              <td class="p-2.5">
                <span class="fluent-badge <?= $log['severity'] === 'CRITICAL' ? 'fluent-badge-red' : ($log['severity'] === 'WARN' ? 'fluent-badge-amber' : 'fluent-badge-blue') ?>">
                  <?= htmlspecialchars($log['severity']) ?>
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
