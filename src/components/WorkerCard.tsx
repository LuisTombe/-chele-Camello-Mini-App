import React from 'react';
import { WorkerProfile } from '../types';
import { 
  Star, 
  MapPin, 
  Award, 
  Clock, 
  CheckCircle, 
  ShieldCheck, 
  ArrowRight,
  Send
} from 'lucide-react';

interface WorkerCardProps {
  worker: WorkerProfile;
  onOpenProfile: (worker: WorkerProfile) => void;
  onRequestQuote: (worker: WorkerProfile) => void;
}

export const WorkerCard: React.FC<WorkerCardProps> = ({
  worker,
  onOpenProfile,
  onRequestQuote,
}) => {
  const formattedRate = new Intl.NumberFormat('es-CO', {
    style: 'currency',
    currency: 'COP',
    maximumFractionDigits: 0,
  }).format(worker.minRate);

  return (
    <div className="bg-white rounded-2xl border border-[#E3DFD7] p-5 sm:p-6 shadow-xs hover:shadow-md transition-all duration-300 flex flex-col justify-between group hover:border-[#D96528]/40">
      {/* Top Header */}
      <div>
        <div className="flex items-start justify-between gap-3 mb-4">
          <div className="flex items-start gap-3.5">
            {/* Avatar with 2px #E5A93C border */}
            <div className="relative shrink-0">
              <img
                src={worker.avatar}
                alt={worker.name}
                className="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-2 border-[#E5A93C] shadow-xs"
              />
              {worker.isAvailable ? (
                <span 
                  className="absolute bottom-0 right-0 w-3.5 h-3.5 bg-emerald-500 border-2 border-white rounded-full" 
                  title="Disponible para camellar hoy"
                />
              ) : (
                <span 
                  className="absolute bottom-0 right-0 w-3.5 h-3.5 bg-gray-400 border-2 border-white rounded-full" 
                  title="En descanso / Ocupado"
                />
              )}
            </div>

            <div>
              <div className="flex items-center gap-1.5 flex-wrap">
                <h3 className="text-base sm:text-lg font-bold text-[#12263F] group-hover:text-[#D96528] transition-colors">
                  {worker.name}
                </h3>
                {worker.senaCertified && (
                  <span 
                    className="inline-flex items-center gap-1 px-1.5 py-0.5 bg-amber-50 text-[#7b5500] text-[10px] font-bold rounded-md border border-[#E5A93C]/40"
                    title="Certificado por el Servicio Nacional de Aprendizaje (SENA)"
                  >
                    <Award className="w-3 h-3 text-[#E5A93C]" />
                    <span>SENA</span>
                  </span>
                )}
              </div>

              <p className="text-xs sm:text-sm font-semibold text-[#D96528] mt-0.5">
                {worker.specialty}
              </p>

              <div className="flex items-center gap-1.5 mt-1 text-xs text-[#606D7B]">
                <MapPin className="w-3.5 h-3.5 text-[#D96528] shrink-0" />
                <span className="font-medium text-[#12263F]">{worker.neighborhood}</span>
                <span>•</span>
                <span>a {worker.distanceKm} km</span>
              </div>
            </div>
          </div>

          {/* Rating Block */}
          <div className="flex flex-col items-end shrink-0 bg-[#FBF9F6] border border-[#E3DFD7] px-2.5 py-1.5 rounded-xl">
            <div className="flex items-center gap-1">
              <Star className="w-4 h-4 fill-[#E5A93C] text-[#E5A93C]" />
              <span className="font-bold text-sm text-[#12263F]">{worker.rating.toFixed(1)}</span>
            </div>
            <span className="text-[10px] text-[#606D7B]">({worker.totalReviews} reseñas)</span>
          </div>
        </div>

        {/* Bio preview */}
        <p className="text-xs sm:text-sm text-[#606D7B] line-clamp-2 leading-relaxed mb-4">
          {worker.bio}
        </p>

        {/* Secondary Trades Chips */}
        <div className="flex flex-wrap gap-1.5 mb-4">
          {worker.secondaryTrades.slice(0, 3).map((trade, idx) => (
            <span
              key={idx}
              className="text-[11px] font-medium bg-[#FBF9F6] text-[#12263F] px-2 py-0.5 rounded-md border border-[#E3DFD7]/80"
            >
              {trade}
            </span>
          ))}
        </div>

        {/* Price & Guarantee ribbon */}
        <div className="flex items-center justify-between py-2.5 px-3 bg-[#FBF9F6] rounded-xl border border-[#E3DFD7] mb-5 text-xs">
          <div>
            <span className="text-[11px] text-[#606D7B] block">Tarifa estimada desde</span>
            <span className="text-sm font-extrabold text-[#12263F]">{formattedRate}</span>
          </div>

          <div className="text-right">
            <span className="text-[11px] text-[#606D7B] block">Trabajos realizados</span>
            <span className="font-bold text-[#12263F] flex items-center gap-1 justify-end">
              <CheckCircle className="w-3.5 h-3.5 text-emerald-600" />
              {worker.completedJobs} camellos
            </span>
          </div>
        </div>
      </div>

      {/* Footer Actions */}
      <div className="grid grid-cols-2 gap-2 pt-2 border-t border-[#E3DFD7]/60">
        <button
          type="button"
          onClick={() => onOpenProfile(worker)}
          className="w-full py-2.5 px-3 rounded-xl border border-[#E3DFD7] bg-white text-[#12263F] text-xs font-bold hover:bg-[#FBF9F6] hover:border-[#12263F] transition-all flex items-center justify-center gap-1.5 cursor-pointer"
        >
          <span>Ver Perfil</span>
          <ArrowRight className="w-3.5 h-3.5 text-[#606D7B]" />
        </button>

        <button
          type="button"
          onClick={() => onRequestQuote(worker)}
          className="w-full py-2.5 px-3 rounded-xl bg-[#D96528] hover:bg-[#C25319] text-white text-xs font-bold shadow-xs hover:shadow transition-all flex items-center justify-center gap-1.5 cursor-pointer"
        >
          <Send className="w-3.5 h-3.5 text-amber-200" />
          <span>Cotizar</span>
        </button>
      </div>
    </div>
  );
};
