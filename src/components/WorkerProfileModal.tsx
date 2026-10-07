import React from 'react';
import { WorkerProfile, Service } from '../types';
import { 
  X, 
  Star, 
  MapPin, 
  Award, 
  Clock, 
  CheckCircle, 
  Phone, 
  Mail, 
  ShieldCheck, 
  Wrench, 
  Calendar,
  Send,
  MessageSquare
} from 'lucide-react';

interface WorkerProfileModalProps {
  worker: WorkerProfile | null;
  onClose: () => void;
  onRequestQuote: (worker: WorkerProfile, selectedService?: Service) => void;
}

export const WorkerProfileModal: React.FC<WorkerProfileModalProps> = ({
  worker,
  onClose,
  onRequestQuote,
}) => {
  if (!worker) return null;

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-[#12263F]/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="bg-white w-full max-w-2xl max-h-[90vh] rounded-2xl shadow-2xl border border-[#E3DFD7] flex flex-col overflow-hidden">
        {/* Modal Top Bar */}
        <div className="relative bg-gradient-to-r from-[#12263F] via-[#1c3758] to-[#12263F] text-white p-5 sm:p-6 shrink-0">
          <button
            type="button"
            onClick={onClose}
            aria-label="Cerrar perfil"
            className="absolute top-4 right-4 p-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>

          <div className="flex flex-col sm:flex-row items-center sm:items-start gap-4 text-center sm:text-left">
            <div className="relative shrink-0">
              <img
                src={worker.avatar}
                alt={worker.name}
                className="w-20 h-20 sm:w-24 sm:h-24 rounded-full object-cover border-3 border-[#E5A93C] shadow-lg"
              />
              {worker.isAvailable && (
                <span className="absolute bottom-1 right-1 px-2 py-0.5 bg-emerald-500 text-white text-[10px] font-bold rounded-full border-2 border-white">
                  Disponible
                </span>
              )}
            </div>

            <div className="flex-1">
              <div className="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                <h2 className="text-xl sm:text-2xl font-bold">{worker.name}</h2>
                {worker.senaCertified && (
                  <span className="inline-flex items-center gap-1 px-2 py-0.5 bg-[#E5A93C] text-[#12263F] text-xs font-extrabold rounded-md shadow-xs">
                    <Award className="w-3.5 h-3.5" />
                    SENA Certificado
                  </span>
                )}
              </div>

              <p className="text-amber-300 font-semibold text-sm mt-0.5">
                {worker.specialty}
              </p>

              <div className="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-2 text-xs text-slate-200">
                <span className="flex items-center gap-1">
                  <MapPin className="w-3.5 h-3.5 text-[#E5A93C]" />
                  Barrio {worker.neighborhood} ({worker.distanceKm} km)
                </span>
                <span className="flex items-center gap-1">
                  <Star className="w-3.5 h-3.5 fill-[#E5A93C] text-[#E5A93C]" />
                  <span className="font-bold text-white">{worker.rating.toFixed(1)}</span>
                  <span>({worker.totalReviews} reseñas)</span>
                </span>
                <span className="flex items-center gap-1">
                  <CheckCircle className="w-3.5 h-3.5 text-emerald-400" />
                  {worker.completedJobs} trabajos
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Scrollable Body */}
        <div className="overflow-y-auto p-5 sm:p-6 space-y-6">
          {/* SENA Accreditation highlight if present */}
          {worker.senaCertified && worker.certificateTitle && (
            <div className="bg-amber-50/80 border border-[#E5A93C]/40 rounded-xl p-3.5 flex items-start gap-3">
              <Award className="w-5 h-5 text-[#9b6b00] shrink-0 mt-0.5" />
              <div>
                <h4 className="text-xs font-bold text-[#7b5500] uppercase tracking-wide">
                  Acreditación Técnica Oficial
                </h4>
                <p className="text-xs font-medium text-[#12263F] mt-0.5">
                  {worker.certificateTitle}
                </p>
                <p className="text-[11px] text-[#606D7B] mt-0.5">
                  Verificado por la coordinación académica del SENA ADSO Ficha 3413988.
                </p>
              </div>
            </div>
          )}

          {/* Bio & Details */}
          <div>
            <h3 className="text-sm font-bold text-[#12263F] uppercase tracking-wider mb-2">
              Sobre el Camellador
            </h3>
            <p className="text-xs sm:text-sm text-[#606D7B] leading-relaxed">
              {worker.bio}
            </p>

            <div className="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
              <div className="bg-[#FBF9F6] p-3 rounded-xl border border-[#E3DFD7]">
                <span className="text-[11px] text-[#606D7B] block">Experiencia</span>
                <span className="text-sm font-bold text-[#12263F]">{worker.experienceYears} años</span>
              </div>
              <div className="bg-[#FBF9F6] p-3 rounded-xl border border-[#E3DFD7]">
                <span className="text-[11px] text-[#606D7B] block">Horario Habitual</span>
                <span className="text-xs font-bold text-[#12263F] line-clamp-1">{worker.workingHours}</span>
              </div>
              <div className="bg-[#FBF9F6] p-3 rounded-xl border border-[#E3DFD7] col-span-2 sm:col-span-1">
                <span className="text-[11px] text-[#606D7B] block">Tarifa Base</span>
                <span className="text-sm font-bold text-[#D96528]">
                  Desde ${worker.minRate.toLocaleString('es-CO')} COP
                </span>
              </div>
            </div>
          </div>

          {/* Services Catalog */}
          <div>
            <div className="flex items-center justify-between mb-3">
              <h3 className="text-sm font-bold text-[#12263F] uppercase tracking-wider">
                Catálogo de Servicios Ofertados
              </h3>
              <span className="text-xs text-[#606D7B]">{worker.services.length} disponibles</span>
            </div>

            <div className="space-y-2.5">
              {worker.services.map((svc) => (
                <div
                  key={svc.id}
                  className="bg-white border border-[#E3DFD7] rounded-xl p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-[#D96528] transition-colors"
                >
                  <div className="flex-1">
                    <h4 className="text-sm font-bold text-[#12263F]">{svc.title}</h4>
                    <p className="text-xs text-[#606D7B] mt-0.5">{svc.description}</p>
                    <div className="mt-1">
                      <span className="text-xs font-bold text-[#D96528]">
                        ${svc.estimatedPrice.toLocaleString('es-CO')} COP
                      </span>
                      <span className="text-[10px] text-[#606D7B] ml-1">
                        ({svc.priceType === 'desde' ? 'precio base' : 'precio fijo'})
                      </span>
                    </div>
                  </div>

                  <button
                    type="button"
                    onClick={() => {
                      onClose();
                      onRequestQuote(worker, svc);
                    }}
                    className="self-end sm:self-center px-3 py-1.5 bg-[#D96528] hover:bg-[#C25319] text-white text-xs font-bold rounded-lg transition flex items-center gap-1 cursor-pointer"
                  >
                    <Send className="w-3 h-3" />
                    <span>Solicitar</span>
                  </button>
                </div>
              ))}
            </div>
          </div>

          {/* Portfolio gallery */}
          {worker.portfolio && worker.portfolio.length > 0 && (
            <div>
              <h3 className="text-sm font-bold text-[#12263F] uppercase tracking-wider mb-2">
                Trabajos Recientes en Neiva
              </h3>
              <div className="grid grid-cols-2 gap-2">
                {worker.portfolio.map((img, idx) => (
                  <div key={idx} className="rounded-xl overflow-hidden h-28 border border-[#E3DFD7]">
                    <img
                      src={img}
                      alt={`Trabajo realizado ${idx + 1}`}
                      className="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                    />
                  </div>
                ))}
              </div>
            </div>
          )}

          {/* Reviews section */}
          <div>
            <div className="flex items-center justify-between mb-3">
              <h3 className="text-sm font-bold text-[#12263F] uppercase tracking-wider">
                Reseñas de Clientes ({worker.reviews.length})
              </h3>
              <div className="flex items-center gap-1">
                <Star className="w-4 h-4 fill-[#E5A93C] text-[#E5A93C]" />
                <span className="text-xs font-bold text-[#12263F]">{worker.rating.toFixed(1)} de 5</span>
              </div>
            </div>

            <div className="space-y-3">
              {worker.reviews.map((rev) => (
                <div key={rev.id} className="bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl p-3">
                  <div className="flex items-center justify-between mb-1.5">
                    <div className="flex items-center gap-2">
                      <img
                        src={rev.clientAvatar}
                        alt={rev.clientName}
                        className="w-6 h-6 rounded-full object-cover border border-[#E3DFD7]"
                      />
                      <span className="text-xs font-bold text-[#12263F]">{rev.clientName}</span>
                    </div>
                    <div className="flex items-center gap-1">
                      <div className="flex">
                        {[...Array(rev.rating)].map((_, i) => (
                          <Star key={i} className="w-3 h-3 fill-[#E5A93C] text-[#E5A93C]" />
                        ))}
                      </div>
                      <span className="text-[10px] text-[#606D7B] ml-1">{rev.date}</span>
                    </div>
                  </div>
                  <p className="text-xs text-[#606D7B] italic">"{rev.comment}"</p>
                  <span className="text-[10px] text-[#D96528] font-semibold mt-1 inline-block">
                    Servicio: {rev.serviceTitle}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </div>

        {/* Modal Footer CTA */}
        <div className="p-4 bg-[#FBF9F6] border-t border-[#E3DFD7] flex items-center justify-between gap-3 shrink-0">
          <div className="hidden sm:block">
            <span className="text-[11px] text-[#606D7B] block">Disponibilidad</span>
            <span className="text-xs font-bold text-[#12263F]">
              {worker.isAvailable ? 'Listo para agendar' : 'Agenda para mañana'}
            </span>
          </div>

          <div className="flex items-center gap-2 w-full sm:w-auto">
            <button
              type="button"
              onClick={onClose}
              className="flex-1 sm:flex-none px-4 py-2.5 rounded-xl border border-[#E3DFD7] bg-white text-xs font-bold text-[#12263F] hover:bg-gray-50 transition cursor-pointer"
            >
              Cerrar
            </button>

            <button
              type="button"
              onClick={() => {
                onClose();
                onRequestQuote(worker);
              }}
              className="flex-1 sm:flex-none px-6 py-2.5 rounded-xl bg-[#D96528] hover:bg-[#C25319] text-white text-xs font-bold shadow-md transition flex items-center justify-center gap-1.5 cursor-pointer"
            >
              <Send className="w-3.5 h-3.5 text-amber-200" />
              <span>Solicitar Cotización</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  );
};
