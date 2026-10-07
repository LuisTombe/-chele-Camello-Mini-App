import React from 'react';
import { RequestStatus } from '../types';
import { Clock, CheckCircle2, AlertCircle, Wrench, XCircle } from 'lucide-react';

interface StatusBadgeProps {
  status: RequestStatus;
  size?: 'sm' | 'md';
}

export const StatusBadge: React.FC<StatusBadgeProps> = ({ status, size = 'sm' }) => {
  const getStatusConfig = () => {
    switch (status) {
      case 'PENDIENTE':
        return {
          bg: '#FEF3C7',
          text: '#92400E',
          label: 'Pendiente',
          dot: '#D97706',
          icon: Clock,
          pulse: false,
        };
      case 'ACEPTADA':
      case 'PROGRAMADA':
        return {
          bg: '#E0F2FE',
          text: '#0369A1',
          label: status === 'PROGRAMADA' ? 'Programada' : 'Aceptada',
          dot: '#0284C7',
          icon: CheckCircle2,
          pulse: false,
        };
      case 'EN_PROCESO':
        return {
          bg: '#FFEDD5',
          text: '#C2410C',
          label: 'En Proceso',
          dot: '#EA580C',
          icon: Wrench,
          pulse: true,
        };
      case 'FINALIZADA':
        return {
          bg: '#DCFCE7',
          text: '#15803D',
          label: 'Finalizada',
          dot: '#16A34A',
          icon: CheckCircle2,
          pulse: false,
        };
      case 'RECHAZADA':
      case 'CANCELADA':
      default:
        return {
          bg: '#FEE2E2',
          text: '#B91C1C',
          label: status === 'RECHAZADA' ? 'Rechazada' : 'Cancelada',
          dot: '#DC2626',
          icon: XCircle,
          pulse: false,
        };
    }
  };

  const config = getStatusConfig();
  const Icon = config.icon;
  const paddingClass = size === 'sm' ? 'px-2.5 py-1 text-[11px]' : 'px-3 py-1.5 text-xs';

  return (
    <span
      className={`inline-flex items-center gap-1.5 rounded-full font-bold tracking-wider uppercase select-none ${paddingClass}`}
      style={{ backgroundColor: config.bg, color: config.text }}
    >
      <span
        className={`w-2 h-2 rounded-full inline-block ${config.pulse ? 'pulse-dot' : ''}`}
        style={{ backgroundColor: config.dot }}
      />
      <Icon className="w-3 h-3 stroke-[2.5]" />
      <span>{config.label}</span>
    </span>
  );
};
