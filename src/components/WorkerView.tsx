import React, { useState } from 'react';
import { WorkerProfile, Service, ServiceRequest } from '../types';
import { 
  UserCheck, 
  Power, 
  Plus, 
  DollarSign, 
  Star, 
  CheckCircle, 
  Briefcase, 
  TrendingUp, 
  Award, 
  Clock, 
  MapPin, 
  Eye, 
  EyeOff, 
  Edit3,
  Calendar
} from 'lucide-react';

interface WorkerViewProps {
  worker: WorkerProfile;
  requests: ServiceRequest[];
  onToggleAvailability: () => void;
  onAddService: (newService: Omit<Service, 'id' | 'workerId'>) => void;
  onToggleServiceActive: (serviceId: string) => void;
  onGoToRequests: () => void;
}

export const WorkerView: React.FC<WorkerViewProps> = ({
  worker,
  requests,
  onToggleAvailability,
  onAddService,
  onToggleServiceActive,
  onGoToRequests,
}) => {
  const [showAddModal, setShowAddModal] = useState(false);
  const [newTitle, setNewTitle] = useState('');
  const [newDescription, setNewDescription] = useState('');
  const [newPrice, setNewPrice] = useState('50000');
  const [newPriceType, setNewPriceType] = useState<'fijo' | 'desde' | 'por_hora'>('desde');

  // Stats calculation
  const workerRequests = requests.filter((r) => r.workerId === worker.id);
  const completedJobs = workerRequests.filter((r) => r.status === 'FINALIZADA');
  const totalEarned = completedJobs.reduce((acc, curr) => acc + curr.estimatedCost, 0) + (worker.completedJobs * 55000);
  const pendingCount = workerRequests.filter((r) => r.status === 'PENDIENTE').length;

  const handleCreateService = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTitle.trim()) return;

    onAddService({
      title: newTitle,
      description: newDescription,
      estimatedPrice: parseInt(newPrice, 10) || 40000,
      priceType: newPriceType,
      isActive: true,
      categoryId: worker.categoryId,
    });

    setNewTitle('');
    setNewDescription('');
    setNewPrice('50000');
    setShowAddModal(false);
  };

  return (
    <div className="space-y-6">
      {/* Top Profile & Availability Card */}
      <div className="bg-white rounded-2xl border border-[#E3DFD7] p-5 sm:p-6 shadow-xs">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-[#E3DFD7]">
          <div className="flex items-center gap-4">
            <div className="relative">
              <img
                src={worker.avatar}
                alt={worker.name}
                className="w-16 h-16 sm:w-20 sm:h-20 rounded-full object-cover border-3 border-[#E5A93C] shadow-md"
              />
              <span
                className={`absolute bottom-0 right-0 w-4 h-4 rounded-full border-2 border-white ${
                  worker.isAvailable ? 'bg-emerald-500' : 'bg-gray-400'
                }`}
              />
            </div>

            <div>
              <div className="flex items-center gap-2">
                <h1 className="text-xl sm:text-2xl font-bold text-[#12263F]">{worker.name}</h1>
                {worker.senaCertified && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-[#E5A93C] text-[#12263F] text-[11px] font-extrabold rounded-md">
                    <Award className="w-3 h-3" />
                    SENA
                  </span>
                )}
              </div>
              <p className="text-xs sm:text-sm font-semibold text-[#D96528] mt-0.5">
                {worker.specialty}
              </p>
              <div className="flex items-center gap-2 text-xs text-[#606D7B] mt-1">
                <MapPin className="w-3.5 h-3.5 text-[#D96528]" />
                <span>Base: Barrio {worker.neighborhood}, Neiva</span>
              </div>
            </div>
          </div>

          {/* Availability Toggle Switch */}
          <div className="bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl p-3 flex items-center justify-between sm:justify-start gap-4">
            <div>
              <span className="text-[11px] font-bold text-[#606D7B] uppercase block">
                Estado Operativo
              </span>
              <span
                className={`text-xs font-bold ${
                  worker.isAvailable ? 'text-emerald-700' : 'text-gray-600'
                }`}
              >
                {worker.isAvailable ? '● Disponible para Camellar' : '○ Fuera de Servicio / Descanso'}
              </span>
            </div>

            <button
              type="button"
              onClick={onToggleAvailability}
              className={`p-2.5 rounded-xl transition flex items-center gap-1.5 text-xs font-bold cursor-pointer ${
                worker.isAvailable
                  ? 'bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs'
                  : 'bg-gray-200 hover:bg-gray-300 text-gray-700'
              }`}
            >
              <Power className="w-4 h-4" />
              <span>{worker.isAvailable ? 'Activo' : 'Activar'}</span>
            </button>
          </div>
        </div>

        {/* 4 Stats Cards */}
        <div className="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pt-6">
          <div className="bg-[#FBF9F6] p-4 rounded-xl border border-[#E3DFD7]">
            <span className="text-[11px] text-[#606D7B] font-bold uppercase tracking-wide block">
              Ingresos del Mes
            </span>
            <div className="flex items-baseline gap-1 mt-1">
              <span className="text-xl sm:text-2xl font-black text-[#12263F]">
                ${totalEarned.toLocaleString('es-CO')}
              </span>
              <span className="text-[10px] text-[#606D7B]">COP</span>
            </div>
            <span className="text-[11px] text-emerald-700 font-semibold flex items-center gap-0.5 mt-1">
              <TrendingUp className="w-3 h-3" />
              Directo a tu Nequi / Efectivo
            </span>
          </div>

          <div 
            onClick={onGoToRequests}
            className="bg-[#FBF9F6] p-4 rounded-xl border border-[#E3DFD7] cursor-pointer hover:border-[#D96528] transition"
          >
            <span className="text-[11px] text-[#606D7B] font-bold uppercase tracking-wide block">
              Solicitudes Pendientes
            </span>
            <div className="flex items-baseline gap-1 mt-1">
              <span className="text-xl sm:text-2xl font-black text-[#D96528]">
                {pendingCount}
              </span>
              <span className="text-[10px] text-[#606D7B]">por responder</span>
            </div>
            <span className="text-[11px] text-[#D96528] font-semibold underline mt-1 block">
              Ir al Tablero de Estados →
            </span>
          </div>

          <div className="bg-[#FBF9F6] p-4 rounded-xl border border-[#E3DFD7]">
            <span className="text-[11px] text-[#606D7B] font-bold uppercase tracking-wide block">
              Reputación Neiva
            </span>
            <div className="flex items-center gap-1.5 mt-1">
              <Star className="w-5 h-5 fill-[#E5A93C] text-[#E5A93C]" />
              <span className="text-xl sm:text-2xl font-black text-[#12263F]">
                {worker.rating.toFixed(1)}
              </span>
              <span className="text-xs text-[#606D7B]">({worker.totalReviews} votos)</span>
            </div>
            <span className="text-[11px] text-[#606D7B] block mt-1">
              Top Camellador en {worker.neighborhood}
            </span>
          </div>

          <div className="bg-[#FBF9F6] p-4 rounded-xl border border-[#E3DFD7]">
            <span className="text-[11px] text-[#606D7B] font-bold uppercase tracking-wide block">
              Camellos Realizados
            </span>
            <div className="flex items-baseline gap-1 mt-1">
              <span className="text-xl sm:text-2xl font-black text-[#12263F]">
                {worker.completedJobs + completedJobs.length}
              </span>
              <span className="text-[10px] text-[#606D7B]">trabajos</span>
            </div>
            <span className="text-[11px] text-emerald-700 font-semibold flex items-center gap-0.5 mt-1">
              <CheckCircle className="w-3 h-3" />
              100% Satisfacción
            </span>
          </div>
        </div>
      </div>

      {/* Services Catalog Management (PRD Módulo 3) */}
      <div className="bg-white rounded-2xl border border-[#E3DFD7] p-5 sm:p-6 shadow-xs">
        <div className="flex items-center justify-between mb-4">
          <div>
            <h2 className="text-base sm:text-lg font-bold text-[#12263F]">
              Catálogo de Servicios Ofertados
            </h2>
            <p className="text-xs text-[#606D7B]">
              Publica, ajusta tarifas de referencia o desactiva temporalmente tus ofertas.
            </p>
          </div>

          <button
            type="button"
            onClick={() => setShowAddModal(true)}
            className="px-3.5 py-2 rounded-xl bg-[#D96528] hover:bg-[#C25319] text-white text-xs font-bold shadow-xs transition flex items-center gap-1.5 cursor-pointer"
          >
            <Plus className="w-4 h-4" />
            <span>Nuevo Servicio</span>
          </button>
        </div>

        {/* Services List */}
        <div className="space-y-3">
          {worker.services.map((svc) => (
            <div
              key={svc.id}
              className={`p-4 rounded-xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 ${
                svc.isActive
                  ? 'bg-white border-[#E3DFD7]'
                  : 'bg-gray-50 border-gray-200 opacity-60'
              }`}
            >
              <div className="flex-1">
                <div className="flex items-center gap-2">
                  <h3 className="font-bold text-sm text-[#12263F]">{svc.title}</h3>
                  <span
                    className={`text-[10px] font-bold px-2 py-0.5 rounded-full ${
                      svc.isActive
                        ? 'bg-emerald-100 text-emerald-800'
                        : 'bg-gray-200 text-gray-700'
                    }`}
                  >
                    {svc.isActive ? 'Activo en Búsqueda' : 'Pausado'}
                  </span>
                </div>
                <p className="text-xs text-[#606D7B] mt-1">{svc.description}</p>
                <div className="flex items-center gap-2 mt-2">
                  <span className="text-xs font-extrabold text-[#D96528]">
                    ${svc.estimatedPrice.toLocaleString('es-CO')} COP
                  </span>
                  <span className="text-[10px] text-[#606D7B] bg-[#FBF9F6] px-1.5 py-0.5 rounded border border-[#E3DFD7]">
                    Modalidad: {svc.priceType}
                  </span>
                </div>
              </div>

              {/* Action buttons */}
              <div className="flex items-center gap-2 self-end sm:self-center">
                <button
                  type="button"
                  onClick={() => onToggleServiceActive(svc.id)}
                  className={`p-2 rounded-xl border text-xs font-semibold flex items-center gap-1 transition cursor-pointer ${
                    svc.isActive
                      ? 'border-[#E3DFD7] text-[#606D7B] hover:text-[#12263F] hover:bg-[#FBF9F6]'
                      : 'border-emerald-300 bg-emerald-50 text-emerald-700'
                  }`}
                  title={svc.isActive ? 'Desactivar de la búsqueda' : 'Reactivar servicio'}
                >
                  {svc.isActive ? <EyeOff className="w-3.5 h-3.5" /> : <Eye className="w-3.5 h-3.5" />}
                  <span>{svc.isActive ? 'Pausar' : 'Activar'}</span>
                </button>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Add Service Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-[#12263F]/60 backdrop-blur-xs">
          <div className="bg-white rounded-2xl max-w-md w-full p-5 border border-[#E3DFD7] shadow-2xl space-y-4">
            <div className="flex items-center justify-between border-b border-[#E3DFD7] pb-3">
              <h3 className="font-bold text-base text-[#12263F]">Agregar Servicio al Catálogo</h3>
              <button
                type="button"
                onClick={() => setShowAddModal(false)}
                className="text-gray-400 hover:text-gray-600 text-sm font-bold cursor-pointer"
              >
                ✕
              </button>
            </div>

            <form onSubmit={handleCreateService} className="space-y-3 text-xs sm:text-sm">
              <div>
                <label className="block font-bold text-[#12263F] mb-1">
                  Título del Servicio
                </label>
                <input
                  type="text"
                  placeholder="Ej. Cambio de toma corriente polarizado"
                  value={newTitle}
                  onChange={(e) => setNewTitle(e.target.value)}
                  className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3 py-2 text-[#12263F] focus:border-[#D96528] focus:outline-none"
                  required
                />
              </div>

              <div>
                <label className="block font-bold text-[#12263F] mb-1">
                  Descripción del Trabajo
                </label>
                <textarea
                  rows={2}
                  placeholder="Especifica el alcance y herramientas incluidas..."
                  value={newDescription}
                  onChange={(e) => setNewDescription(e.target.value)}
                  className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl p-3 text-[#12263F] focus:border-[#D96528] focus:outline-none resize-none"
                  required
                />
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="block font-bold text-[#12263F] mb-1">
                    Tarifa Base (COP)
                  </label>
                  <input
                    type="number"
                    step="5000"
                    value={newPrice}
                    onChange={(e) => setNewPrice(e.target.value)}
                    className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3 py-2 text-[#12263F] focus:border-[#D96528] focus:outline-none"
                    required
                  />
                </div>

                <div>
                  <label className="block font-bold text-[#12263F] mb-1">
                    Tipo de Tarifa
                  </label>
                  <select
                    value={newPriceType}
                    onChange={(e) => setNewPriceType(e.target.value as any)}
                    className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3 py-2 text-[#12263F] focus:border-[#D96528] focus:outline-none"
                  >
                    <option value="desde">Precio base ("Desde")</option>
                    <option value="fijo">Precio Fijo</option>
                    <option value="por_hora">Por Hora</option>
                  </select>
                </div>
              </div>

              <div className="pt-2 flex justify-end gap-2 border-t border-[#E3DFD7]">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-4 py-2 rounded-xl border border-[#E3DFD7] text-xs font-bold text-[#606D7B]"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 rounded-xl bg-[#D96528] hover:bg-[#C25319] text-white text-xs font-bold shadow-xs"
                >
                  Guardar Servicio
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
