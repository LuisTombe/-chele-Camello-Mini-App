import React, { useState } from 'react';
import { ServiceRequest } from '../types';
import { X, Star, Send, ThumbsUp, CheckCircle2 } from 'lucide-react';

interface ReviewModalProps {
  request: ServiceRequest | null;
  onClose: () => void;
  onSubmitReview: (requestId: string, workerId: string, rating: number, comment: string) => void;
}

export const ReviewModal: React.FC<ReviewModalProps> = ({
  request,
  onClose,
  onSubmitReview,
}) => {
  if (!request) return null;

  const [rating, setRating] = useState<number>(5);
  const [hoverRating, setHoverRating] = useState<number | null>(null);
  const [comment, setComment] = useState<string>('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!comment.trim()) {
      alert('Por favor escribe un breve comentario sobre la atención y calidad del trabajo.');
      return;
    }
    onSubmitReview(request.id, request.workerId, rating, comment);
  };

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-[#12263F]/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-[#E3DFD7] overflow-hidden">
        {/* Header */}
        <div className="bg-[#12263F] text-white p-4 flex items-center justify-between">
          <div className="flex items-center gap-2.5">
            <Star className="w-5 h-5 fill-[#E5A93C] text-[#E5A93C]" />
            <h3 className="font-bold text-base">Calificar Servicio</h3>
          </div>
          <button
            type="button"
            onClick={onClose}
            aria-label="Cerrar modal de reseña"
            className="p-1 rounded-full bg-white/10 hover:bg-white/20 text-white cursor-pointer"
          >
            <X className="w-4 h-4" />
          </button>
        </div>

        {/* Content */}
        <form onSubmit={handleSubmit} className="p-5 space-y-4 text-xs sm:text-sm">
          <div className="text-center space-y-1">
            <img
              src={request.workerAvatar}
              alt={request.workerName}
              className="w-16 h-16 rounded-full object-cover mx-auto border-2 border-[#E5A93C] shadow-xs"
            />
            <h4 className="font-bold text-[#12263F] text-base">{request.workerName}</h4>
            <p className="text-xs text-[#606D7B]">{request.serviceTitle}</p>
          </div>

          {/* Star selector */}
          <div className="py-2 flex flex-col items-center justify-center gap-1.5">
            <span className="text-xs font-semibold text-[#606D7B]">
              ¿Qué tal fue la atención y solución del camellador?
            </span>
            <div className="flex items-center gap-2">
              {[1, 2, 3, 4, 5].map((star) => {
                const isFilled = (hoverRating !== null ? hoverRating : rating) >= star;
                return (
                  <button
                    key={star}
                    type="button"
                    onMouseEnter={() => setHoverRating(star)}
                    onMouseLeave={() => setHoverRating(null)}
                    onClick={() => setRating(star)}
                    className="p-1 transition-transform hover:scale-125 cursor-pointer"
                  >
                    <Star
                      className={`w-8 h-8 ${
                        isFilled
                          ? 'fill-[#E5A93C] text-[#E5A93C]'
                          : 'text-[#E3DFD7] fill-transparent'
                      }`}
                    />
                  </button>
                );
              })}
            </div>
            <span className="text-xs font-bold text-[#12263F]">
              {rating === 5 && '¡Excelente trabajo! 5/5'}
              {rating === 4 && 'Muy buen servicio 4/5'}
              {rating === 3 && 'Aceptable 3/5'}
              {rating === 2 && 'Regular 2/5'}
              {rating === 1 && 'Malo 1/5'}
            </span>
          </div>

          {/* Feedback textarea */}
          <div>
            <label className="block font-bold text-[#12263F] mb-1">
              Tu opinión para la comunidad de Neiva
            </label>
            <textarea
              rows={3}
              value={comment}
              onChange={(e) => setComment(e.target.value)}
              placeholder="Ej. Llegó a tiempo a Cándido, dejó todo limpio y el precio fue el convenido sin cobros sorpresa..."
              className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl p-3 text-[#12263F] font-medium focus:border-[#D96528] focus:outline-none resize-none"
              required
            />
          </div>

          {/* Action buttons */}
          <div className="flex items-center justify-end gap-2 pt-2 border-t border-[#E3DFD7]">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 rounded-xl border border-[#E3DFD7] text-xs font-bold text-[#606D7B] hover:text-[#12263F] cursor-pointer"
            >
              Cancelar
            </button>
            <button
              type="submit"
              className="px-5 py-2 rounded-xl bg-[#D96528] hover:bg-[#C25319] text-white text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer"
            >
              <Send className="w-3.5 h-3.5" />
              <span>Publicar Calificación</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
