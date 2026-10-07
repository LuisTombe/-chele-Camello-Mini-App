import React, { useState } from 'react';
import { ServiceRequest, RequestStatus, Role } from '../types';
import { StatusBadge } from './StatusBadge';
import { 
  Calendar, 
  Clock, 
  MapPin, 
  Phone, 
  MessageSquare, 
  Star, 
  CheckCircle2, 
  XCircle, 
  Wrench, 
  AlertTriangle, 
  ChevronRight,
  Search,
  ArrowUpRight,
  Camera
} from 'lucide-react';

interface RequestsManagerProps {
  requests: ServiceRequest[];
  currentRole: Role;
  onUpdateStatus: (requestId: string, newStatus: RequestStatus) => void;
  onOpenChat: (request: ServiceRequest) => void;
  onOpenReview: (request: ServiceRequest) => void;
}

export const RequestsManager: React.FC<RequestsManagerProps> = ({
  requests,
  currentRole,
  onUpdateStatus,
  onOpenChat,
  onOpenReview,
}) => {
  const [filterStatus, setFilterStatus] = useState<string>('todos');
  const [searchTerm, setSearchTerm] = useState<string>('');
  const [expandedRequestId, setExpandedRequestId] = useState<string | null>(null);

  const filteredRequests = requests.filter((req) => {
    const matchesStatus = filterStatus === 'todos' || req.status === filterStatus;
    const matchesSearch =
      req.code.toLowerCase().includes(searchTerm.toLowerCase()) ||
      req.serviceTitle.toLowerCase().includes(searchTerm.toLowerCase()) ||
      req.workerName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      req.clientName.toLowerCase().includes(searchTerm.toLowerCase()) ||
      req.neighborhood.toLowerCase().includes(searchTerm.toLowerCase());
    return matchesStatus && matchesSearch;
  });

  const getRoleFiltered = () => {
    // If worker, show jobs where worker is w-1 or all for testing
    // If client, show all client requests
    return filteredRequests;
  };

  const finalDisplay = getRoleFiltered();

  return (
    <div className="space-y-6">
      {/* Top Controls & Status Tabs */}
      <div className="bg-white rounded-2xl border border-[#E3DFD7] p-4 sm:p-5 shadow-xs space-y-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h2 className="text-lg sm:text-xl font-bold text-[#12263F]">
              {currentRole === 'TRABAJADOR' ? 'Gestión de Solicitudes Recibidas' : 'Mis Solicitudes de Camello'}
            </h2>
            <p className="text-xs text-[#606D7B]">
              {currentRole === 'TRABAJADOR'
                ? 'Control de estados: Acepta, inicia y finaliza los trabajos agendados en Neiva.'
                : 'Monitorea el estado en tiempo real, comunícate con tu técnico y califica el servicio.'}
            </p>
          </div>

          {/* Quick Search */}
          <div className="relative w-full sm:w-64">
            <Search className="w-4 h-4 text-[#606D7B] absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Buscar por código, barrio o técnico..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl pl-9 pr-3 py-2 text-xs font-medium text-[#12263F] focus:outline-none focus:border-[#D96528]"
            />
          </div>
        </div>

        {/* Status Filter Tabs */}
        <div className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-xs">
          {[
            { id: 'todos', label: 'Todos' },
            { id: 'PENDIENTE', label: 'Pendientes' },
            { id: 'ACEPTADA', label: 'Aceptadas' },
            { id: 'EN_PROCESO', label: 'En Proceso' },
            { id: 'FINALIZADA', label: 'Finalizadas' },
            { id: 'CANCELADA', label: 'Canceladas' },
          ].map((tab) => {
            const count = tab.id === 'todos' 
              ? requests.length 
              : requests.filter((r) => r.status === tab.id).length;

            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => setFilterStatus(tab.id)}
                className={`px-3 py-1.5 rounded-full font-semibold transition cursor-pointer shrink-0 flex items-center gap-1.5 ${
                  filterStatus === tab.id
                    ? 'bg-[#12263F] text-white shadow-xs'
                    : 'bg-[#FBF9F6] text-[#606D7B] hover:text-[#12263F] border border-[#E3DFD7]'
                }`}
              >
                <span>{tab.label}</span>
                <span className={`text-[10px] px-1.5 py-0.2 rounded-full ${
                  filterStatus === tab.id ? 'bg-white/20 text-white' : 'bg-[#E3DFD7] text-[#12263F]'
                }`}>
                  {count}
                </span>
              </button>
            );
          })}
        </div>
      </div>

      {/* Requests List */}
      {finalDisplay.length === 0 ? (
        <div className="bg-white rounded-2xl border border-[#E3DFD7] p-10 text-center space-y-3">
          <div className="w-12 h-12 rounded-full bg-amber-50 text-[#D96528] flex items-center justify-center mx-auto">
            <Wrench className="w-6 h-6" />
          </div>
          <h3 className="font-bold text-[#12263F] text-base">No hay solicitudes en este filtro</h3>
          <p className="text-xs text-[#606D7B] max-w-sm mx-auto">
            No se encontraron camellos con el estado seleccionado. Cambia el filtro o solicita un nuevo servicio desde el explorador.
          </p>
        </div>
      ) : (
        <div className="space-y-4">
          {finalDisplay.map((req) => {
            const isExpanded = expandedRequestId === req.id;
            return (
              <div
                key={req.id}
                className="bg-white rounded-2xl border border-[#E3DFD7] p-5 shadow-xs hover:border-[#D96528]/50 transition-all duration-200"
              >
                {/* Card Header Row */}
                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-[#E3DFD7]/60">
                  <div className="flex items-center gap-3">
                    <span className="font-mono text-xs font-bold text-[#12263F] bg-[#FBF9F6] px-2 py-1 rounded-md border border-[#E3DFD7]">
                      {req.code}
                    </span>
                    <StatusBadge status={req.status} />
                    {req.urgent && (
                      <span className="inline-flex items-center gap-1 text-[10px] font-bold text-red-700 bg-red-50 border border-red-200 px-2 py-0.5 rounded-full">
                        <AlertTriangle className="w-3 h-3 text-red-600" />
                        URGENTE HOY
                      </span>
                    )}
                  </div>

                  <span className="text-[11px] text-[#606D7B]">
                    Registrado: {req.createdAt}
                  </span>
                </div>

                {/* Card Middle Info */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4 py-4">
                  {/* Service & Problem */}
                  <div className="md:col-span-2 space-y-1.5">
                    <h3 className="text-base font-bold text-[#12263F] flex items-center gap-2">
                      <span>{req.serviceTitle}</span>
                    </h3>
                    <p className="text-xs text-[#606D7B] line-clamp-2">
                      {req.description}
                    </p>

                    <div className="flex flex-wrap items-center gap-3 pt-2 text-xs text-[#606D7B]">
                      <span className="flex items-center gap-1">
                        <MapPin className="w-3.5 h-3.5 text-[#D96528]" />
                        <span className="font-semibold text-[#12263F]">{req.neighborhood}</span>: {req.address}
                      </span>
                      <span className="flex items-center gap-1">
                        <Calendar className="w-3.5 h-3.5 text-[#606D7B]" />
                        {req.scheduledDate} a las {req.scheduledTime}
                      </span>
                    </div>

                    {req.diagnosisPhoto && (
                      <div className="pt-1 flex items-center gap-1.5">
                        <Camera className="w-3.5 h-3.5 text-[#D96528]" />
                        <span className="text-[11px] font-semibold text-[#D96528]">
                          Foto del problema adjunta
                        </span>
                      </div>
                    )}
                  </div>

                  {/* Worker & Client details */}
                  <div className="bg-[#FBF9F6] rounded-xl p-3 border border-[#E3DFD7] flex flex-col justify-between">
                    <div className="flex items-center gap-3">
                      <img
                        src={currentRole === 'TRABAJADOR' ? req.clientAvatar : req.workerAvatar}
                        alt="Avatar"
                        className="w-10 h-10 rounded-full object-cover border border-[#E3DFD7]"
                      />
                      <div>
                        <span className="text-[10px] text-[#606D7B] block font-semibold uppercase">
                          {currentRole === 'TRABAJADOR' ? 'Cliente Solicitante' : 'Técnico Asignado'}
                        </span>
                        <h4 className="text-xs font-bold text-[#12263F]">
                          {currentRole === 'TRABAJADOR' ? req.clientName : req.workerName}
                        </h4>
                        <span className="text-[11px] text-[#606D7B]">
                          {currentRole === 'TRABAJADOR' ? req.clientPhone : req.workerSpecialty}
                        </span>
                      </div>
                    </div>

                    <div className="pt-2 mt-2 border-t border-[#E3DFD7]/80 flex items-center justify-between">
                      <span className="text-[11px] text-[#606D7B]">Valor pactado</span>
                      <span className="text-sm font-extrabold text-[#D96528]">
                        ${req.estimatedCost.toLocaleString('es-CO')} COP
                      </span>
                    </div>
                  </div>
                </div>

                {/* State Transition Flow Diagram (Progress Track) */}
                <div className="py-2.5 px-3 bg-[#FBF9F6] rounded-xl border border-[#E3DFD7] mb-3">
                  <div className="flex items-center justify-between text-[11px] font-bold text-[#606D7B]">
                    <span className={req.status === 'PENDIENTE' ? 'text-[#D96528]' : ''}>1. Solicitado</span>
                    <span>→</span>
                    <span className={req.status === 'ACEPTADA' ? 'text-sky-700' : ''}>2. Confirmado</span>
                    <span>→</span>
                    <span className={req.status === 'EN_PROCESO' ? 'text-orange-600' : ''}>3. En Ejecución</span>
                    <span>→</span>
                    <span className={req.status === 'FINALIZADA' ? 'text-emerald-700' : ''}>4. Finalizado & Calificado</span>
                  </div>
                </div>

                {/* Card Action Buttons */}
                <div className="flex flex-wrap items-center justify-between gap-2 pt-2 border-t border-[#E3DFD7]/60">
                  {/* Left: Chat Trigger */}
                  <button
                    type="button"
                    onClick={() => onOpenChat(req)}
                    className="px-3.5 py-2 rounded-xl bg-white border border-[#E3DFD7] hover:border-[#12263F] text-[#12263F] text-xs font-bold flex items-center gap-1.5 transition cursor-pointer"
                  >
                    <MessageSquare className="w-3.5 h-3.5 text-[#D96528]" />
                    <span>Chat con {currentRole === 'TRABAJADOR' ? req.clientName.split(' ')[0] : req.workerName.split(' ')[0]}</span>
                  </button>

                  {/* Right: State Action Buttons based on Role & Current State */}
                  <div className="flex items-center gap-2 flex-wrap">
                    {/* Worker Action Buttons */}
                    {currentRole === 'TRABAJADOR' ? (
                      <>
                        {req.status === 'PENDIENTE' && (
                          <>
                            <button
                              type="button"
                              onClick={() => onUpdateStatus(req.id, 'RECHAZADA')}
                              className="px-3 py-1.5 rounded-xl border border-red-200 text-red-700 text-xs font-bold hover:bg-red-50 transition cursor-pointer"
                            >
                              Rechazar
                            </button>
                            <button
                              type="button"
                              onClick={() => onUpdateStatus(req.id, 'ACEPTADA')}
                              className="px-4 py-1.5 rounded-xl bg-sky-700 hover:bg-sky-800 text-white text-xs font-bold shadow-xs transition flex items-center gap-1 cursor-pointer"
                            >
                              <CheckCircle2 className="w-3.5 h-3.5" />
                              <span>Aceptar Camello</span>
                            </button>
                          </>
                        )}

                        {req.status === 'ACEPTADA' && (
                          <button
                            type="button"
                            onClick={() => onUpdateStatus(req.id, 'EN_PROCESO')}
                            className="px-4 py-1.5 rounded-xl bg-[#D96528] hover:bg-[#C25319] text-white text-xs font-bold shadow-xs transition flex items-center gap-1 cursor-pointer"
                          >
                            <Wrench className="w-3.5 h-3.5" />
                            <span>Iniciar Trabajo (En Proceso)</span>
                          </button>
                        )}

                        {req.status === 'EN_PROCESO' && (
                          <button
                            type="button"
                            onClick={() => onUpdateStatus(req.id, 'FINALIZADA')}
                            className="px-4 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold shadow-xs transition flex items-center gap-1 cursor-pointer"
                          >
                            <CheckCircle2 className="w-3.5 h-3.5" />
                            <span>Marcar como Finalizado</span>
                          </button>
                        )}
                      </>
                    ) : (
                      /* Client Action Buttons */
                      <>
                        {req.status === 'PENDIENTE' && (
                          <button
                            type="button"
                            onClick={() => onUpdateStatus(req.id, 'CANCELADA')}
                            className="px-3 py-1.5 rounded-xl border border-[#E3DFD7] text-[#606D7B] hover:text-red-700 text-xs font-semibold cursor-pointer"
                          >
                            Cancelar Solicitud
                          </button>
                        )}

                        {req.status === 'FINALIZADA' && (
                          <button
                            type="button"
                            onClick={() => onOpenReview(req)}
                            disabled={req.hasReview}
                            className={`px-4 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer ${
                              req.hasReview
                                ? 'bg-amber-50 text-amber-800 border border-amber-300 opacity-80 cursor-default'
                                : 'bg-[#E5A93C] hover:bg-[#d4992f] text-[#12263F] shadow-xs'
                            }`}
                          >
                            <Star className="w-3.5 h-3.5 fill-current" />
                            <span>{req.hasReview ? 'Calificado ★★★★★' : 'Calificar al Camellador'}</span>
                          </button>
                        )}
                      </>
                    )}
                  </div>
                </div>
              </div>
            );
          })}
        </div>
      )}
    </div>
  );
};
