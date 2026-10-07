/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState } from 'react';
import { 
  Role, 
  RequestStatus, 
  WorkerProfile, 
  Service, 
  ServiceRequest, 
  ChatMessage, 
  AuditLog 
} from './types';
import { 
  CATEGORIES, 
  NEIVA_NEIGHBORHOODS, 
  WORKERS_SEED, 
  INITIAL_REQUESTS, 
  INITIAL_CHAT, 
  INITIAL_AUDIT_LOGS 
} from './data/mockData';
import { Header } from './components/Header';
import { SpecialtyChips } from './components/SpecialtyChips';
import { WorkerCard } from './components/WorkerCard';
import { WorkerProfileModal } from './components/WorkerProfileModal';
import { RequestModal } from './components/RequestModal';
import { RequestsManager } from './components/RequestsManager';
import { ChatModal } from './components/ChatModal';
import { ReviewModal } from './components/ReviewModal';
import { WorkerView } from './components/WorkerView';
import { AdminView } from './components/AdminView';
import { DeviceFrame } from './components/DeviceFrame';
import { 
  Search, 
  MapPin, 
  Flame, 
  ShieldCheck, 
  Award, 
  SlidersHorizontal, 
  Clock, 
  Users, 
  Sparkles,
  CheckCircle,
  ThumbsUp,
  AlertCircle
} from 'lucide-react';

export default function App() {
  // App Global State
  const [currentRole, setCurrentRole] = useState<Role>('CLIENTE');
  const [activeTab, setActiveTab] = useState<'explorar' | 'solicitudes' | 'trabajador' | 'admin'>('explorar');
  const [isMobileDeviceFrame, setIsMobileDeviceFrame] = useState<boolean>(false);

  // Entities State
  const [workers, setWorkers] = useState<WorkerProfile[]>(WORKERS_SEED);
  const [requests, setRequests] = useState<ServiceRequest[]>(INITIAL_REQUESTS);
  const [chatMap, setChatMap] = useState<Record<string, ChatMessage[]>>(INITIAL_CHAT);
  const [auditLogs, setAuditLogs] = useState<AuditLog[]>(INITIAL_AUDIT_LOGS);

  // Filter & Search State
  const [selectedCategory, setSelectedCategory] = useState<string | null>(null);
  const [selectedNeighborhood, setSelectedNeighborhood] = useState<string>('todos');
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [sortBy, setSortBy] = useState<'rating' | 'distance' | 'price'>('rating');

  // Modals State
  const [profileModalWorker, setProfileModalWorker] = useState<WorkerProfile | null>(null);
  const [requestModalWorker, setRequestModalWorker] = useState<WorkerProfile | null>(null);
  const [requestModalService, setRequestModalService] = useState<Service | null>(null);
  const [chatModalRequest, setChatModalRequest] = useState<ServiceRequest | null>(null);
  const [reviewModalRequest, setReviewModalRequest] = useState<ServiceRequest | null>(null);
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3500);
  };

  // Filter Workers
  const filteredWorkers = workers.filter((worker) => {
    const matchesCategory = !selectedCategory || worker.categoryId === selectedCategory;
    const matchesNeighborhood =
      selectedNeighborhood === 'todos' || worker.neighborhood === selectedNeighborhood;
    const matchesSearch =
      worker.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      worker.specialty.toLowerCase().includes(searchQuery.toLowerCase()) ||
      worker.neighborhood.toLowerCase().includes(searchQuery.toLowerCase()) ||
      worker.secondaryTrades.some((trade) =>
        trade.toLowerCase().includes(searchQuery.toLowerCase())
      );
    return matchesCategory && matchesNeighborhood && matchesSearch;
  }).sort((a, b) => {
    if (sortBy === 'rating') return b.rating - a.rating;
    if (sortBy === 'distance') return a.distanceKm - b.distanceKm;
    if (sortBy === 'price') return a.minRate - b.minRate;
    return 0;
  });

  // Handlers
  const handleCreateRequest = (
    data: Omit<ServiceRequest, 'id' | 'code' | 'createdAt' | 'updatedAt'>
  ) => {
    const newId = `req-${Date.now()}`;
    const newCode = `CAM-2026-${Math.floor(100 + Math.random() * 900)}`;
    const now = new Date();
    const timeStr = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;
    const dateStr = now.toISOString().split('T')[0];

    const newRequest: ServiceRequest = {
      ...data,
      id: newId,
      code: newCode,
      createdAt: `${dateStr} ${timeStr}`,
      updatedAt: `${dateStr} ${timeStr}`,
    };

    setRequests([newRequest, ...requests]);

    // Add initial message to chat
    setChatMap((prev) => ({
      ...prev,
      [newId]: [
        {
          id: `msg-${Date.now()}`,
          requestId: newId,
          senderId: newRequest.clientId,
          senderName: newRequest.clientName,
          senderRole: 'CLIENTE',
          text: `¡Hola ${newRequest.workerName}! He creado una solicitud para ${newRequest.serviceTitle} en el barrio ${newRequest.neighborhood}. Detalle: "${newRequest.description}"`,
          timestamp: timeStr,
        },
      ],
    }));

    // Add Audit Log
    const newLog: AuditLog = {
      id: `aud-${Date.now()}`,
      action: 'SERVICE_REQUEST_CREATED',
      user: 'usuario_cliente',
      timestamp: `${dateStr} ${timeStr}:00`,
      schema: 'schema_solicitudes',
      details: `Solicitud ${newCode} registrada para ${newRequest.workerName} (${newRequest.category}).`,
      severity: 'INFO',
    };
    setAuditLogs([newLog, ...auditLogs]);

    setRequestModalWorker(null);
    setRequestModalService(null);
    setActiveTab('solicitudes');
    showToast(`¡Solicitud ${newCode} enviada! Notificando al técnico.`);
  };

  const handleUpdateStatus = (requestId: string, newStatus: RequestStatus) => {
    setRequests((prev) =>
      prev.map((r) =>
        r.id === requestId
          ? {
              ...r,
              status: newStatus,
              updatedAt: new Date().toLocaleTimeString('es-CO', {
                hour: '2-digit',
                minute: '2-digit',
              }),
            }
          : r
      )
    );

    const targetReq = requests.find((r) => r.id === requestId);
    if (targetReq) {
      const now = new Date();
      const timeStr = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;

      // Automatically add system notice in chat
      const statusNotice: Record<RequestStatus, string> = {
        ACEPTADA: 'El camellador ha aceptado el trabajo. Coordinando visita técnica.',
        RECHAZADA: 'El camellador no tiene disponibilidad para esta franja horaria.',
        PROGRAMADA: 'El servicio ha sido agendado.',
        EN_PROCESO: 'El técnico inició la labor en la dirección indicada.',
        FINALIZADA: 'El camello ha sido marcado como finalizado con éxito.',
        CANCELADA: 'La solicitud ha sido cancelada.',
        PENDIENTE: 'Solicitud en revisión.',
      };

      setChatMap((prev) => ({
        ...prev,
        [requestId]: [
          ...(prev[requestId] || []),
          {
            id: `msg-${Date.now()}`,
            requestId,
            senderId: 'sistema',
            senderName: 'Échele Camello Bot',
            senderRole: 'TRABAJADOR',
            text: `[ESTADO ACTUALIZADO]: ${statusNotice[newStatus]}`,
            timestamp: timeStr,
          },
        ],
      }));

      // Add audit log
      const newLog: AuditLog = {
        id: `aud-${Date.now()}`,
        action: 'STATUS_TRANSITION',
        user: currentRole === 'TRABAJADOR' ? 'tecnico_camellador' : 'usuario_cliente',
        timestamp: `${now.toISOString().split('T')[0]} ${timeStr}:00`,
        schema: 'schema_solicitudes',
        details: `Solicitud ${targetReq.code} cambió a ${newStatus}.`,
        severity: 'INFO',
      };
      setAuditLogs((logs) => [newLog, ...logs]);

      showToast(`Estado de ${targetReq.code} cambiado a ${newStatus}`);
    }
  };

  const handleSendMessage = (requestId: string, text: string) => {
    const isClient = currentRole === 'CLIENTE';
    const now = new Date();
    const timeStr = `${now.getHours().toString().padStart(2, '0')}:${now.getMinutes().toString().padStart(2, '0')}`;

    const newMsg: ChatMessage = {
      id: `msg-${Date.now()}`,
      requestId,
      senderId: isClient ? 'cli-me' : 'w-1',
      senderName: isClient ? 'Tú (Cliente)' : 'Jairo Hernán Trujillo (Técnico)',
      senderRole: isClient ? 'CLIENTE' : 'TRABAJADOR',
      text,
      timestamp: timeStr,
    };

    setChatMap((prev) => ({
      ...prev,
      [requestId]: [...(prev[requestId] || []), newMsg],
    }));
  };

  const handleSubmitReview = (
    requestId: string,
    workerId: string,
    ratingScore: number,
    commentText: string
  ) => {
    const targetWorker = workers.find((w) => w.id === workerId);
    if (!targetWorker) return;

    const newTotalReviews = targetWorker.totalReviews + 1;
    const newRating = (targetWorker.rating * targetWorker.totalReviews + ratingScore) / newTotalReviews;

    const newReview = {
      id: `rev-${Date.now()}`,
      workerId,
      clientName: 'Usuario Verificado',
      clientAvatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
      rating: ratingScore,
      comment: commentText,
      date: 'Hoy',
      serviceTitle: requests.find((r) => r.id === requestId)?.serviceTitle || 'Servicio Técnico',
    };

    setWorkers((prev) =>
      prev.map((w) =>
        w.id === workerId
          ? {
              ...w,
              rating: Number(newRating.toFixed(2)),
              totalReviews: newTotalReviews,
              reviews: [newReview, ...w.reviews],
            }
          : w
      )
    );

    setRequests((prev) =>
      prev.map((r) => (r.id === requestId ? { ...r, hasReview: true } : r))
    );

    // Audit log in schema_reputacion
    const newLog: AuditLog = {
      id: `aud-${Date.now()}`,
      action: 'CALIFICACION_REGISTRADA',
      user: 'usuario_cliente',
      timestamp: `${new Date().toISOString().split('T')[0]} 12:00:00`,
      schema: 'schema_reputacion',
      details: `Calificación de ${ratingScore} estrellas registrada para trabajador ${targetWorker.name}. Nuevo promedio: ${newRating.toFixed(2)}.`,
      severity: 'INFO',
    };
    setAuditLogs((logs) => [newLog, ...logs]);

    setReviewModalRequest(null);
    showToast(`¡Gracias por calificar a ${targetWorker.name} con ${ratingScore} estrellas!`);
  };

  const handleToggleWorkerAvailability = () => {
    // Toggles availability for primary sample worker w-1
    setWorkers((prev) =>
      prev.map((w) => (w.id === 'w-1' ? { ...w, isAvailable: !w.isAvailable } : w))
    );
    showToast('Estado de disponibilidad laboral actualizado');
  };

  const handleAddServiceToWorker = (newSvcData: Omit<Service, 'id' | 'workerId'>) => {
    const newService: Service = {
      ...newSvcData,
      id: `svc-${Date.now()}`,
      workerId: 'w-1',
    };
    setWorkers((prev) =>
      prev.map((w) =>
        w.id === 'w-1' ? { ...w, services: [newService, ...w.services] } : w
      )
    );
    showToast('¡Servicio agregado con éxito a tu catálogo!');
  };

  const handleToggleServiceActive = (serviceId: string) => {
    setWorkers((prev) =>
      prev.map((w) =>
        w.id === 'w-1'
          ? {
              ...w,
              services: w.services.map((s) =>
                s.id === serviceId ? { ...s, isActive: !s.isActive } : s
              ),
            }
          : w
      )
    );
    showToast('Estado del servicio actualizado');
  };

  const handleToggleVerification = (workerId: string) => {
    setWorkers((prev) =>
      prev.map((w) =>
        w.id === workerId ? { ...w, senaCertified: !w.senaCertified } : w
      )
    );
    showToast('Insignia de acreditación SENA actualizada en la base de datos');
  };

  // Pending count for the badge
  const pendingRequestsCount = requests.filter((r) => r.status === 'PENDIENTE').length;

  return (
    <DeviceFrame isMobileDevice={isMobileDeviceFrame}>
      <div className="min-h-screen bg-[#FBF9F6] text-[#12263F] flex flex-col selection:bg-[#D96528] selection:text-white">
        {/* Navigation & Role Bar */}
        <Header
          currentRole={currentRole}
          onChangeRole={setCurrentRole}
          activeTab={activeTab}
          onChangeTab={setActiveTab}
          pendingRequestsCount={pendingRequestsCount}
          selectedNeighborhood={selectedNeighborhood}
          onChangeNeighborhood={setSelectedNeighborhood}
          neighborhoods={NEIVA_NEIGHBORHOODS}
          isMobileDeviceFrame={isMobileDeviceFrame}
          onToggleDeviceFrame={() => setIsMobileDeviceFrame(!isMobileDeviceFrame)}
        />

        {/* Global Toast Alert */}
        {toastMessage && (
          <div className="fixed bottom-6 right-6 z-50 bg-[#12263F] text-white px-4 py-3 rounded-2xl shadow-xl border border-[#E5A93C] flex items-center gap-2.5 animate-in slide-in-from-bottom-5 text-xs sm:text-sm font-semibold">
            <CheckCircle className="w-4 h-4 text-[#E5A93C] shrink-0" />
            <span>{toastMessage}</span>
          </div>
        )}

        {/* Main Workspace Body */}
        <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 sm:py-8">
          {/* TAB 1: EXPLORAR / BUSCAR CAMELLADORES */}
          {activeTab === 'explorar' && (
            <div className="space-y-6 sm:space-y-8">
              {/* Hero Banner with Modern Warm Industrial Feel */}
              <div className="relative rounded-3xl bg-gradient-to-br from-[#12263F] via-[#1a385c] to-[#12263F] text-white p-6 sm:p-10 overflow-hidden shadow-lg border border-[#E3DFD7]/20">
                {/* Decorative warm ambient glow */}
                <div className="absolute top-0 right-0 w-96 h-96 bg-[#D96528]/15 rounded-full blur-3xl -mr-20 -mt-20 pointer-events-none" />
                <div className="absolute bottom-0 left-1/3 w-64 h-64 bg-[#E5A93C]/10 rounded-full blur-2xl pointer-events-none" />

                <div className="relative z-10 max-w-2xl space-y-4">
                  <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-[#E5A93C] text-xs font-bold border border-white/10">
                    <Flame className="w-3.5 h-3.5 fill-[#E5A93C]" />
                    <span>Mano de obra local verificada • Neiva, Huila</span>
                  </div>

                  <h1 className="text-2xl sm:text-4xl font-extrabold tracking-tight leading-tight">
                    ¿Se le dañó algo en casa?{' '}
                    <span className="text-[#D96528]">Échele Camello.</span>
                  </h1>

                  <p className="text-xs sm:text-sm text-slate-200 leading-relaxed max-w-xl">
                    Encuentra plomeros, electricistas, técnicos de aire y cerrajeros de confianza cerca a tu barrio. Tarifas justas, acreditación técnica SENA y pago directo sin intermediarios abusivos.
                  </p>

                  {/* Quick Search Bar inside Hero */}
                  <div className="pt-2">
                    <div className="bg-white p-1.5 rounded-2xl shadow-xl flex flex-col sm:flex-row items-stretch gap-2 text-xs sm:text-sm border border-white/40">
                      <div className="flex-1 flex items-center px-3 gap-2">
                        <Search className="w-4 h-4 text-[#606D7B]" />
                        <input
                          type="text"
                          placeholder="¿Qué necesitas reparar? Ej: Fuga de agua, Minisplit, Breakers..."
                          value={searchQuery}
                          onChange={(e) => setSearchQuery(e.target.value)}
                          className="w-full bg-transparent text-[#12263F] font-semibold focus:outline-none placeholder:text-[#606D7B]/80 text-xs sm:text-sm"
                        />
                      </div>

                      <button
                        type="button"
                        onClick={() => {}}
                        className="px-6 py-2.5 rounded-xl bg-[#D96528] hover:bg-[#C25319] text-white font-bold transition flex items-center justify-center gap-1.5 shadow-md cursor-pointer shrink-0"
                      >
                        <Flame className="w-4 h-4" />
                        <span>Buscar Ahora</span>
                      </button>
                    </div>
                  </div>

                  {/* Quick Neighborhood Badges */}
                  <div className="flex items-center gap-2 flex-wrap pt-1 text-xs text-slate-300">
                    <span className="text-[11px] font-semibold text-amber-300">Barrios activos:</span>
                    {['Cándido', 'Las Granjas', 'Ipanema', 'Prado Alto', 'Centro'].map((b) => (
                      <button
                        key={b}
                        type="button"
                        onClick={() => setSelectedNeighborhood(b)}
                        className={`text-[11px] px-2 py-0.5 rounded-md transition cursor-pointer ${
                          selectedNeighborhood === b
                            ? 'bg-[#E5A93C] text-[#12263F] font-bold'
                            : 'bg-white/10 hover:bg-white/20 text-white'
                        }`}
                      >
                        {b}
                      </button>
                    ))}
                  </div>
                </div>
              </div>

              {/* Specialty Category Filter Chips */}
              <div className="space-y-3">
                <div className="flex items-center justify-between">
                  <h2 className="text-base sm:text-lg font-bold text-[#12263F] flex items-center gap-2">
                    <span>Filtros de Oficio & Especialidad</span>
                  </h2>
                  <span className="text-xs text-[#606D7B]">
                    {CATEGORIES.length} categorías técnicas
                  </span>
                </div>

                <SpecialtyChips
                  categories={CATEGORIES}
                  selectedCategory={selectedCategory}
                  onSelectCategory={setSelectedCategory}
                />
              </div>

              {/* Filter bar: sorting & result counts */}
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 rounded-2xl border border-[#E3DFD7]">
                <div className="flex items-center gap-2 text-xs text-[#606D7B]">
                  <span className="font-bold text-[#12263F]">{filteredWorkers.length}</span>
                  <span>camelladores disponibles</span>
                  {selectedNeighborhood !== 'todos' && (
                    <span className="bg-[#FBF9F6] px-2 py-0.5 rounded text-[11px] font-semibold text-[#D96528] border border-[#E3DFD7]">
                      en Barrio {selectedNeighborhood}
                    </span>
                  )}
                </div>

                <div className="flex items-center gap-2 text-xs">
                  <SlidersHorizontal className="w-3.5 h-3.5 text-[#606D7B]" />
                  <span className="text-[#606D7B] font-medium">Ordenar por:</span>
                  <select
                    value={sortBy}
                    onChange={(e) => setSortBy(e.target.value as any)}
                    className="bg-[#FBF9F6] border border-[#E3DFD7] rounded-lg px-2.5 py-1 text-xs font-bold text-[#12263F] focus:outline-none cursor-pointer"
                  >
                    <option value="rating">Mayor Calificación (Estrellas)</option>
                    <option value="distance">Más Cercanos (Distancia Km)</option>
                    <option value="price">Menor Tarifa Base</option>
                  </select>
                </div>
              </div>

              {/* Workers Grid */}
              {filteredWorkers.length === 0 ? (
                <div className="bg-white rounded-2xl border border-[#E3DFD7] p-12 text-center space-y-3">
                  <div className="w-12 h-12 rounded-full bg-amber-50 text-[#D96528] flex items-center justify-center mx-auto">
                    <Search className="w-6 h-6" />
                  </div>
                  <h3 className="font-bold text-[#12263F] text-base">
                    No encontramos camelladores con ese filtro
                  </h3>
                  <p className="text-xs text-[#606D7B] max-w-sm mx-auto">
                    Prueba cambiando el barrio o seleccionando "Todos los Oficios" para explorar técnicos en otros sectores de Neiva.
                  </p>
                  <button
                    type="button"
                    onClick={() => {
                      setSelectedCategory(null);
                      setSelectedNeighborhood('todos');
                      setSearchQuery('');
                    }}
                    className="px-4 py-2 bg-[#12263F] text-white rounded-xl text-xs font-bold hover:bg-[#1c3758] transition cursor-pointer"
                  >
                    Restablecer Filtros
                  </button>
                </div>
              ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                  {filteredWorkers.map((worker) => (
                    <WorkerCard
                      key={worker.id}
                      worker={worker}
                      onOpenProfile={(w) => setProfileModalWorker(w)}
                      onRequestQuote={(w) => {
                        setRequestModalWorker(w);
                        setRequestModalService(null);
                      }}
                    />
                  ))}
                </div>
              )}
            </div>
          )}

          {/* TAB 2: GESTIÓN DE SOLICITUDES / MIS CAMELLOS */}
          {activeTab === 'solicitudes' && (
            <RequestsManager
              requests={requests}
              currentRole={currentRole}
              onUpdateStatus={handleUpdateStatus}
              onOpenChat={(req) => setChatModalRequest(req)}
              onOpenReview={(req) => setReviewModalRequest(req)}
            />
          )}

          {/* TAB 3: MODO TRABAJADOR */}
          {activeTab === 'trabajador' && (
            <WorkerView
              worker={workers.find((w) => w.id === 'w-1') || workers[0]}
              requests={requests}
              onToggleAvailability={handleToggleWorkerAvailability}
              onAddService={handleAddServiceToWorker}
              onToggleServiceActive={handleToggleServiceActive}
              onGoToRequests={() => setActiveTab('solicitudes')}
            />
          )}

          {/* TAB 4: MODO ADMINISTRADOR (SENA ADSO AUDIT) */}
          {activeTab === 'admin' && (
            <AdminView
              workers={workers}
              auditLogs={auditLogs}
              requests={requests}
              onToggleVerification={handleToggleVerification}
              onAddAuditLog={(action, schema, details) => {
                const newLog: AuditLog = {
                  id: `aud-${Date.now()}`,
                  action,
                  user: 'admin_adso',
                  timestamp: new Date().toISOString(),
                  schema,
                  details,
                  severity: 'INFO',
                };
                setAuditLogs((l) => [newLog, ...l]);
              }}
            />
          )}
        </main>

        {/* Footer */}
        <footer className="bg-white border-t border-[#E3DFD7] py-6 px-4 sm:px-6 text-xs text-[#606D7B] mt-12">
          <div className="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <div className="flex items-center gap-2">
              <div className="w-6 h-6 rounded-lg bg-[#D96528] flex items-center justify-center text-white">
                <Flame className="w-3.5 h-3.5 fill-amber-200" />
              </div>
              <span className="font-extrabold text-[#12263F]">Échele Camello</span>
              <span>— Plataforma de intermediación laboral para Neiva, Huila</span>
            </div>

            <div className="flex items-center gap-3 text-[11px]">
              <span className="font-semibold text-[#12263F]">SENA ADSO Ficha 3413988</span>
              <span>•</span>
              <span>Arquitectura Microservicios & Docker PostgreSQL</span>
            </div>
          </div>
        </footer>

        {/* Modals & Dialogs */}
        {profileModalWorker && (
          <WorkerProfileModal
            worker={profileModalWorker}
            onClose={() => setProfileModalWorker(null)}
            onRequestQuote={(w, svc) => {
              setProfileModalWorker(null);
              setRequestModalWorker(w);
              setRequestModalService(svc || null);
            }}
          />
        )}

        {requestModalWorker && (
          <RequestModal
            worker={requestModalWorker}
            initialService={requestModalService}
            onClose={() => {
              setRequestModalWorker(null);
              setRequestModalService(null);
            }}
            onSubmitRequest={handleCreateRequest}
          />
        )}

        {chatModalRequest && (
          <ChatModal
            request={chatModalRequest}
            messages={chatMap[chatModalRequest.id] || []}
            currentRole={currentRole}
            onClose={() => setChatModalRequest(null)}
            onSendMessage={handleSendMessage}
          />
        )}

        {reviewModalRequest && (
          <ReviewModal
            request={reviewModalRequest}
            onClose={() => setReviewModalRequest(null)}
            onSubmitReview={handleSubmitReview}
          />
        )}
      </div>
    </DeviceFrame>
  );
}
