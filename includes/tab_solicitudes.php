<?php
/**
 * Échele Camello - Pestaña Solicitudes (Microsoft Fluent Design 2)
 */

$statusBadges = [
    'PENDIENTE' => ['class' => 'fluent-badge fluent-badge-amber', 'label' => 'Pendiente'],
    'ACEPTADA' => ['class' => 'fluent-badge fluent-badge-blue', 'label' => 'Aceptada'],
    'PROGRAMADA' => ['class' => 'fluent-badge fluent-badge-blue', 'label' => 'Programada'],
    'EN_PROCESO' => ['class' => 'fluent-badge fluent-badge-blue', 'label' => 'En Proceso'],
    'FINALIZADA' => ['class' => 'fluent-badge fluent-badge-green', 'label' => 'Finalizada'],
    'RECHAZADA' => ['class' => 'fluent-badge fluent-badge-red', 'label' => 'Rechazada'],
    'CANCELADA' => ['class' => 'fluent-badge fluent-badge-neutral', 'label' => 'Cancelada'],
];
?>

<div class="space-y-4">
  <!-- Cabecera de Solicitudes Fluent -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-xl border border-[rgba(0,0,0,0.08)] shadow-[0_2px_4px_rgba(0,0,0,0.03)]">
    <div class="space-y-0.5">
      <h2 class="text-base sm:text-lg font-bold text-[#242424] font-fluent-title flex items-center gap-2">
        <i data-lucide="clipboard-list" class="w-5 h-5 text-[#0078D4]"></i>
        <span>Gestor de Solicitudes y Camellos</span>
      </h2>
      <p class="text-xs text-[#616161]">
        Seguimiento en tiempo real y flujo de estado entre clientes y técnicos en Neiva.
      </p>
    </div>

    <div class="flex items-center gap-2">
      <button
        type="button"
        onclick="switchTab('explorar')"
        class="fluent-btn-primary text-xs"
      >
        <i data-lucide="plus" class="w-4 h-4"></i>
        <span>Nuevo Camello</span>
      </button>
    </div>
  </div>

  <!-- Lista de Solicitudes Fluent -->
  <div class="space-y-3">
    <?php if (empty($allRequests)): ?>
      <div class="text-center py-12 bg-white rounded-xl border border-[rgba(0,0,0,0.08)] p-6 space-y-2">
        <div class="w-10 h-10 rounded-full bg-[#F3F3F3] text-[#616161] flex items-center justify-center mx-auto">
          <i data-lucide="inbox" class="w-5 h-5"></i>
        </div>
        <h4 class="font-bold text-sm text-[#242424]">No hay solicitudes registradas</h4>
        <p class="text-xs text-[#616161]">Explora los camelladores y solicita un servicio técnico a domicilio.</p>
      </div>
    <?php else: ?>
      <?php foreach ($allRequests as $req): ?>
        <?php
          $badge = $statusBadges[$req['status']] ?? ['class' => 'fluent-badge fluent-badge-neutral', 'label' => $req['status']];
        ?>
        <div class="fluent-card-static p-4 sm:p-5 space-y-3.5">
          <!-- Fila Superior: Código, Urgencia, Estado -->
          <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[rgba(0,0,0,0.06)] pb-2.5">
            <div class="flex items-center gap-2">
              <span class="font-bold text-sm text-[#242424] font-mono">
                <?= htmlspecialchars($req['code']) ?>
              </span>
              <?php if (!empty($req['urgent'])): ?>
                <span class="fluent-badge fluent-badge-red animate-pulse">
                  <i data-lucide="flame" class="w-3 h-3"></i>
                  <span>URGENTE</span>
                </span>
              <?php endif; ?>
              <span class="text-xs text-[#616161] hidden sm:inline">
                • Registrado: <?= htmlspecialchars($req['created_at']) ?>
              </span>
            </div>

            <!-- Badge de Estado Fluent -->
            <div class="<?= $badge['class'] ?>">
              <?= $badge['label'] ?>
            </div>
          </div>

          <!-- Fila Central: Detalle de Servicio y Participantes -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
            <!-- Técnico Asignado -->
            <div class="flex items-center gap-3 bg-[#FAFAFA] p-2.5 rounded-md border border-[rgba(0,0,0,0.05)]">
              <img
                src="<?= htmlspecialchars($req['worker_avatar']) ?>"
                alt="<?= htmlspecialchars($req['worker_name']) ?>"
                class="w-10 h-10 rounded-md object-cover border border-[rgba(0,0,0,0.08)] shrink-0"
              />
              <div class="space-y-0.5">
                <span class="text-[10px] text-[#8A8A8A] font-semibold block uppercase">Técnico</span>
                <h5 class="font-bold text-[#242424] line-clamp-1"><?= htmlspecialchars($req['worker_name']) ?></h5>
                <p class="text-[11px] text-[#0078D4] font-semibold line-clamp-1"><?= htmlspecialchars($req['worker_specialty']) ?></p>
              </div>
            </div>

            <!-- Cliente Solicitante -->
            <div class="flex items-center gap-3 bg-[#FAFAFA] p-2.5 rounded-md border border-[rgba(0,0,0,0.05)]">
              <img
                src="<?= htmlspecialchars($req['client_avatar']) ?>"
                alt="<?= htmlspecialchars($req['client_name']) ?>"
                class="w-10 h-10 rounded-md object-cover border border-[rgba(0,0,0,0.08)] shrink-0"
              />
              <div class="space-y-0.5">
                <span class="text-[10px] text-[#8A8A8A] font-semibold block uppercase">Cliente</span>
                <h5 class="font-bold text-[#242424] line-clamp-1"><?= htmlspecialchars($req['client_name']) ?></h5>
                <p class="text-[11px] text-[#616161] font-medium"><?= htmlspecialchars($req['client_phone']) ?></p>
              </div>
            </div>

            <!-- Fechas y Costo Estimado -->
            <div class="bg-[#EFF6FC] p-2.5 rounded-md border border-[#C7DCF8]/50 flex flex-col justify-between">
              <div>
                <span class="text-[10px] text-[#0078D4] font-semibold block uppercase">Agenda</span>
                <p class="font-bold text-[#242424] text-xs">
                  📅 <?= htmlspecialchars($req['scheduled_date']) ?> • <?= htmlspecialchars($req['scheduled_time']) ?>
                </p>
              </div>
              <div class="pt-1 flex items-center justify-between">
                <span class="text-xs text-[#616161]">Valor Estimado:</span>
                <span class="font-bold text-[#0078D4] text-sm">
                  $<?= number_format($req['estimated_cost'], 0, ',', '.') ?> COP
                </span>
              </div>
            </div>
          </div>

          <!-- Descripción y Dirección -->
          <div class="bg-[#F9F9F9] p-3 rounded-md text-xs space-y-1 border border-[rgba(0,0,0,0.04)]">
            <div class="flex items-center gap-1.5 text-[#616161]">
              <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#0078D4] shrink-0"></i>
              <span><strong>Ubicación:</strong> <?= htmlspecialchars($req['address']) ?> (Barrio <?= htmlspecialchars($req['neighborhood']) ?>)</span>
            </div>
            <div class="text-[#242424] leading-relaxed">
              <strong>Motivo:</strong> <?= htmlspecialchars($req['description']) ?>
            </div>
          </div>

          <!-- Fila de Acciones Fluent: Botones de Transición y Chat -->
          <div class="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[rgba(0,0,0,0.06)]">
            <div class="flex items-center gap-2">
              <button
                type="button"
                onclick="openChatModal('<?= htmlspecialchars($req['id']) ?>', '<?= htmlspecialchars($req['worker_name']) ?>', '<?= htmlspecialchars($req['code']) ?>')"
                class="fluent-btn text-xs py-1"
              >
                <i data-lucide="message-square" class="w-3.5 h-3.5 text-[#0078D4]"></i>
                <span>Mensajes / Chat</span>
              </button>
            </div>

            <!-- Botones de Transición de Estado Fluent -->
            <div class="flex items-center gap-1.5 flex-wrap">
              <?php if ($req['status'] === 'PENDIENTE'): ?>
                <button
                  type="button"
                  onclick="updateRequestStatus('<?= htmlspecialchars($req['id']) ?>', 'ACEPTADA')"
                  class="fluent-btn-primary text-xs py-1"
                >
                  Aceptar Trabajo
                </button>
                <button
                  type="button"
                  onclick="updateRequestStatus('<?= htmlspecialchars($req['id']) ?>', 'RECHAZADA')"
                  class="fluent-btn text-xs py-1 text-rose-700 hover:bg-rose-50"
                >
                  Rechazar
                </button>
              <?php elseif ($req['status'] === 'ACEPTADA' || $req['status'] === 'PROGRAMADA'): ?>
                <button
                  type="button"
                  onclick="updateRequestStatus('<?= htmlspecialchars($req['id']) ?>', 'EN_PROCESO')"
                  class="fluent-btn-primary text-xs py-1"
                >
                  <i data-lucide="play" class="w-3 h-3"></i>
                  <span>Iniciar Camello</span>
                </button>
              <?php elseif ($req['status'] === 'EN_PROCESO'): ?>
                <button
                  type="button"
                  onclick="updateRequestStatus('<?= htmlspecialchars($req['id']) ?>', 'FINALIZADA')"
                  class="fluent-btn text-xs py-1 bg-emerald-600 hover:bg-emerald-700 text-white border-emerald-600"
                >
                  <i data-lucide="check" class="w-3 h-3"></i>
                  <span>Finalizar Camello</span>
                </button>
              <?php elseif ($req['status'] === 'FINALIZADA'): ?>
                <?php if (empty($req['has_review'])): ?>
                  <button
                    type="button"
                    onclick="openReviewModal('<?= htmlspecialchars($req['id']) ?>', '<?= htmlspecialchars($req['worker_id']) ?>', '<?= htmlspecialchars($req['worker_name']) ?>', '<?= htmlspecialchars($req['service_title']) ?>')"
                    class="fluent-btn text-xs py-1 bg-amber-500 hover:bg-amber-600 text-white border-amber-600"
                  >
                    <span>★ Calificar Técnico</span>
                  </button>
                <?php else: ?>
                  <span class="fluent-badge fluent-badge-green">
                    <i data-lucide="check-circle" class="w-3.5 h-3.5"></i>
                    <span>Calificado</span>
                  </span>
                <?php endif; ?>
              <?php endif; ?>

              <?php if (!in_array($req['status'], ['FINALIZADA', 'CANCELADA', 'RECHAZADA'])): ?>
                <button
                  type="button"
                  onclick="updateRequestStatus('<?= htmlspecialchars($req['id']) ?>', 'CANCELADA')"
                  class="fluent-btn-subtle text-xs py-1 text-[#616161]"
                >
                  Cancelar
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>
