import React, { useState } from 'react';
import { WorkerProfile, Service, ServiceRequest } from '../types';
import { NEIVA_NEIGHBORHOODS } from '../data/mockData';
import { 
  X, 
  Send, 
  Calendar, 
  Clock, 
  MapPin, 
  AlertTriangle, 
  DollarSign, 
  FileText,
  CheckCircle,
  Camera
} from 'lucide-react';

interface RequestModalProps {
  worker: WorkerProfile | null;
  initialService?: Service | null;
  onClose: () => void;
  onSubmitRequest: (requestData: Omit<ServiceRequest, 'id' | 'code' | 'createdAt' | 'updatedAt'>) => void;
}

export const RequestModal: React.FC<RequestModalProps> = ({
  worker,
  initialService,
  onClose,
  onSubmitRequest,
}) => {
  if (!worker) return null;

  const [selectedServiceId, setSelectedServiceId] = useState<string>(
    initialService ? initialService.id : worker.services[0]?.id || ''
  );
  const [scheduledDate, setScheduledDate] = useState<string>(
    new Date().toISOString().split('T')[0]
  );
  const [scheduledTime, setScheduledTime] = useState<string>('14:00');
  const [neighborhood, setNeighborhood] = useState<string>(worker.neighborhood);
  const [address, setAddress] = useState<string>('Calle 26 # 1W - 45');
  const [description, setDescription] = useState<string>('');
  const [urgent, setUrgent] = useState<boolean>(false);
  const [hasPhoto, setHasPhoto] = useState<boolean>(false);

  const selectedService = worker.services.find((s) => s.id === selectedServiceId);
  const baseCost = selectedService ? selectedService.estimatedPrice : worker.minRate;
  const estimatedCost = urgent ? baseCost + 15000 : baseCost;

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!description.trim()) {
      alert('Por favor describe brevemente el problema o trabajo que necesitas.');
      return;
    }

    const payload: Omit<ServiceRequest, 'id' | 'code' | 'createdAt' | 'updatedAt'> = {
      clientId: 'cli-me',
      clientName: 'Usuario Cliente (Tú)',
      clientPhone: '+57 312 999 8877',
      clientAvatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80',
      workerId: worker.id,
      workerName: worker.name,
      workerSpecialty: worker.specialty,
      workerAvatar: worker.avatar,
      serviceTitle: selectedService ? selectedService.title : 'Trabajo Personalizado',
      category: worker.specialty,
      status: 'PENDIENTE',
      scheduledDate,
      scheduledTime,
      address,
      neighborhood,
      description,
      estimatedCost,
      urgent,
      diagnosisPhoto: hasPhoto
        ? 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80'
        : undefined,
    };

    onSubmitRequest(payload);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-[#12263F]/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="bg-white w-full max-w-xl max-h-[92vh] rounded-2xl shadow-2xl border border-[#E3DFD7] flex flex-col overflow-hidden">
        {/* Header */}
        <div className="bg-[#12263F] text-white p-4 sm:p-5 flex items-center justify-between shrink-0">
          <div className="flex items-center gap-3">
            <img
              src={worker.avatar}
              alt={worker.name}
              className="w-10 h-10 rounded-full object-cover border-2 border-[#E5A93C]"
            />
            <div>
              <h2 className="text-base sm:text-lg font-bold">Solicitar Camello</h2>
              <p className="text-xs text-amber-300">
                Para: {worker.name} ({worker.specialty})
              </p>
            </div>
          </div>

          <button
            type="button"
            onClick={onClose}
            aria-label="Cerrar modal"
            className="p-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Form Body */}
        <form onSubmit={handleSubmit} className="overflow-y-auto p-5 space-y-4 text-xs sm:text-sm">
          {/* Service picker */}
          <div>
            <label className="block font-bold text-[#12263F] mb-1.5">
              1. Selecciona el servicio requerido
            </label>
            <select
              value={selectedServiceId}
              onChange={(e) => setSelectedServiceId(e.target.value)}
              className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3 py-2.5 font-medium text-[#12263F] focus:border-[#D96528] focus:outline-none"
            >
              {worker.services.map((svc) => (
                <option key={svc.id} value={svc.id}>
                  {svc.title} — ${svc.estimatedPrice.toLocaleString('es-CO')} COP ({svc.priceType})
                </option>
              ))}
              <option value="otro">Otro diagnóstico / Cotización a revisar en sitio</option>
            </select>
          </div>

          {/* Date & Time */}
          <div className="grid grid-cols-2 gap-3">
            <div>
              <label className="block font-bold text-[#12263F] mb-1.5 flex items-center gap-1">
                <Calendar className="w-3.5 h-3.5 text-[#D96528]" />
                Fecha deseada
              </label>
              <input
                type="date"
                value={scheduledDate}
                onChange={(e) => setScheduledDate(e.target.value)}
                className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3 py-2 text-[#12263F] font-medium focus:border-[#D96528] focus:outline-none"
                required
              />
            </div>

            <div>
              <label className="block font-bold text-[#12263F] mb-1.5 flex items-center gap-1">
                <Clock className="w-3.5 h-3.5 text-[#D96528]" />
                Hora aproximada
              </label>
              <input
                type="time"
                value={scheduledTime}
                onChange={(e) => setScheduledTime(e.target.value)}
                className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3 py-2 text-[#12263F] font-medium focus:border-[#D96528] focus:outline-none"
                required
              />
            </div>
          </div>

          {/* Location in Neiva */}
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label className="block font-bold text-[#12263F] mb-1.5 flex items-center gap-1">
                <MapPin className="w-3.5 h-3.5 text-[#D96528]" />
                Barrio en Neiva
              </label>
              <select
                value={neighborhood}
                onChange={(e) => setNeighborhood(e.target.value)}
                className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3 py-2 text-[#12263F] font-medium focus:border-[#D96528] focus:outline-none"
              >
                {NEIVA_NEIGHBORHOODS.map((barrio) => (
                  <option key={barrio} value={barrio}>
                    {barrio}
                  </option>
                ))}
              </select>
            </div>

            <div>
              <label className="block font-bold text-[#12263F] mb-1.5">
                Dirección exacta o referencia
              </label>
              <input
                type="text"
                value={address}
                onChange={(e) => setAddress(e.target.value)}
                placeholder="Ej. Calle 26 # 1W - 45, Casa 3"
                className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3 py-2 text-[#12263F] font-medium focus:border-[#D96528] focus:outline-none"
                required
              />
            </div>
          </div>

          {/* Problem description */}
          <div>
            <label className="block font-bold text-[#12263F] mb-1.5 flex items-center gap-1">
              <FileText className="w-3.5 h-3.5 text-[#D96528]" />
              ¿Qué problema tienes o qué necesitas reparar?
            </label>
            <textarea
              rows={3}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Describe los síntomas, ruidos, fugas o especificaciones del trabajo para que el camellador traiga los repuestos correctos..."
              className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl p-3 text-[#12263F] font-medium focus:border-[#D96528] focus:outline-none resize-none"
              required
            />
          </div>

          {/* Urgency & Diagnosis photo attach simulation */}
          <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3 bg-[#FBF9F6] rounded-xl border border-[#E3DFD7]">
            <label className="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                checked={urgent}
                onChange={(e) => setUrgent(e.target.checked)}
                className="w-4 h-4 text-[#D96528] accent-[#D96528] rounded cursor-pointer"
              />
              <span className="text-xs font-semibold text-[#12263F]">
                ¡Servicio urgente para hoy! (+ $15.000 recargo de desplazamiento inmediato)
              </span>
            </label>

            <button
              type="button"
              onClick={() => setHasPhoto(!hasPhoto)}
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer ${
                hasPhoto 
                  ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' 
                  : 'bg-white text-[#606D7B] border border-[#E3DFD7] hover:text-[#12263F]'
              }`}
            >
              <Camera className="w-3.5 h-3.5" />
              <span>{hasPhoto ? 'Foto adjuntada (1)' : 'Adjuntar foto'}</span>
            </button>
          </div>

          {/* Pricing summary */}
          <div className="bg-amber-50/70 border border-[#E5A93C]/40 rounded-xl p-3.5 flex items-center justify-between">
            <div>
              <span className="text-[11px] text-[#7b5500] font-bold uppercase tracking-wider block">
                Valor estimado acordado
              </span>
              <span className="text-xs text-[#606D7B]">
                Pago directo al técnico al finalizar el trabajo (Efectivo o Nequi)
              </span>
            </div>
            <div className="text-right">
              <span className="text-lg font-black text-[#D96528]">
                ${estimatedCost.toLocaleString('es-CO')} COP
              </span>
            </div>
          </div>

          {/* Footer Submit */}
          <div className="pt-2 flex items-center justify-end gap-2 border-t border-[#E3DFD7]">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2.5 rounded-xl border border-[#E3DFD7] text-xs font-bold text-[#606D7B] hover:text-[#12263F] cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              className="px-6 py-2.5 rounded-xl bg-[#D96528] hover:bg-[#C25319] text-white text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer"
            >
              <Send className="w-3.5 h-3.5 text-amber-200" />
              <span>Enviar Solicitud al Camellador</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
