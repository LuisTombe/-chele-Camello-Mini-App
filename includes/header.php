<?php
/**
 * Échele Camello - Componente de Cabecera y Navegación (Fluent Design System)
 */

$pendingCount = 0;
foreach ($allRequests as $r) {
    if ($r['status'] === 'PENDIENTE') {
        $pendingCount++;
    }
}
?>
<header class="sticky top-0 z-40 bg-white/90 backdrop-blur-md text-[#242424] border-b border-[rgba(0,0,0,0.08)] shadow-[0_1px_3px_rgba(0,0,0,0.05)]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6">
    <div class="flex items-center justify-between h-16 gap-3">
      
      <!-- Logo y Marca Fluent -->
      <div class="flex items-center gap-3 shrink-0 cursor-pointer" onclick="switchTab('explorar')">
        <div class="w-9 h-9 rounded-md bg-[#0078D4] flex items-center justify-center shadow-sm text-white border border-[#005A9E]/30">
          <i data-lucide="flame" class="w-5 h-5 text-white"></i>
        </div>
        <div>
          <div class="flex items-center gap-1.5">
            <span class="font-bold text-base sm:text-lg tracking-tight text-[#242424]">Échele Camello</span>
            <span class="fluent-badge fluent-badge-blue">NEIVA</span>
          </div>
          <p class="text-[11px] text-[#616161] font-medium leading-tight">
            Mano de obra local verificada • SENA
          </p>
        </div>
      </div>

      <!-- Selector de Barrio en Neiva (Fluent Control) -->
      <div class="hidden md:flex items-center gap-2 bg-[#F3F3F3] hover:bg-[#EBEBEB] px-3 py-1.5 rounded-md border border-[rgba(0,0,0,0.08)] transition text-xs">
        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#0078D4] shrink-0"></i>
        <span class="text-[#616161] font-medium">Barrio:</span>
        <select id="neighborhood-selector" class="bg-transparent text-[#242424] font-semibold focus:outline-none cursor-pointer text-xs">
          <option value="todos">Todos los Barrios</option>
          <?php foreach ($neighborhoods as $b): ?>
            <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Indicador MySQL XAMPP (Fluent Pill) -->
      <div class="hidden lg:flex items-center gap-1.5 fluent-badge fluent-badge-green">
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span>MySQL XAMPP :<?= $activeDbPort ?></span>
      </div>

      <!-- Controles de Rol y Simulador (Fluent Command Bar) -->
      <div class="flex items-center gap-2">
        
        <!-- Toggle Simulador Móvil -->
        <button type="button" id="btn-toggle-device" onclick="toggleDeviceFrame()" title="Simulador Móvil" class="fluent-btn-subtle text-xs flex items-center gap-1 cursor-pointer">
          <i data-lucide="smartphone" class="w-4 h-4 text-[#616161]"></i>
          <span class="hidden sm:inline">Móvil</span>
        </button>

        <!-- Selector de Rol Segmentado Fluent -->
        <div class="flex items-center bg-[#F3F3F3] p-0.5 rounded-md border border-[rgba(0,0,0,0.08)] text-xs">
          <button type="button" id="role-btn-cliente" onclick="switchRole('CLIENTE')" class="px-2.5 py-1 rounded-[4px] font-semibold transition cursor-pointer bg-white text-[#242424] shadow-sm" title="Vista Cliente">
            Cliente
          </button>
          <button type="button" id="role-btn-trabajador" onclick="switchRole('TRABAJADOR')" class="px-2.5 py-1 rounded-[4px] font-semibold transition cursor-pointer text-[#616161] hover:text-[#242424]" title="Vista Trabajador">
            Trabajador
          </button>
          <button type="button" id="role-btn-admin" onclick="switchRole('ADMINISTRADOR')" class="px-2.5 py-1 rounded-[4px] font-semibold transition cursor-pointer text-[#616161] hover:text-[#242424]" title="Panel Administrador">
            Admin
          </button>
        </div>

      </div>

    </div>

    <!-- Barra de Pestañas de Navegación Fluent Pivot -->
    <div class="flex items-center justify-between border-t border-[rgba(0,0,0,0.06)] overflow-x-auto gap-2">
      <div class="flex items-center gap-1">
        <button type="button" onclick="switchTab('explorar')" data-tab="explorar" class="nav-tab-btn fluent-tab-item active">
          <i data-lucide="compass" class="w-4 h-4"></i>
          <span>Explorar</span>
        </button>
        <button type="button" onclick="switchTab('solicitudes')" data-tab="solicitudes" class="nav-tab-btn fluent-tab-item">
          <i data-lucide="clipboard-list" class="w-4 h-4"></i>
          <span>Solicitudes</span>
          <?php if ($pendingCount > 0): ?>
            <span class="fluent-badge fluent-badge-amber text-[10px] ml-1"><?= $pendingCount ?></span>
          <?php endif; ?>
        </button>
        <button type="button" id="tab-btn-trabajador" onclick="switchTab('trabajador')" data-tab="trabajador" class="nav-tab-btn fluent-tab-item hidden">
          <i data-lucide="briefcase" class="w-4 h-4"></i>
          <span>Mi Taller</span>
        </button>
        <button type="button" id="tab-btn-admin" onclick="switchTab('admin')" data-tab="admin" class="nav-tab-btn fluent-tab-item hidden">
          <i data-lucide="shield-check" class="w-4 h-4"></i>
          <span>Admin SENA</span>
        </button>
      </div>

      <div class="text-[11px] text-[#616161] font-medium shrink-0 flex items-center gap-1.5 py-2">
        <span class="w-1.5 h-1.5 rounded-full bg-[#0078D4]"></span>
        <span>Rol actual: <strong class="role-indicator text-[#0078D4] font-semibold">CLIENTE</strong></span>
      </div>
    </div>

  </div>
</header>
