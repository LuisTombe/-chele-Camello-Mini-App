<?php
/**
 * Échele Camello - Hero Banner (Microsoft Fluent Design 2)
 */
?>
<div class="relative rounded-xl bg-white border border-[rgba(0,0,0,0.08)] p-6 sm:p-8 shadow-[0_2px_8px_rgba(0,0,0,0.04)] overflow-hidden">
  <!-- Sutil acento lateral Fluent (Windows 11 Accent Strip) -->
  <div class="absolute top-0 left-0 w-1.5 h-full bg-[#0078D4]"></div>

  <div class="relative z-10 max-w-3xl space-y-3.5 pl-2">
    <div class="inline-flex items-center gap-1.5 fluent-badge fluent-badge-blue text-xs font-semibold">
      <i data-lucide="shield-check" class="w-3.5 h-3.5 text-[#0078D4]"></i>
      <span>Acreditación Técnica SENA Regional Huila • Neiva</span>
    </div>

    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-[#242424] font-fluent-title">
      Servicios Técnicos y Mano de Obra en <span class="text-[#0078D4]">Neiva</span>
    </h1>

    <p class="text-xs sm:text-sm text-[#616161] leading-relaxed max-w-2xl">
      Encuentra plomeros, electricistas, técnicos de aire y cerrajeros verificados. Conexión directa entre cliente y técnico sin intermediarios abusivos.
    </p>

    <!-- Barra de Búsqueda Fluent -->
    <div class="pt-2">
      <div class="bg-[#F9F9F9] p-1.5 rounded-lg border border-[rgba(0,0,0,0.08)] flex flex-col sm:flex-row items-stretch gap-2 shadow-[0_1px_2px_rgba(0,0,0,0.03)]">
        <div class="flex-1 flex items-center px-3 gap-2 bg-white rounded-md border border-[rgba(0,0,0,0.08)] focus-within:border-b-2 focus-within:border-b-[#0078D4] transition">
          <i data-lucide="search" class="w-4 h-4 text-[#616161]"></i>
          <input
            type="text"
            id="main-search-input"
            placeholder="Buscar por avería o especialidad: Fuga de agua, Minisplit, Corto, Llaves..."
            class="w-full bg-transparent text-[#242424] text-xs sm:text-sm font-normal py-2 focus:outline-none placeholder:text-[#8A8A8A]"
          />
        </div>

        <button
          type="button"
          onclick="filterWorkers()"
          class="fluent-btn-primary shrink-0"
        >
          <i data-lucide="search" class="w-4 h-4"></i>
          <span>Buscar Técnico</span>
        </button>
      </div>
    </div>

    <!-- Filtros Rápidos de Barrios (Fluent Chips) -->
    <div class="flex items-center gap-1.5 flex-wrap pt-1 text-xs text-[#616161]">
      <span class="text-[11px] font-semibold text-[#242424]">Barrios activos:</span>
      <?php 
      $quickNeighborhoods = ['Cándido Leguízamo', 'Las Granjas', 'Ipanema', 'Prado Alto', 'Centro'];
      foreach ($quickNeighborhoods as $b): 
      ?>
        <button
          type="button"
          data-neighborhood="<?= htmlspecialchars($b) ?>"
          onclick="selectNeighborhood('<?= htmlspecialchars($b) ?>')"
          class="quick-neigh-btn text-[11px] px-2.5 py-1 rounded-md transition cursor-pointer bg-[#F3F3F3] hover:bg-[#EBEBEB] text-[#242424] border border-[rgba(0,0,0,0.06)] font-medium"
        >
          <?= htmlspecialchars($b) ?>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</div>
