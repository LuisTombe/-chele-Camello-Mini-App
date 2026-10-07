import React, { useState, useRef, useEffect } from 'react';
import { ServiceRequest, ChatMessage, Role } from '../types';
import { 
  X, 
  Send, 
  Phone, 
  MapPin, 
  Image as ImageIcon, 
  CheckCheck,
  Paperclip,
  Smile
} from 'lucide-react';

interface ChatModalProps {
  request: ServiceRequest | null;
  messages: ChatMessage[];
  currentRole: Role;
  onClose: () => void;
  onSendMessage: (requestId: string, text: string) => void;
}

export const ChatModal: React.FC<ChatModalProps> = ({
  request,
  messages,
  currentRole,
  onClose,
  onSendMessage,
}) => {
  if (!request) return null;

  const [inputMessage, setInputMessage] = useState('');
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const handleSend = (e: React.FormEvent) => {
    e.preventDefault();
    if (!inputMessage.trim()) return;
    onSendMessage(request.id, inputMessage);
    setInputMessage('');
  };

  const isClient = currentRole === 'CLIENTE';
  const otherName = isClient ? request.workerName : request.clientName;
  const otherRole = isClient ? request.workerSpecialty : 'Cliente (Neiva)';
  const otherAvatar = isClient ? request.workerAvatar : request.clientAvatar;
  const otherPhone = isClient ? '+57 312 458 9210' : request.clientPhone;

  // Quick suggestion chips
  const quickReplies = isClient
    ? [
        '¿A qué hora calcula llegar aproximadamente?',
        'Sí señor, aquí tengo el efectivo listo.',
        'La casa tiene reja blanca sobre la vía principal.',
      ]
    : [
        'Ya voy en camino en la moto.',
        '¿Me confirma si la llave de paso está cerrada?',
        'Ya me encuentro afuera de la vivienda.',
      ];

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-[#12263F]/60 backdrop-blur-xs animate-in fade-in duration-200">
      <div className="bg-white w-full max-w-lg h-[85vh] rounded-2xl shadow-2xl border border-[#E3DFD7] flex flex-col overflow-hidden">
        {/* Chat Header */}
        <div className="bg-[#12263F] text-white p-3.5 sm:p-4 flex items-center justify-between shrink-0">
          <div className="flex items-center gap-3">
            <div className="relative">
              <img
                src={otherAvatar}
                alt={otherName}
                className="w-10 h-10 rounded-full object-cover border-2 border-[#E5A93C]"
              />
              <span className="absolute bottom-0 right-0 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full" />
            </div>

            <div>
              <h3 className="font-bold text-sm sm:text-base leading-tight flex items-center gap-1.5">
                <span>{otherName}</span>
              </h3>
              <p className="text-[11px] text-amber-300 font-medium">
                {otherRole} • {request.code}
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <a
              href={`tel:${otherPhone}`}
              className="p-2 rounded-full bg-white/10 hover:bg-white/20 text-white transition"
              title="Llamada celular directa"
            >
              <Phone className="w-4 h-4 text-emerald-400" />
            </a>

            <button
              type="button"
              onClick={onClose}
              aria-label="Cerrar chat"
              className="p-1.5 rounded-full bg-white/10 hover:bg-white/20 text-white transition cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Job Mini Context Bar */}
        <div className="bg-[#FBF9F6] border-b border-[#E3DFD7] px-4 py-2 flex items-center justify-between text-xs text-[#606D7B] shrink-0">
          <span className="font-semibold text-[#12263F] truncate max-w-[200px]">
            {request.serviceTitle}
          </span>
          <span className="font-bold text-[#D96528]">
            ${request.estimatedCost.toLocaleString('es-CO')} COP
          </span>
        </div>

        {/* Message Thread */}
        <div className="flex-1 overflow-y-auto p-4 space-y-3 bg-[#FBF9F6]/50">
          {messages.length === 0 ? (
            <div className="text-center py-10 space-y-2 text-[#606D7B]">
              <p className="text-xs">No hay mensajes previos para esta solicitud.</p>
              <p className="text-[11px]">Escribe un mensaje para coordinar la llegada del camellador.</p>
            </div>
          ) : (
            messages.map((msg) => {
              const isMine =
                (isClient && msg.senderRole === 'CLIENTE') ||
                (!isClient && msg.senderRole === 'TRABAJADOR');

              return (
                <div
                  key={msg.id}
                  className={`flex flex-col ${isMine ? 'items-end' : 'items-start'}`}
                >
                  <span className="text-[10px] text-[#606D7B] mb-0.5 px-1">
                    {msg.senderName} • {msg.timestamp}
                  </span>
                  <div
                    className={`max-w-[85%] sm:max-w-[75%] rounded-2xl px-3.5 py-2.5 text-xs sm:text-sm leading-relaxed shadow-xs ${
                      isMine
                        ? 'bg-[#12263F] text-white rounded-br-xs'
                        : 'bg-white text-[#12263F] border border-[#E3DFD7] rounded-bl-xs'
                    }`}
                  >
                    <p>{msg.text}</p>
                    {msg.attachmentUrl && (
                      <div className="mt-2 rounded-lg overflow-hidden border border-white/20">
                        <img
                          src={msg.attachmentUrl}
                          alt="Adjunto de diagnóstico"
                          className="w-full h-32 object-cover"
                        />
                      </div>
                    )}
                  </div>
                  <span className="text-[9px] text-[#606D7B] mt-0.5 px-1 flex items-center gap-0.5">
                    {isMine && <CheckCheck className="w-3 h-3 text-emerald-600 inline" />}
                  </span>
                </div>
              );
            })
          )}
          <div ref={messagesEndRef} />
        </div>

        {/* Quick Suggestions */}
        <div className="p-2 bg-white border-t border-[#E3DFD7] flex items-center gap-1.5 overflow-x-auto scrollbar-none shrink-0">
          {quickReplies.map((qr, idx) => (
            <button
              key={idx}
              type="button"
              onClick={() => onSendMessage(request.id, qr)}
              className="text-[11px] font-medium bg-[#FBF9F6] border border-[#E3DFD7] text-[#12263F] hover:border-[#D96528] hover:text-[#D96528] px-2.5 py-1 rounded-full whitespace-nowrap transition cursor-pointer"
            >
              {qr}
            </button>
          ))}
        </div>

        {/* Message Input Box */}
        <form
          onSubmit={handleSend}
          className="p-3 bg-white border-t border-[#E3DFD7] flex items-center gap-2 shrink-0"
        >
          <input
            type="text"
            placeholder="Escribe un mensaje al camellador..."
            value={inputMessage}
            onChange={(e) => setInputMessage(e.target.value)}
            className="flex-1 bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl px-3.5 py-2 text-xs sm:text-sm font-medium text-[#12263F] focus:outline-none focus:border-[#D96528]"
          />

          <button
            type="submit"
            disabled={!inputMessage.trim()}
            className="p-2.5 bg-[#D96528] hover:bg-[#C25319] disabled:opacity-40 text-white rounded-xl shadow-xs transition cursor-pointer"
          >
            <Send className="w-4 h-4" />
          </button>
        </form>
      </div>
    </div>
  );
};
