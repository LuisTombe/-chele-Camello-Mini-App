<?php
/**
 * Échele Camello - Pestaña Explorar (Microsoft Fluent Design 2)
 */

$iconMap = [
    'plomeria' => 'wrench',
    'electricidad' => 'zap',
    'aires' => 'wind',
    'cerrajeria' => 'key',
    'pintura' => 'paintbrush',
    'construccion' => 'hammer',
    'motos' => 'bike',
    'carpinteria' => 'scissors',
];
?>

<div class="space-y-6">
  <!-- Hero Banner Fluent -->
  <?php require __DIR__ . '/hero.php'; ?>

  <!-- Filtros de Categorías Fluent -->
  <div class="space-y-2.5">
    <div class="flex items-center justify-between">
      <h2 class="text-sm font-bold text-[#242424] flex items-center gap-2">
        <i data-lucide="layers" class="w-4 h-4 text-[#0078D4]"></i>
        <span>Especialidades y Oficios Técnicos</span>
      </h2>
      <span class="text-xs text-[#616161]"><?= count($categories) ?> categorías</span>
    </div>

    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
      <?php foreach ($categories as $cat): ?>
        <?php $icon = $iconMap[$cat['id']] ?? 'wrench'; ?>
        <button
          type="button"
          data-category="<?= htmlspecialchars($cat['id']) ?>"
          onclick="selectCategory('<?= htmlspecialchars($cat['id']) ?>')"
          class="cat-chip-btn flex items-center gap-2 px-3 py-1.5 rounded-md bg-white border border-[rgba(0,0,0,0.08)] text-xs font-semibold text-[#242424] hover:bg-[#F9F9F9] hover:border-[#0078D4]/40 transition shrink-0 cursor-pointer shadow-[0_1px_2px_rgba(0,0,0,0.03)]"
        >
          <div class="w-5 h-5 rounded-[4px] bg-[#EFF6FC] flex items-center justify-center text-[#0078D4]">
            <i data-lucide="<?= $icon ?>" class="w-3.5 h-3.5"></i>
          </div>
          <span><?= htmlspecialchars($cat['name']) ?></span>
          <span class="text-[10px] text-[#8A8A8A] font-normal">(<?= $cat['technician_count'] ?>)</span>
        </button>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Barra de Filtros & Ordenamiento Fluent -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-2 border-t border-[rgba(0,0,0,0.06)]">
    <div class="flex items-center gap-2">
      <h3 class="text-sm sm:text-base font-bold text-[#242424] font-fluent-title">
        Camelladores Disponibles en Neiva
      </h3>
      <span id="workers-count-badge" class="fluent-badge fluent-badge-blue">
        <?= count($workers) ?> técnicos
      </span>
    </div>

    <div class="flex items-center gap-2 self-end sm:self-auto text-xs">
      <span class="text-[#616161] font-medium flex items-center gap-1">
        <i data-lucide="arrow-up-down" class="w-3.5 h-3.5"></i>
        <span>Ordenar por:</span>
      </span>
      <select id="sort-by-select" class="bg-white border border-[#D1D1D1] rounded-md px-2.5 py-1 text-xs font-semibold text-[#242424] focus:outline-none focus:border-b-2 focus:border-b-[#0078D4] cursor-pointer shadow-sm">
        <option value="rating">Mejor Calificación</option>
        <option value="distance">Más Cercanos</option>
        <option value="price">Menor Tarifa Base</option>
        <option value="experience">Mayor Experiencia</option>
      </select>
    </div>
  </div>

  <!-- Grid de Tarjetas de Camelladores Fluent -->
  <div id="workers-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($workers as $w): ?>
      <?php
        $searchTerms = $w['name'] . ' ' . $w['specialty'] . ' ' . $w['neighborhood'] . ' ' . implode(' ', $w['secondary_trades']);
      ?>
      <div
        class="worker-card-item fluent-card p-4 flex flex-col justify-between"
        data-category="<?= htmlspecialchars($w['category_id']) ?>"
        data-neighborhood="<?= htmlspecialchars($w['neighborhood']) ?>"
        data-rating="<?= $w['rating'] ?>"
        data-distance="<?= $w['distance_km'] ?>"
        data-price="<?= $w['min_rate'] ?>"
        data-experience="<?= (int)($w['experience_years'] ?? 0) ?>"
        data-search="<?= htmlspecialchars(strtolower($searchTerms)) ?>"
      >
        <div class="space-y-3">
          <!-- Cabecera de la tarjeta Fluent: Avatar, Info, SENA Badge -->
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-3">
              <div class="relative">
                <img
                  src="<?= htmlspecialchars($w['avatar']) ?>"
                  alt="<?= htmlspecialchars($w['name']) ?>"
                  class="w-12 h-12 rounded-lg object-cover border border-[rgba(0,0,0,0.08)] shadow-sm"
                />
                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full border-2 border-white <?= $w['is_available'] ? 'bg-emerald-500' : 'bg-slate-400' ?>" title="<?= $w['is_available'] ? 'Disponible' : 'Ocupado' ?>"></span>
              </div>
              <div class="space-y-0.5">
                <h4 class="font-bold text-sm text-[#242424] line-clamp-1">
                  <?= htmlspecialchars($w['name']) ?>
                </h4>
                <p class="text-xs font-semibold text-[#0078D4] line-clamp-1">
                  <?= htmlspecialchars($w['specialty']) ?>
                </p>
                <div class="flex items-center gap-1.5 text-[11px] text-[#616161]">
                  <i data-lucide="map-pin" class="w-3 h-3 text-[#616161] shrink-0"></i>
                  <span><?= htmlspecialchars($w['neighborhood']) ?></span>
                  <span class="text-slate-300">•</span>
                  <span><?= $w['distance_km'] ?> km</span>
                </div>
              </div>
            </div>

            <!-- Insignia SENA Fluent -->
            <?php if ($w['sena_certified']): ?>
              <div class="fluent-badge fluent-badge-green shrink-0" title="Acreditado SENA">
                <i data-lucide="award" class="w-3 h-3 text-emerald-700"></i>
                <span>SENA</span>
              </div>
            <?php endif; ?>
          </div>

          <!-- Métricas de Reputación y Trabajos (Fluent Sub-panel) -->
          <div class="grid grid-cols-3 gap-2 bg-[#FAFAFA] p-2 rounded-md text-center border border-[rgba(0,0,0,0.04)]">
            <div>
              <span class="block text-[10px] text-[#616161] font-medium">Calificación</span>
              <span class="text-xs font-bold text-amber-600 flex items-center justify-center gap-0.5">
                <span>★</span> <?= number_format($w['rating'], 1) ?>
              </span>
            </div>
            <div>
              <span class="block text-[10px] text-[#616161] font-medium">Reseñas</span>
              <span class="text-xs font-bold text-[#242424]"><?= $w['total_reviews'] ?></span>
            </div>
            <div>
              <span class="block text-[10px] text-[#616161] font-medium">Camellos</span>
              <span class="text-xs font-bold text-[#107C41]"><?= $w['completed_jobs'] ?></span>
            </div>
          </div>

          <!-- Tags de Oficios Fluent -->
          <div class="flex items-center gap-1.5 flex-wrap">
            <?php foreach (array_slice($w['secondary_trades'], 0, 3) as $trade): ?>
              <span class="fluent-badge fluent-badge-neutral text-[10px]">
                <?= htmlspecialchars($trade) ?>
              </span>
            <?php endforeach; ?>
          </div>

          <!-- Muestra de Portafolio Fluent -->
          <?php if (!empty($w['portfolio'])): ?>
            <div class="flex items-center gap-1.5 pt-0.5">
              <?php foreach (array_slice($w['portfolio'], 0, 2) as $img): ?>
                <img src="<?= htmlspecialchars($img) ?>" class="w-14 h-10 rounded-md object-cover border border-[rgba(0,0,0,0.06)]" />
              <?php endforeach; ?>
              <span class="text-[10px] text-[#616161] font-medium">+<?= count($w['services']) ?> servicios</span>
            </div>
          <?php endif; ?>
        </div>

        <!-- Pie de la tarjeta: Tarifa base y Botones Fluent -->
        <div class="pt-3 mt-3 border-t border-[rgba(0,0,0,0.06)] flex items-center justify-between gap-2">
          <div>
            <span class="block text-[10px] text-[#616161]">Tarifa base</span>
            <span class="text-xs sm:text-sm font-bold text-[#242424]">
              $<?= number_format($w['min_rate'], 0, ',', '.') ?> <span class="text-[10px] font-normal text-[#616161]">COP</span>
            </span>
          </div>

          <div class="flex items-center gap-1.5">
            <button
              type="button"
              onclick="openWorkerProfile('<?= htmlspecialchars($w['id']) ?>')"
              class="fluent-btn text-xs py-1 px-2.5"
            >
              Perfil
            </button>
            <button
              type="button"
              onclick="openRequestModalFor('<?= htmlspecialchars($w['id']) ?>')"
              class="fluent-btn-primary text-xs py-1 px-3"
            >
              Solicitar
            </button>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Mensaje de no encontrados Fluent -->
  <div id="workers-empty-message" class="hidden text-center py-12 bg-white rounded-xl border border-[rgba(0,0,0,0.08)] p-6 space-y-3">
    <div class="w-10 h-10 rounded-full bg-[#EFF6FC] text-[#0078D4] flex items-center justify-center mx-auto">
      <i data-lucide="search-x" class="w-5 h-5"></i>
    </div>
    <h4 class="font-bold text-sm text-[#242424]">No se encontraron camelladores</h4>
    <p class="text-xs text-[#616161] max-w-sm mx-auto">
      Intenta seleccionar "Todos los barrios" o limpiar el filtro de búsqueda.
    </p>
    <button
      type="button"
      onclick="selectNeighborhood('todos'); selectCategory(null); document.getElementById('main-search-input').value = ''; filterWorkers();"
      class="fluent-btn-primary text-xs"
    >
      Restablecer Filtros
    </button>
  </div>
</div>
