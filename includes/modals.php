<?php
/**
 * Échele Camello - Diálogos Modales (Microsoft Fluent Design 2)
 */
?>

<!-- 1. MODAL: PERFIL COMPLETO DE TRABAJADOR (FLUENT DIALOG) -->
<div id="modal-worker-profile" class="fluent-modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
  <div class="fluent-modal-dialog w-full max-w-2xl my-auto max-h-[90vh] flex flex-col">
    <!-- Cabecera de Perfil Fluent -->
    <div class="fluent-modal-header flex items-start justify-between gap-4 shrink-0">
      <div class="flex items-center gap-3.5">
        <img id="profile-avatar" src="" class="w-14 h-14 rounded-lg object-cover border border-[rgba(0,0,0,0.12)] shadow-sm shrink-0">
        <div class="space-y-0.5">
          <div class="flex items-center gap-2 flex-wrap">
            <h3 id="profile-name" class="text-base sm:text-lg font-bold text-[#242424] font-fluent-title"></h3>
            <div id="profile-sena-badge" class="hidden fluent-badge fluent-badge-green">
              <i data-lucide="award" class="w-3 h-3 text-emerald-700"></i>
              <span>SENA</span>
            </div>
          </div>
          <p id="profile-specialty" class="text-xs font-semibold text-[#0078D4]"></p>
          <div class="flex items-center gap-1.5 text-xs text-[#616161]">
            <i data-lucide="map-pin" class="w-3.5 h-3.5 text-[#616161]"></i>
            <span id="profile-neighborhood"></span>
          </div>
        </div>
      </div>

      <button type="button" onclick="closeModal('modal-worker-profile')" class="fluent-btn-subtle p-1.5 rounded-md text-[#616161] hover:text-[#242424]">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <!-- Cuerpo desplazable del modal Fluent -->
    <div class="p-5 sm:p-6 space-y-4 overflow-y-auto flex-1 text-xs sm:text-sm bg-white">
      <!-- Métricas Fluent -->
      <div class="grid grid-cols-3 gap-2 bg-[#FAFAFA] p-2.5 rounded-lg text-center border border-[rgba(0,0,0,0.06)]">
        <div>
          <span class="text-[10px] text-[#616161] block font-medium">Calificación</span>
          <span class="text-xs sm:text-sm font-bold text-amber-600 flex items-center justify-center gap-1">
            <span>★</span> <span id="profile-rating"></span> <span id="profile-reviews-count" class="text-[10px] text-[#8A8A8A]"></span>
          </span>
        </div>
        <div>
          <span class="text-[10px] text-[#616161] block font-medium">Camellos</span>
          <span id="profile-completed-jobs" class="text-xs sm:text-sm font-bold text-[#107C41]"></span>
        </div>
        <div>
          <span class="text-[10px] text-[#616161] block font-medium">Experiencia</span>
          <span id="profile-experience" class="text-xs font-semibold text-[#242424]"></span>
        </div>
      </div>

      <!-- Certificación SENA Detalle -->
      <div id="profile-cert-box" class="p-3 bg-[#EBF8F1] rounded-lg border border-[#BBE7CF] text-[#107C41] text-xs flex items-center gap-2.5">
        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-700 shrink-0"></i>
        <div>
          <strong class="block text-emerald-900">Acreditación Técnica Validada:</strong>
          <span id="profile-cert-title" class="text-[11px] text-emerald-800"></span>
        </div>
      </div>

      <!-- Biografía -->
      <div class="space-y-1">
        <h4 class="font-bold text-[#242424] text-xs uppercase tracking-wider text-[#8A8A8A]">Acerca del Técnico</h4>
        <p id="profile-bio" class="text-[#616161] text-xs leading-relaxed"></p>
      </div>

      <!-- Servicios Ofrecidos -->
      <div class="space-y-2">
        <h4 class="font-bold text-[#242424] text-xs uppercase tracking-wider text-[#8A8A8A]">Servicios y Tarifas Base</h4>
        <div id="profile-services-list" class="space-y-2"></div>
      </div>

      <!-- Muestras de Trabajos -->
      <div class="space-y-2">
        <h4 class="font-bold text-[#242424] text-xs uppercase tracking-wider text-[#8A8A8A]">Muestras de Trabajos en Neiva</h4>
        <div id="profile-portfolio-list" class="flex items-center gap-2 overflow-x-auto pb-1"></div>
      </div>

      <!-- Reseñas -->
      <div class="space-y-2">
        <h4 class="font-bold text-[#242424] text-xs uppercase tracking-wider text-[#8A8A8A]">Opiniones de Clientes</h4>
        <div id="profile-reviews-list" class="space-y-2"></div>
      </div>
    </div>

    <!-- Pie del Modal Fluent -->
    <div class="p-3.5 bg-[#FAFAFA] border-t border-[rgba(0,0,0,0.08)] flex items-center justify-end gap-2 shrink-0">
      <button type="button" onclick="closeModal('modal-worker-profile')" class="fluent-btn text-xs py-1.5 px-3">
        Cerrar
      </button>
    </div>
  </div>
</div>

<!-- 2. MODAL: CREAR SOLICITUD DE SERVICIO (FLUENT DIALOG) -->
<div id="modal-create-request" class="fluent-modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
  <div class="fluent-modal-dialog w-full max-w-lg my-auto max-h-[92vh] flex flex-col">
    <!-- Cabecera Fluent -->
    <div class="fluent-modal-header flex items-center justify-between shrink-0">
      <div class="flex items-center gap-3">
        <img id="req-worker-avatar" src="" class="w-9 h-9 rounded-md object-cover border border-[rgba(0,0,0,0.12)]">
        <div>
          <h3 class="font-bold text-sm sm:text-base text-[#242424] font-fluent-title">Solicitar Servicio Técnico</h3>
          <p class="text-xs text-[#0078D4] font-semibold"><span id="req-worker-name"></span> • <span id="req-worker-specialty"></span></p>
        </div>
      </div>
      <button type="button" onclick="closeModal('modal-create-request')" class="fluent-btn-subtle p-1.5 rounded-md text-[#616161] hover:text-[#242424]">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <form onsubmit="submitServiceRequest(event)" class="p-5 space-y-3.5 overflow-y-auto flex-1 text-xs sm:text-sm bg-white">
      <!-- Selector de Servicio -->
      <div class="space-y-1">
        <label class="block font-semibold text-[#242424]">Servicio a contratar:</label>
        <select id="req-service-select" onchange="updateRequestTotal()" class="fluent-input w-full cursor-pointer"></select>
      </div>

      <!-- Dirección y Barrio -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="block font-semibold text-[#242424]">Dirección del predio:</label>
          <input type="text" id="req-address" required placeholder="Ej: Calle 26 # 1W - 45 Casa 3" class="fluent-input w-full">
        </div>
        <div class="space-y-1">
          <label class="block font-semibold text-[#242424]">Barrio en Neiva:</label>
          <select id="req-neighborhood" class="fluent-input w-full cursor-pointer">
            <?php foreach ($neighborhoods as $b): ?>
              <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Fecha y Hora -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="block font-semibold text-[#242424]">Fecha requerida:</label>
          <input type="date" id="req-date" required value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" class="fluent-input w-full">
        </div>
        <div class="space-y-1">
          <label class="block font-semibold text-[#242424]">Hora sugerida:</label>
          <input type="time" id="req-time" required value="<?= date('H:i') ?>" class="fluent-input w-full">
        </div>
      </div>

      <!-- Descripción -->
      <div class="space-y-1">
        <label class="block font-semibold text-[#242424]">Detalle de la falla o labor:</label>
        <textarea id="req-description" required rows="2" placeholder="Describe brevemente el problema a solucionar..." class="fluent-input w-full"></textarea>
      </div>

      <!-- Check Urgencia Fluent -->
      <div class="flex items-center justify-between p-3 rounded-md bg-[#FFF9E5] border border-[#FEE89D]">
        <div class="flex items-center gap-2">
          <i data-lucide="flame" class="w-4 h-4 text-amber-600"></i>
          <div>
            <span class="block font-semibold text-[#835C00] text-xs">¿Atención de urgencia prioritaria?</span>
            <span class="text-[11px] text-[#835C00]/80">El técnico acude de inmediato (+ $15.000 COP)</span>
          </div>
        </div>
        <input type="checkbox" id="req-urgent-check" onchange="updateRequestTotal()" class="w-4 h-4 text-[#0078D4] rounded border-[#D1D1D1] focus:ring-[#0078D4] cursor-pointer">
      </div>

      <!-- Resumen de Costo Fluent -->
      <div class="p-3 rounded-lg bg-[#EFF6FC] border border-[#C7DCF8]/60 flex items-center justify-between">
        <div>
          <span class="text-xs text-[#0F6CBD] font-semibold block">Total Estimado:</span>
          <span class="text-[11px] text-[#616161]">Pago directo al técnico tras labor</span>
        </div>
        <span id="req-total-price" class="text-base font-bold text-[#0078D4] font-fluent-title">$0 COP</span>
      </div>

      <!-- Botones Fluent -->
      <div class="pt-2 flex items-center justify-end gap-2 border-t border-[rgba(0,0,0,0.06)]">
        <button type="button" onclick="closeModal('modal-create-request')" class="fluent-btn text-xs py-1.5 px-3">
          Cancelar
        </button>
        <button type="submit" class="fluent-btn-primary text-xs py-1.5 px-4">
          <i data-lucide="check" class="w-3.5 h-3.5"></i>
          <span>Enviar Solicitud</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 3. MODAL: CHAT EN TIEMPO REAL (FLUENT DIALOG) -->
<div id="modal-chat" class="fluent-modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 overflow-y-auto">
  <div class="fluent-modal-dialog w-full max-w-lg my-auto h-[82vh] flex flex-col">
    <!-- Cabecera Chat Fluent -->
    <div class="fluent-modal-header flex items-center justify-between shrink-0">
      <div>
        <h3 id="chat-modal-title" class="font-bold text-sm sm:text-base text-[#242424] font-fluent-title"></h3>
        <p id="chat-modal-worker" class="text-xs text-[#0078D4] font-semibold"></p>
      </div>
      <button type="button" onclick="closeModal('modal-chat')" class="fluent-btn-subtle p-1.5 rounded-md text-[#616161] hover:text-[#242424]">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <!-- Mensajes Container Fluent -->
    <div id="chat-messages-container" class="p-4 overflow-y-auto flex-1 space-y-2 bg-[#F9F9F9]"></div>

    <!-- Input Enviar Fluent -->
    <form onsubmit="sendChatMessage(event)" class="p-3 bg-white border-t border-[rgba(0,0,0,0.08)] flex items-center gap-2 shrink-0">
      <input
        type="text"
        id="chat-input-text"
        required
        placeholder="Escribe un mensaje al técnico..."
        class="fluent-input flex-1 py-1.5"
      />
      <button type="submit" class="fluent-btn-primary py-1.5 px-3">
        <i data-lucide="send" class="w-3.5 h-3.5"></i>
      </button>
    </form>
  </div>
</div>

<!-- 4. MODAL: CALIFICAR TRABAJO (FLUENT DIALOG) -->
<div id="modal-review" class="fluent-modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
  <div class="fluent-modal-dialog w-full max-w-md">
    <div class="fluent-modal-header flex items-center justify-between">
      <div>
        <h3 class="font-bold text-sm sm:text-base text-[#242424] font-fluent-title">Calificar Servicio Finalizado</h3>
        <p class="text-xs text-[#616161]">Tu valoración respalda la calidad de la comunidad</p>
      </div>
      <button type="button" onclick="closeModal('modal-review')" class="fluent-btn-subtle p-1.5 rounded-md text-[#616161] hover:text-[#242424]">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <form onsubmit="submitReview(event)" class="p-5 space-y-3.5 text-xs sm:text-sm bg-white">
      <div class="bg-[#FAFAFA] p-2.5 rounded-md border border-[rgba(0,0,0,0.06)]">
        <span class="text-[10px] text-[#8A8A8A] block font-semibold">Técnico evaluado:</span>
        <h4 id="review-worker-name" class="font-bold text-[#242424] text-xs sm:text-sm"></h4>
        <p id="review-service-title" class="text-xs text-[#0078D4] font-semibold"></p>
      </div>

      <!-- Selector de Estrellas Fluent -->
      <div class="text-center space-y-1">
        <label class="block font-semibold text-[#242424]">Calificación general:</label>
        <div id="review-stars-container" class="flex items-center justify-center gap-1.5 py-1">
          <button type="button" data-star="1" onclick="setReviewStars(1)" class="star-btn text-2xl text-amber-500 cursor-pointer">★</button>
          <button type="button" data-star="2" onclick="setReviewStars(2)" class="star-btn text-2xl text-amber-500 cursor-pointer">★</button>
          <button type="button" data-star="3" onclick="setReviewStars(3)" class="star-btn text-2xl text-amber-500 cursor-pointer">★</button>
          <button type="button" data-star="4" onclick="setReviewStars(4)" class="star-btn text-2xl text-amber-500 cursor-pointer">★</button>
          <button type="button" data-star="5" onclick="setReviewStars(5)" class="star-btn text-2xl text-amber-500 cursor-pointer">★</button>
        </div>
      </div>

      <!-- Comentario Fluent -->
      <div class="space-y-1">
        <label class="block font-semibold text-[#242424]">Comentario o reseña:</label>
        <textarea id="review-comment" required rows="2" placeholder="Comenta sobre la puntualidad, calidad técnica y pulcritud..." class="fluent-input w-full"></textarea>
      </div>

      <div class="pt-2 flex items-center justify-end gap-2 border-t border-[rgba(0,0,0,0.06)]">
        <button type="button" onclick="closeModal('modal-review')" class="fluent-btn text-xs py-1.5 px-3">
          Cancelar
        </button>
        <button type="submit" class="fluent-btn-primary text-xs py-1.5 px-4">
          <span>Guardar Calificación</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 5. MODAL: AGREGAR SERVICIO A TALLER (FLUENT DIALOG) -->
<div id="modal-add-service" class="fluent-modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
  <div class="fluent-modal-dialog w-full max-w-md">
    <div class="fluent-modal-header flex items-center justify-between">
      <h3 class="font-bold text-sm sm:text-base text-[#242424] font-fluent-title">Agregar Nuevo Servicio Técnico</h3>
      <button type="button" onclick="closeModal('modal-add-service')" class="fluent-btn-subtle p-1.5 rounded-md text-[#616161] hover:text-[#242424]">
        <i data-lucide="x" class="w-4 h-4"></i>
      </button>
    </div>

    <form onsubmit="submitAddService(event)" class="p-5 space-y-3.5 text-xs sm:text-sm bg-white">
      <div class="space-y-1">
        <label class="block font-semibold text-[#242424]">Título del servicio:</label>
        <input type="text" id="new-svc-title" required placeholder="Ej: Mantenimiento de Calentador a Gas" class="fluent-input w-full">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div class="space-y-1">
          <label class="block font-semibold text-[#242424]">Categoría:</label>
          <select id="new-svc-category" class="fluent-input w-full cursor-pointer">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['id']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="space-y-1">
          <label class="block font-semibold text-[#242424]">Modalidad:</label>
          <select id="new-svc-price-type" class="fluent-input w-full cursor-pointer">
            <option value="desde">Desde</option>
            <option value="fijo">Precio Fijo</option>
            <option value="por_hora">Por Hora</option>
          </select>
        </div>
      </div>

      <div class="space-y-1">
        <label class="block font-semibold text-[#242424]">Precio Estimado (COP):</label>
        <input type="number" id="new-svc-price" required min="10000" step="5000" value="45000" class="fluent-input w-full">
      </div>

      <div class="space-y-1">
        <label class="block font-semibold text-[#242424]">Descripción:</label>
        <textarea id="new-svc-desc" required rows="2" placeholder="Detalla alcances, herramientas y repuestos incluidos..." class="fluent-input w-full"></textarea>
      </div>

      <div class="pt-2 flex items-center justify-end gap-2 border-t border-[rgba(0,0,0,0.06)]">
        <button type="button" onclick="closeModal('modal-add-service')" class="fluent-btn text-xs py-1.5 px-3">
          Cancelar
        </button>
        <button type="submit" class="fluent-btn-primary text-xs py-1.5 px-4">
          <span>Guardar Servicio</span>
        </button>
      </div>
    </form>
  </div>
</div>

<!-- 6. MODAL: BANDEJA DE NOTIFICACIONES (MS-COMUNICACION / OBSERVER) -->
<div id="modal-notifications" class="fluent-modal-overlay hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4">
  <div class="fluent-modal-dialog w-full max-w-md max-h-[80vh] flex flex-col">
    <div class="fluent-modal-header flex items-center justify-between shrink-0">
      <div class="flex items-center gap-2">
        <i data-lucide="bell" class="w-4 h-4 text-[#0078D4]"></i>
        <h3 class="font-bold text-sm sm:text-base text-[#242424] font-fluent-title">Centro de Notificaciones</h3>
      </div>
      <div class="flex items-center gap-2">
        <button type="button" onclick="markAllNotificationsAsRead()" class="text-[11px] text-[#0078D4] hover:underline font-semibold cursor-pointer">
          Marcar leídas
        </button>
        <button type="button" onclick="closeModal('modal-notifications')" class="fluent-btn-subtle p-1.5 rounded-md text-[#616161] hover:text-[#242424]">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
    </div>
    <div id="notifications-list-container" class="p-3 overflow-y-auto flex-1 space-y-2 bg-[#FAFAFA]">
      <p class="text-xs text-[#8A8A8A] text-center py-6">No tienes notificaciones pendientes.</p>
    </div>
  </div>
</div>

