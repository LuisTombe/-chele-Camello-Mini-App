/**
 * Échele Camello - Lógica de Interfaz y Conexión Asíncrona con PHP & MySQL
 */

// Estado Global de la App
const state = {
  currentRole: 'CLIENTE',
  activeTab: 'explorar',
  selectedCategory: null,
  selectedNeighborhood: 'todos',
  searchQuery: '',
  sortBy: 'rating',
  isMobileDevice: false,
  selectedWorkerForProfile: null,
  selectedWorkerForRequest: null,
  selectedServiceForRequest: null,
  selectedRequestForChat: null,
  selectedRequestForReview: null,
  reviewRating: 5,
};

// Notificación Toast Global (Fluent Toast Style)
function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = 'bg-white text-[#242424] px-4 py-3 rounded-lg shadow-[0_8px_24px_rgba(0,0,0,0.14)] border border-[rgba(0,0,0,0.08)] flex items-center gap-2.5 text-xs font-semibold transition-all transform translate-y-3 opacity-0';
  toast.innerHTML = `
    <i data-lucide="check-circle-2" class="w-4 h-4 text-[#107C41] shrink-0"></i>
    <span>${message}</span>
  `;

  container.appendChild(toast);
  lucide.createIcons();

  setTimeout(() => {
    toast.classList.remove('translate-y-3', 'opacity-0');
  }, 10);

  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-3');
    setTimeout(() => toast.remove(), 250);
  }, 3200);
}

// Selector de Pestañas Fluent Pivot
function switchTab(tabName) {
  state.activeTab = tabName;
  document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
  const target = document.getElementById(`tab-${tabName}`);
  if (target) target.classList.remove('hidden');

  // Actualizar botones de navegación Pivot
  document.querySelectorAll('.nav-tab-btn').forEach(btn => {
    const isCurrent = btn.dataset.tab === tabName;
    if (isCurrent) {
      btn.className = 'nav-tab-btn fluent-tab-item active';
    } else {
      btn.className = 'nav-tab-btn fluent-tab-item';
    }
  });

  // Re-render iconos
  lucide.createIcons();
}

// Selector de Rol Fluent
function switchRole(role) {
  state.currentRole = role;
  document.querySelectorAll('.role-indicator').forEach(el => {
    el.textContent = role;
  });

  ['cliente', 'trabajador', 'admin'].forEach(r => {
    const btn = document.getElementById(`role-btn-${r}`);
    if (!btn) return;
    if (r.toUpperCase() === role) {
      btn.className = 'px-2.5 py-1 rounded-[4px] font-semibold transition cursor-pointer bg-white text-[#242424] shadow-sm';
    } else {
      btn.className = 'px-2.5 py-1 rounded-[4px] font-semibold transition cursor-pointer text-[#616161] hover:text-[#242424]';
    }
  });

  // Mostrar pestañas según rol
  const workerTabBtn = document.getElementById('tab-btn-trabajador');
  const adminTabBtn = document.getElementById('tab-btn-admin');

  if (workerTabBtn) {
    if (role === 'TRABAJADOR' || role === 'ADMINISTRADOR') {
      workerTabBtn.classList.remove('hidden');
    } else {
      workerTabBtn.classList.add('hidden');
    }
  }

  if (adminTabBtn) {
    if (role === 'ADMINISTRADOR') {
      adminTabBtn.classList.remove('hidden');
    } else {
      adminTabBtn.classList.add('hidden');
    }
  }

  showToast(`Modo cambiado a: ${role}`);
}

// Toggle Simulador Móvil Fluent
function toggleDeviceFrame() {
  state.isMobileDevice = !state.isMobileDevice;
  const mainWrapper = document.getElementById('app-main-wrapper');
  const notch = document.getElementById('mobile-notch');
  const toggleBtn = document.getElementById('btn-toggle-device');

  if (state.isMobileDevice) {
    mainWrapper.classList.add('mobile-frame-container');
    if (notch) notch.classList.remove('hidden');
    if (toggleBtn) toggleBtn.classList.add('bg-[#0078D4]', 'text-white');
  } else {
    mainWrapper.classList.remove('mobile-frame-container');
    if (notch) notch.classList.add('hidden');
    if (toggleBtn) toggleBtn.classList.remove('bg-[#0078D4]', 'text-white');
  }
}

// Filtrado de Trabajadores
function filterWorkers() {
  const query = state.searchQuery.toLowerCase();
  const cat = state.selectedCategory;
  const neigh = state.selectedNeighborhood;

  const cards = document.querySelectorAll('.worker-card-item');
  let visibleCount = 0;

  cards.forEach(card => {
    const cardCat = card.dataset.category;
    const cardNeigh = card.dataset.neighborhood;
    const cardSearch = (card.dataset.search || '').toLowerCase();

    const matchesCat = !cat || cardCat === cat;
    const matchesNeigh = neigh === 'todos' || cardNeigh === neigh;
    const matchesQuery = !query || cardSearch.includes(query);

    if (matchesCat && matchesNeigh && matchesQuery) {
      card.classList.remove('hidden');
      visibleCount++;
    } else {
      card.classList.add('hidden');
    }
  });

  const emptyMsg = document.getElementById('workers-empty-message');
  if (emptyMsg) {
    if (visibleCount === 0) {
      emptyMsg.classList.remove('hidden');
    } else {
      emptyMsg.classList.add('hidden');
    }
  }

  const countBadge = document.getElementById('workers-count-badge');
  if (countBadge) {
    countBadge.textContent = `${visibleCount} técnicos`;
  }
}

// Selección de Categoría Fluent
function selectCategory(catId) {
  if (state.selectedCategory === catId) {
    state.selectedCategory = null;
  } else {
    state.selectedCategory = catId;
  }

  document.querySelectorAll('.cat-chip-btn').forEach(btn => {
    const isActive = btn.dataset.category === state.selectedCategory;
    if (isActive) {
      btn.className = 'cat-chip-btn flex items-center gap-2 px-3 py-1.5 rounded-md bg-[#EFF6FC] border border-[#0078D4] text-xs font-semibold text-[#0078D4] transition shrink-0 cursor-pointer shadow-sm';
    } else {
      btn.className = 'cat-chip-btn flex items-center gap-2 px-3 py-1.5 rounded-md bg-white border border-[rgba(0,0,0,0.08)] text-xs font-semibold text-[#242424] hover:bg-[#F9F9F9] hover:border-[#0078D4]/40 transition shrink-0 cursor-pointer shadow-[0_1px_2px_rgba(0,0,0,0.03)]';
    }
  });

  filterWorkers();
}

// Selección de Barrio Fluent
function selectNeighborhood(neighborhood) {
  state.selectedNeighborhood = neighborhood;
  const selectElem = document.getElementById('neighborhood-selector');
  if (selectElem) selectElem.value = neighborhood;

  // Actualizar botones de barrios rápidos Fluent
  document.querySelectorAll('.quick-neigh-btn').forEach(btn => {
    const isCurrent = btn.dataset.neighborhood === neighborhood;
    if (isCurrent) {
      btn.className = 'quick-neigh-btn text-[11px] px-2.5 py-1 rounded-md transition cursor-pointer bg-[#0078D4] text-white border border-[#0078D4] font-semibold shadow-sm';
    } else {
      btn.className = 'quick-neigh-btn text-[11px] px-2.5 py-1 rounded-md transition cursor-pointer bg-[#F3F3F3] hover:bg-[#EBEBEB] text-[#242424] border border-[rgba(0,0,0,0.06)] font-medium';
    }
  });

  filterWorkers();
}

// Modales
function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) {
    modal.classList.add('hidden');
    document.body.style.overflow = '';
  }
}

// Abrir Perfil de Trabajador
async function openWorkerProfile(workerId) {
  try {
    const res = await fetch(`api/workers.php?id=${encodeURIComponent(workerId)}`);
    const data = await res.json();
    if (!data.success || !data.data) {
      showToast('No se pudo cargar el perfil', 'error');
      return;
    }

    const w = data.data;
    state.selectedWorkerForProfile = w;

    // Llenar contenido modal
    document.getElementById('profile-avatar').src = w.avatar;
    document.getElementById('profile-name').textContent = w.name;
    document.getElementById('profile-specialty').textContent = w.specialty;
    document.getElementById('profile-neighborhood').textContent = `${w.neighborhood} (${w.distance_km} km)`;
    document.getElementById('profile-rating').textContent = Number(w.rating).toFixed(1);
    document.getElementById('profile-reviews-count').textContent = `(${w.total_reviews} reseñas)`;
    document.getElementById('profile-completed-jobs').textContent = `${w.completed_jobs} camellos`;
    document.getElementById('profile-bio').textContent = w.bio || 'Sin descripción';
    document.getElementById('profile-experience').textContent = `${w.experience_years} años de experiencia técnica`;

    // SENA Badge
    const senaBadge = document.getElementById('profile-sena-badge');
    if (w.sena_certified) {
      senaBadge.classList.remove('hidden');
      document.getElementById('profile-cert-title').textContent = w.certificate_title || 'Técnico Certificado SENA';
    } else {
      senaBadge.classList.add('hidden');
    }

    // Servicios Fluent
    const servicesContainer = document.getElementById('profile-services-list');
    servicesContainer.innerHTML = '';
    (w.services || []).forEach(s => {
      const sCard = document.createElement('div');
      sCard.className = 'p-3 rounded-lg border border-[rgba(0,0,0,0.08)] bg-white hover:border-[#0078D4]/50 transition flex items-center justify-between gap-3 shadow-[0_1px_2px_rgba(0,0,0,0.03)]';
      sCard.innerHTML = `
        <div class="space-y-0.5">
          <h4 class="font-bold text-xs sm:text-sm text-[#242424]">${s.title}</h4>
          <p class="text-[11px] text-[#616161] line-clamp-1">${s.description}</p>
          <span class="inline-block text-[11px] font-bold text-[#0078D4]">$${Number(s.estimated_price).toLocaleString('es-CO')} COP (${s.price_type})</span>
        </div>
        <button type="button" onclick="openRequestModalFor('${w.id}', '${s.id}')" class="fluent-btn-primary text-xs py-1 px-3 shrink-0">
          Solicitar
        </button>
      `;
      servicesContainer.appendChild(sCard);
    });

    // Portfolio Fluent
    const portfolioContainer = document.getElementById('profile-portfolio-list');
    portfolioContainer.innerHTML = '';
    (w.portfolio || []).forEach(imgUrl => {
      const img = document.createElement('img');
      img.src = imgUrl;
      img.className = 'w-20 h-20 sm:w-24 sm:h-24 rounded-md object-cover border border-[rgba(0,0,0,0.08)] shadow-sm';
      portfolioContainer.appendChild(img);
    });

    // Reseñas Fluent
    const reviewsContainer = document.getElementById('profile-reviews-list');
    reviewsContainer.innerHTML = '';
    (w.reviews || []).forEach(r => {
      const rCard = document.createElement('div');
      rCard.className = 'p-2.5 rounded-md bg-[#FAFAFA] border border-[rgba(0,0,0,0.04)] text-xs space-y-1';
      rCard.innerHTML = `
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <img src="${r.client_avatar || 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80'}" class="w-5 h-5 rounded-full object-cover">
            <span class="font-semibold text-[#242424]">${r.client_name}</span>
          </div>
          <div class="flex items-center text-amber-600 font-bold text-[11px]">
            <span>★ ${r.rating}</span>
          </div>
        </div>
        <p class="text-[#616161] text-[11px] italic">"${r.comment}"</p>
        <span class="text-[10px] text-[#8A8A8A] block">${r.review_date} • ${r.service_title}</span>
      `;
      reviewsContainer.appendChild(rCard);
    });

    openModal('modal-worker-profile');
    lucide.createIcons();
  } catch (err) {
    console.error(err);
    showToast('Error de conexión', 'error');
  }
}

// Abrir Modal de Solicitud de Servicio
async function openRequestModalFor(workerId, serviceId = null) {
  closeModal('modal-worker-profile');

  try {
    const res = await fetch(`api/workers.php?id=${encodeURIComponent(workerId)}`);
    const data = await res.json();
    if (!data.success || !data.data) return;

    const w = data.data;
    state.selectedWorkerForRequest = w;

    document.getElementById('req-worker-avatar').src = w.avatar;
    document.getElementById('req-worker-name').textContent = w.name;
    document.getElementById('req-worker-specialty').textContent = w.specialty;

    // Llenar selector de servicios
    const svcSelect = document.getElementById('req-service-select');
    svcSelect.innerHTML = '';
    (w.services || []).forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id;
      opt.dataset.price = s.estimated_price;
      opt.dataset.title = s.title;
      opt.dataset.category = s.category_id;
      opt.textContent = `${s.title} — $${Number(s.estimated_price).toLocaleString('es-CO')} COP`;
      if (serviceId && s.id === serviceId) {
        opt.selected = true;
      }
      svcSelect.appendChild(opt);
    });

    updateRequestTotal();
    openModal('modal-create-request');
  } catch (e) {
    console.error(e);
  }
}

// Calcular Total de Solicitud
function updateRequestTotal() {
  const svcSelect = document.getElementById('req-service-select');
  const urgentCheckbox = document.getElementById('req-urgent-check');
  const totalElem = document.getElementById('req-total-price');

  if (!svcSelect || !totalElem) return;

  const selectedOpt = svcSelect.options[svcSelect.selectedIndex];
  let basePrice = selectedOpt ? parseInt(selectedOpt.dataset.price || '40000') : 40000;
  let isUrgent = urgentCheckbox ? urgentCheckbox.checked : false;

  let total = basePrice + (isUrgent ? 15000 : 0);
  totalElem.textContent = `$${total.toLocaleString('es-CO')} COP`;
}

// Enviar Solicitud de Servicio a MySQL
async function submitServiceRequest(event) {
  event.preventDefault();
  const w = state.selectedWorkerForRequest;
  if (!w) return;

  const svcSelect = document.getElementById('req-service-select');
  const selectedOpt = svcSelect.options[svcSelect.selectedIndex];
  const serviceTitle = selectedOpt ? selectedOpt.dataset.title : 'Servicio Técnico';
  const category = selectedOpt ? selectedOpt.dataset.category : w.category_id;
  const basePrice = selectedOpt ? parseInt(selectedOpt.dataset.price || '40000') : 40000;
  const isUrgent = document.getElementById('req-urgent-check').checked;
  const estimatedCost = basePrice + (isUrgent ? 15000 : 0);

  const payload = {
    workerId: w.id,
    workerName: w.name,
    workerSpecialty: w.specialty,
    workerAvatar: w.avatar,
    serviceTitle: serviceTitle,
    category: category,
    address: document.getElementById('req-address').value,
    neighborhood: document.getElementById('req-neighborhood').value,
    scheduledDate: document.getElementById('req-date').value,
    scheduledTime: document.getElementById('req-time').value,
    description: document.getElementById('req-description').value,
    urgent: isUrgent ? 1 : 0,
    estimatedCost: estimatedCost,
    clientId: 'cli-me',
    clientName: 'Tú (Cliente)',
    clientPhone: '+57 312 000 0000',
  };

  try {
    const res = await fetch('api/requests.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const result = await res.json();

    if (result.success) {
      showToast(result.message || '¡Solicitud enviada!');
      closeModal('modal-create-request');
      // Recargar solicitudes y cambiar a tab de solicitudes
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    } else {
      showToast(result.error || 'Error al guardar solicitud', 'error');
    }
  } catch (err) {
    console.error(err);
    showToast('Error de conexión con el servidor', 'error');
  }
}

// Actualizar Estado de Solicitud (Aceptar, Iniciar, Finalizar, Cancelar, etc.)
async function updateRequestStatus(requestId, newStatus) {
  try {
    const res = await fetch('api/requests.php?action=status', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        requestId: requestId,
        status: newStatus,
        role: state.currentRole
      })
    });
    const result = await res.json();
    if (result.success) {
      showToast(`Estado actualizado: ${newStatus}`);
      setTimeout(() => {
        window.location.reload();
      }, 800);
    } else {
      showToast(result.error || 'Error al cambiar estado', 'error');
    }
  } catch (e) {
    console.error(e);
    showToast('Error de conexión', 'error');
  }
}

// Abrir Chat en Tiempo Real
async function openChatModal(requestId, workerName, code) {
  state.selectedRequestForChat = requestId;
  document.getElementById('chat-modal-title').textContent = `Chat: ${code}`;
  document.getElementById('chat-modal-worker').textContent = workerName;

  loadChatMessages(requestId);
  openModal('modal-chat');
}

// Cargar Mensajes de Chat desde MySQL
async function loadChatMessages(requestId) {
  const container = document.getElementById('chat-messages-container');
  container.innerHTML = '<div class="text-center py-8 text-xs text-slate-400">Cargando mensajes...</div>';

  try {
    const res = await fetch(`api/chat.php?requestId=${encodeURIComponent(requestId)}`);
    const result = await res.json();
    if (!result.success) return;

    container.innerHTML = '';
    (result.data || []).forEach(msg => {
      const isMe = (state.currentRole === 'CLIENTE' && msg.sender_role === 'CLIENTE') ||
                   (state.currentRole === 'TRABAJADOR' && msg.sender_role === 'TRABAJADOR');
      const isSystem = msg.sender_id === 'sistema';

      const msgDiv = document.createElement('div');
      if (isSystem) {
        msgDiv.className = 'p-2 rounded-md bg-[#FFF9E5] border border-[#FEE89D] text-[#835C00] text-xs text-center font-medium my-1.5';
        msgDiv.textContent = msg.text;
      } else {
        msgDiv.className = `flex flex-col ${isMe ? 'items-end' : 'items-start'} my-1.5`;
        msgDiv.innerHTML = `
          <div class="max-w-[80%] rounded-lg p-2.5 text-xs sm:text-sm ${
            isMe ? 'bg-[#0078D4] text-white rounded-tr-none shadow-sm' : 'bg-white text-[#242424] rounded-tl-none border border-[rgba(0,0,0,0.08)] shadow-sm'
          }">
            <span class="block text-[10px] font-semibold ${isMe ? 'text-blue-100' : 'text-[#616161]'} mb-0.5">${msg.sender_name}</span>
            <p class="leading-relaxed">${msg.text}</p>
            <span class="block text-[9px] text-right mt-1 ${isMe ? 'text-blue-200' : 'text-[#8A8A8A]'}">${msg.timestamp}</span>
          </div>
        `;
      }
      container.appendChild(msgDiv);
    });

    container.scrollTop = container.scrollHeight;
  } catch (e) {
    console.error(e);
  }
}

// Enviar Mensaje de Chat a MySQL
async function sendChatMessage(event) {
  event.preventDefault();
  const input = document.getElementById('chat-input-text');
  const text = input.value.trim();
  const requestId = state.selectedRequestForChat;

  if (!text || !requestId) return;

  try {
    const res = await fetch('api/chat.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        requestId: requestId,
        text: text,
        role: state.currentRole,
        senderName: state.currentRole === 'CLIENTE' ? 'Tú (Cliente)' : 'Jairo Hernán Trujillo (Técnico)'
      })
    });
    const result = await res.json();
    if (result.success) {
      input.value = '';
      loadChatMessages(requestId);
    }
  } catch (e) {
    console.error(e);
    showToast('Error al enviar mensaje', 'error');
  }
}

// Abrir Modal de Calificación
function openReviewModal(requestId, workerId, workerName, serviceTitle) {
  state.selectedRequestForReview = { requestId, workerId, workerName, serviceTitle };
  state.reviewRating = 5;

  document.getElementById('review-worker-name').textContent = workerName;
  document.getElementById('review-service-title').textContent = serviceTitle;
  document.getElementById('review-comment').value = '';
  setReviewStars(5);

  openModal('modal-review');
}

// Selector de Estrellas
function setReviewStars(rating) {
  state.reviewRating = rating;
  const starsContainer = document.getElementById('review-stars-container');
  starsContainer.querySelectorAll('.star-btn').forEach(btn => {
    const starVal = parseInt(btn.dataset.star);
    if (starVal <= rating) {
      btn.className = 'star-btn text-2xl text-amber-500 cursor-pointer transition transform hover:scale-110';
      btn.innerHTML = '★';
    } else {
      btn.className = 'star-btn text-2xl text-slate-300 cursor-pointer transition transform hover:scale-110';
      btn.innerHTML = '☆';
    }
  });
}

// Enviar Calificación a MySQL
async function submitReview(event) {
  event.preventDefault();
  const reqData = state.selectedRequestForReview;
  if (!reqData) return;

  const comment = document.getElementById('review-comment').value.trim();

  try {
    const res = await fetch('api/reviews.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        requestId: reqData.requestId,
        workerId: reqData.workerId,
        rating: state.reviewRating,
        comment: comment,
        serviceTitle: reqData.serviceTitle,
        clientName: 'Tú (Cliente Verificado)'
      })
    });
    const result = await res.json();
    if (result.success) {
      showToast(result.message || '¡Gracias por calificar!');
      closeModal('modal-review');
      setTimeout(() => window.location.reload(), 1000);
    } else {
      showToast(result.error || 'Error al guardar calificación', 'error');
    }
  } catch (e) {
    console.error(e);
    showToast('Error al conectar con la base de datos', 'error');
  }
}

// Toggle Disponibilidad de Trabajador
async function toggleWorkerAvailability(workerId = 'w-1') {
  try {
    const res = await fetch('api/workers.php?action=toggle_availability', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ workerId })
    });
    const result = await res.json();
    if (result.success) {
      showToast(`Disponibilidad cambiada a: ${result.is_available ? 'DISPONIBLE' : 'OCUPADO'}`);
      setTimeout(() => window.location.reload(), 600);
    }
  } catch (e) {
    console.error(e);
    showToast('Error de conexión', 'error');
  }
}

// Toggle Servicio Activo/Inactivo
async function toggleServiceActive(serviceId) {
  try {
    const res = await fetch('api/workers.php?action=toggle_service', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ serviceId })
    });
    const result = await res.json();
    if (result.success) {
      showToast('Estado del servicio actualizado');
      setTimeout(() => window.location.reload(), 600);
    }
  } catch (e) {
    console.error(e);
  }
}

// Agregar Nuevo Servicio a Catálogo
async function submitAddService(event) {
  event.preventDefault();
  const title = document.getElementById('new-svc-title').value.trim();
  const categoryId = document.getElementById('new-svc-category').value;
  const price = document.getElementById('new-svc-price').value;
  const priceType = document.getElementById('new-svc-price-type').value;
  const description = document.getElementById('new-svc-desc').value.trim();

  try {
    const res = await fetch('api/workers.php?action=add_service', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        workerId: 'w-1',
        title,
        categoryId,
        estimatedPrice: parseInt(price),
        priceType,
        description
      })
    });
    const result = await res.json();
    if (result.success) {
      showToast('¡Servicio agregado con éxito!');
      closeModal('modal-add-service');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(result.error || 'Error al guardar servicio', 'error');
    }
  } catch (e) {
    console.error(e);
  }
}

// Toggle Acreditación SENA (Administrador)
async function toggleWorkerVerification(workerId) {
  try {
    const res = await fetch('api/workers.php?action=toggle_verification', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ workerId })
    });
    const result = await res.json();
    if (result.success) {
      showToast(result.message);
      setTimeout(() => window.location.reload(), 600);
    }
  } catch (e) {
    console.error(e);
  }
}

// Inicialización de Eventos DOM
document.addEventListener('DOMContentLoaded', () => {
  // Lucide Icons inicial
  lucide.createIcons();

  // Búsqueda en vivo
  const searchInput = document.getElementById('main-search-input');
  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      state.searchQuery = e.target.value;
      filterWorkers();
    });
  }

  // Ordenamiento
  const sortSelect = document.getElementById('sort-by-select');
  if (sortSelect) {
    sortSelect.addEventListener('change', (e) => {
      state.sortBy = e.target.value;
      // Reordenar DOM de cards
      const container = document.getElementById('workers-grid');
      if (!container) return;
      const cards = Array.from(container.querySelectorAll('.worker-card-item'));
      cards.sort((a, b) => {
        if (state.sortBy === 'rating') return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
        if (state.sortBy === 'distance') return parseFloat(a.dataset.distance) - parseFloat(b.dataset.distance);
        if (state.sortBy === 'price') return parseInt(a.dataset.price) - parseInt(b.dataset.price);
        if (state.sortBy === 'experience') return parseInt(b.dataset.experience || 0) - parseInt(a.dataset.experience || 0);
        return 0;
      });
      cards.forEach(c => container.appendChild(c));
    });
  }

  // Selector de barrio del header
  const neighSelect = document.getElementById('neighborhood-selector');
  if (neighSelect) {
    neighSelect.addEventListener('change', (e) => {
      selectNeighborhood(e.target.value);
    });
  }
});
