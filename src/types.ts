export type Role = 'CLIENTE' | 'TRABAJADOR' | 'ADMINISTRADOR';

export type RequestStatus = 
  | 'PENDIENTE' 
  | 'ACEPTADA' 
  | 'RECHAZADA' 
  | 'PROGRAMADA' 
  | 'EN_PROCESO' 
  | 'FINALIZADA' 
  | 'CANCELADA';

export interface Category {
  id: string;
  name: string;
  iconName: string;
  description: string;
  technicianCount: number;
}

export interface Service {
  id: string;
  workerId: string;
  title: string;
  description: string;
  estimatedPrice: number;
  priceType: 'fijo' | 'desde' | 'por_hora';
  isActive: boolean;
  categoryId: string;
}

export interface Review {
  id: string;
  workerId: string;
  clientName: string;
  clientAvatar: string;
  rating: number; // 1 to 5
  comment: string;
  date: string;
  serviceTitle: string;
}

export interface WorkerProfile {
  id: string;
  name: string;
  email: string;
  phone: string;
  avatar: string;
  specialty: string;
  categoryId: string;
  secondaryTrades: string[];
  bio: string;
  experienceYears: number;
  senaCertified: boolean;
  certificateTitle?: string;
  neighborhood: string;
  distanceKm: number;
  rating: number;
  totalReviews: number;
  completedJobs: number;
  minRate: number;
  isAvailable: boolean;
  workingHours: string;
  portfolio: string[];
  services: Service[];
  reviews: Review[];
}

export interface ServiceRequest {
  id: string;
  code: string;
  clientId: string;
  clientName: string;
  clientPhone: string;
  clientAvatar: string;
  workerId: string;
  workerName: string;
  workerSpecialty: string;
  workerAvatar: string;
  serviceTitle: string;
  category: string;
  status: RequestStatus;
  scheduledDate: string;
  scheduledTime: string;
  address: string;
  neighborhood: string;
  description: string;
  estimatedCost: number;
  createdAt: string;
  updatedAt: string;
  hasReview?: boolean;
  urgent?: boolean;
  diagnosisPhoto?: string;
}

export interface ChatMessage {
  id: string;
  requestId: string;
  senderId: string;
  senderName: string;
  senderRole: 'CLIENTE' | 'TRABAJADOR';
  text: string;
  timestamp: string;
  attachmentUrl?: string;
}

export interface AuditLog {
  id: string;
  action: string;
  user: string;
  timestamp: string;
  schema: string;
  details: string;
  severity: 'INFO' | 'WARN' | 'CRITICAL';
}
