<?php
/**
 * Échele Camello - Pestaña Mi Taller / Trabajador (Microsoft Fluent Design 2)
 */

$myWorker = null;
foreach ($workers as $w) {
    if ($w['id'] === 'w-1') {
        $myWorker = $w;
        break;
    }
}
if (!$myWorker && !empty($workers)) {
    $myWorker = $workers[0];
}

$myRequests = [];
$totalEarnings = 0;
foreach ($allRequests as $r) {
    if ($r['worker_id'] === $myWorker['id']) {
        $myRequests[] = $r;
        if ($r['status'] === 'FINALIZADA') {
            $totalEarnings += (int)$r['estimated_cost'];
        }
    }
}
?>

<div class="space-y-4">
  <!-- Perfil de Taller & Switch de Disponibilidad Fluent -->
  <div class="fluent-card-static p-5 flex flex-col md:flex-row md:items-center justify-between gap-5">
    <div class="flex items-center gap-3.5">
      <div class="relative">
        <img
          src="<?= htmlspecialchars($myWorker['avatar']) ?>"
          alt="<?= htmlspecialchars($myWorker['name']) ?>"
          class="w-14 h-14 sm:w-16 sm:h-16 rounded-lg object-cover border border-[rgba(0,0,0,0.12)] shadow-sm"
        />
        <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white <?= $myWorker['is_available'] ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
      </div>

      <div class="space-y-0.5">
        <div class="flex items-center gap-2">
          <h3 class="text-base sm:text-lg font-bold text-[#242424] font-fluent-title"><?= htmlspecialchars($myWorker['name']) ?></h3>
          <?php if ($myWorker['sena_certified']): ?>
            <span class="fluent-badge fluent-badge-green">
              SENA Acreditado
            </span>
          <?php endif; ?>
        </div>
        <p class="text-xs font-semibold text-[#0078D4]"><?= htmlspecialchars($myWorker['specialty']) ?></p>
        <p class="text-xs text-[#616161] flex items-center gap-1">
          <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#616161]"></i>
          <span><?= htmlspecialchars($myWorker['neighborhood']) ?> • <?= htmlspecialchars($myWorker['working_hours']) ?></span>
        </p>
      </div>
    </div>

    <!-- Toggle Disponibilidad Fluent (Windows 11 Switch) -->
    <div class="flex items-center gap-3 bg-[#FAFAFA] p-3 rounded-lg border border-[rgba(0,0,0,0.06)]">
      <div>
        <span class="block text-xs font-semibold text-[#242424]">Disponibilidad Laboral</span>
        <span class="text-[11px] text-[#616161]">
          <?= $myWorker['is_available'] ? 'Visible en Neiva' : 'Pausado temporalmente' ?>
        </span>
      </div>

      <label class="fluent-switch">
        <input
          type="checkbox"
          <?= $myWorker['is_available'] ? 'checked' : '' ?>
          onchange="toggleWorkerAvailability('<?= htmlspecialchars($myWorker['id']) ?>')"
        >
        <span class="fluent-slider"></span>
      </label>
    </div>
  </div>

  <!-- Métricas KPI Fluent -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    <div class="fluent-card-static p-4 space-y-1">
      <span class="text-xs text-[#616161] font-medium">Camellos Realizados</span>
      <h4 class="text-xl font-bold text-[#107C41] font-fluent-title"><?= $myWorker['completed_jobs'] ?></h4>
      <span class="text-[11px] text-[#8A8A8A]">Total acumulado</span>
    </div>

    <div class="fluent-card-static p-4 space-y-1">
      <span class="text-xs text-[#616161] font-medium">Ingresos Estimados</span>
      <h4 class="text-xl font-bold text-[#0078D4] font-fluent-title">
        $<?= number_format($totalEarnings > 0 ? $totalEarnings : 185000, 0, ',', '.') ?>
      </h4>
      <span class="text-[11px] text-[#8A8A8A]">Camellos completados</span>
    </div>

    <div class="fluent-card-static p-4 space-y-1">
      <span class="text-xs text-[#616161] font-medium">Reputación Promedio</span>
      <h4 class="text-xl font-bold text-amber-600 font-fluent-title flex items-center gap-1">
        <span>★</span> <?= number_format($myWorker['rating'], 1) ?>
      </h4>
      <span class="text-[11px] text-[#8A8A8A]"><?= $myWorker['total_reviews'] ?> opiniones</span>
    </div>

    <div class="fluent-card-static p-4 space-y-1">
      <span class="text-xs text-[#616161] font-medium">Servicios en Catálogo</span>
      <h4 class="text-xl font-bold text-[#242424] font-fluent-title"><?= count($myWorker['services']) ?></h4>
      <span class="text-[11px] text-[#8A8A8A]">Tarifas directas</span>
    </div>
  </div>

  <!-- Catálogo de Servicios Fluent -->
  <div class="fluent-card-static p-5 space-y-4">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-sm sm:text-base font-bold text-[#242424] font-fluent-title">Catálogo de Servicios y Tarifas</h3>
        <p class="text-xs text-[#616161]">Configura los precios que visualizan los clientes al contratarte.</p>
      </div>

      <button
        type="button"
        onclick="openModal('modal-add-service')"
        class="fluent-btn-primary text-xs"
      >
        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
        <span>Agregar Servicio</span>
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
      <?php foreach ($myWorker['services'] as $svc): ?>
        <div class="p-3.5 rounded-lg border <?= $svc['is_active'] ? 'border-[rgba(0,0,0,0.08)] bg-white' : 'border-[rgba(0,0,0,0.06)] bg-[#F9F9F9] opacity-60' ?> flex flex-col justify-between space-y-2.5">
          <div class="space-y-1">
            <div class="flex items-center justify-between">
              <span class="fluent-badge <?= $svc['is_active'] ? 'fluent-badge-green' : 'fluent-badge-neutral' ?>">
                <?= $svc['is_active'] ? 'ACTIVO' : 'PAUSADO' ?>
              </span>
              <span class="text-xs font-bold text-[#0078D4]">
                $<?= number_format($svc['estimated_price'], 0, ',', '.') ?> COP
              </span>
            </div>
            <h5 class="font-bold text-xs text-[#242424]"><?= htmlspecialchars($svc['title']) ?></h5>
            <p class="text-[11px] text-[#616161] line-clamp-2"><?= htmlspecialchars($svc['description']) ?></p>
          </div>

          <div class="pt-2 border-t border-[rgba(0,0,0,0.06)] flex items-center justify-between text-[11px]">
            <span class="text-[#8A8A8A] capitalize">Tipo: <?= htmlspecialchars($svc['price_type']) ?></span>
            <button
              type="button"
              onclick="toggleServiceActive('<?= htmlspecialchars($svc['id']) ?>')"
              class="font-semibold <?= $svc['is_active'] ? 'text-rose-600 hover:text-rose-700' : 'text-emerald-600 hover:text-emerald-700' ?> cursor-pointer transition"
            >
              <?= $svc['is_active'] ? 'Pausar' : 'Activar' ?>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Camellos Asignados Fluent -->
  <div class="fluent-card-static p-5 space-y-3">
    <h3 class="text-sm sm:text-base font-bold text-[#242424] font-fluent-title">
      Camellos Asignados a Tu Taller (<?= count($myRequests) ?>)
    </h3>

    <?php if (empty($myRequests)): ?>
      <p class="text-xs text-[#8A8A8A] py-3">No tienes solicitudes pendientes en este momento.</p>
    <?php else: ?>
      <div class="divide-y divide-[rgba(0,0,0,0.06)]">
        <?php foreach ($myRequests as $mr): ?>
          <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="space-y-0.5">
              <div class="flex items-center gap-2">
                <span class="font-bold text-[#242424]"><?= htmlspecialchars($mr['code']) ?></span>
                <span class="text-[#616161]">• <?= htmlspecialchars($mr['service_title']) ?></span>
                <span class="fluent-badge fluent-badge-neutral text-[10px]">
                  <?= htmlspecialchars($mr['status']) ?>
                </span>
              </div>
              <p class="text-[#616161]">
                Cliente: <strong><?= htmlspecialchars($mr['client_name']) ?></strong> (<?= htmlspecialchars($mr['neighborhood']) ?>) — 📅 <?= htmlspecialchars($mr['scheduled_date']) ?> <?= htmlspecialchars($mr['scheduled_time']) ?>
              </p>
            </div>

            <div class="flex items-center gap-2">
              <span class="text-xs font-bold text-[#0078D4]">
                $<?= number_format($mr['estimated_cost'], 0, ',', '.') ?> COP
              </span>
              <button
                type="button"
                onclick="openChatModal('<?= htmlspecialchars($mr['id']) ?>', '<?= htmlspecialchars($mr['worker_name']) ?>', '<?= htmlspecialchars($mr['code']) ?>')"
                class="fluent-btn text-xs py-1"
              >
                Chat
              </button>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
