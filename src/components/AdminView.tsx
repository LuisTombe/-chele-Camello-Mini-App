import React, { useState } from 'react';
import { WorkerProfile, AuditLog, ServiceRequest } from '../types';
import { 
  ShieldCheck, 
  Database, 
  Server, 
  CheckCircle2, 
  AlertCircle, 
  Award, 
  Users, 
  FileCode, 
  Activity, 
  Lock, 
  Search,
  Check,
  X
} from 'lucide-react';

interface AdminViewProps {
  workers: WorkerProfile[];
  auditLogs: AuditLog[];
  requests: ServiceRequest[];
  onToggleVerification: (workerId: string) => void;
  onAddAuditLog: (action: string, schema: string, details: string) => void;
}

export const AdminView: React.FC<AdminViewProps> = ({
  workers,
  auditLogs,
  requests,
  onToggleVerification,
  onAddAuditLog,
}) => {
  const [activeSchemaTab, setActiveSchemaTab] = useState('schema_usuarios');
  const [searchTerm, setSearchTerm] = useState('');

  const schemasInfo = [
    {
      id: 'schema_usuarios',
      name: 'schema_usuarios',
      tables: ['roles', 'usuarios', 'perfiles_profesionales'],
      records: workers.length + 15,
      description: 'Gestión de autenticación JWT, RBAC y datos personales.',
    },
    {
      id: 'schema_servicios',
      name: 'schema_servicios',
      tables: ['categorias', 'servicios'],
      records: 38,
      description: 'Catálogo de oficios, tarifas de referencia y soft-deletes.',
    },
    {
      id: 'schema_solicitudes',
      name: 'schema_solicitudes',
      tables: ['solicitudes_servicio'],
      records: requests.length,
      description: 'Máquina de estados de los camellos agendados en Neiva.',
    },
    {
      id: 'schema_reputacion',
      name: 'schema_reputacion',
      tables: ['calificaciones', 'registro_auditoria'],
      records: auditLogs.length + 24,
      description: 'Promedios de estrellas y trazabilidad de eventos del sistema.',
    },
  ];

  return (
    <div className="space-y-6">
      {/* Header Banner */}
      <div className="bg-gradient-to-r from-[#12263F] via-[#1a385c] to-[#12263F] text-white rounded-2xl p-6 shadow-md border border-[#E3DFD7]">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div className="space-y-1">
            <div className="flex items-center gap-2">
              <span className="px-2 py-0.5 bg-emerald-500 text-white text-[11px] font-extrabold rounded-md uppercase tracking-wider">
                Panel ADSO Activo
              </span>
              <span className="text-xs text-amber-300 font-semibold">
                SENA Regional Huila • Ficha 3413988
              </span>
            </div>
            <h1 className="text-xl sm:text-2xl font-black">
              Auditoría Técnica & Moderación
            </h1>
            <p className="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
              Monitoreo de microservicios REST, aislamiento de esquemas PostgreSQL en Docker y validación de antecedentes y certificaciones SENA de los técnicos independientes.
            </p>
          </div>

          <div className="bg-white/10 backdrop-blur-xs rounded-xl p-3 border border-white/20 text-center shrink-0">
            <span className="text-[10px] text-slate-300 uppercase block font-semibold">
              Estado Microservicios
            </span>
            <div className="flex items-center gap-2 justify-center mt-1">
              <span className="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse" />
              <span className="font-extrabold text-xs text-white">4 / 4 ONLINE</span>
            </div>
            <span className="text-[10px] text-emerald-300 mt-0.5 block">Docker PostgreSQL 15</span>
          </div>
        </div>
      </div>

      {/* PostgreSQL Isolated Schemas Inspector (TRD Section 2) */}
      <div className="bg-white rounded-2xl border border-[#E3DFD7] p-5 shadow-xs space-y-4">
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-base sm:text-lg font-bold text-[#12263F] flex items-center gap-2">
              <Database className="w-5 h-5 text-[#D96528]" />
              <span>Aislamiento de Esquemas PostgreSQL (Docker Compose)</span>
            </h2>
            <p className="text-xs text-[#606D7B]">
              Cumplimiento del requerimiento TRD: Instancia única optimizada con 4 esquemas desacoplados.
            </p>
          </div>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          {schemasInfo.map((sch) => (
            <div
              key={sch.id}
              onClick={() => setActiveSchemaTab(sch.id)}
              className={`p-3.5 rounded-xl border transition-all cursor-pointer ${
                activeSchemaTab === sch.id
                  ? 'bg-amber-50/60 border-[#D96528] shadow-xs'
                  : 'bg-[#FBF9F6] border-[#E3DFD7] hover:border-gray-400'
              }`}
            >
              <div className="flex items-center justify-between">
                <span className="font-mono text-xs font-bold text-[#12263F]">
                  {sch.name}
                </span>
                <span className="text-[10px] bg-white border border-[#E3DFD7] px-1.5 py-0.5 rounded font-bold text-[#606D7B]">
                  {sch.records} regs
                </span>
              </div>
              <p className="text-[11px] text-[#606D7B] mt-1.5 leading-snug">
                {sch.description}
              </p>
              <div className="mt-2.5 pt-2 border-t border-[#E3DFD7]/80 flex flex-wrap gap-1">
                {sch.tables.map((tbl) => (
                  <span
                    key={tbl}
                    className="text-[9px] font-mono bg-white px-1.5 py-0.5 rounded border border-[#E3DFD7] text-[#12263F]"
                  >
                    {tbl}
                  </span>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Technician Moderation Table (PRD Módulo 2) */}
      <div className="bg-white rounded-2xl border border-[#E3DFD7] p-5 shadow-xs space-y-4">
        <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <h2 className="text-base sm:text-lg font-bold text-[#12263F] flex items-center gap-2">
              <Users className="w-5 h-5 text-[#D96528]" />
              <span>Moderación de Trabajadores y Certificaciones SENA</span>
            </h2>
            <p className="text-xs text-[#606D7B]">
              Verificación de idoneidad técnica en Neiva y control de suspensión.
            </p>
          </div>

          <div className="relative w-full sm:w-60">
            <Search className="w-3.5 h-3.5 text-[#606D7B] absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              placeholder="Buscar trabajador o barrio..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="w-full bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl pl-8 pr-3 py-1.5 text-xs text-[#12263F] focus:outline-none focus:border-[#D96528]"
            />
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs border-collapse">
            <thead>
              <tr className="border-b border-[#E3DFD7] bg-[#FBF9F6] text-[#606D7B]">
                <th className="py-2.5 px-3 font-bold">Trabajador</th>
                <th className="py-2.5 px-3 font-bold">Especialidad</th>
                <th className="py-2.5 px-3 font-bold">Barrio en Neiva</th>
                <th className="py-2.5 px-3 font-bold">Acreditación SENA</th>
                <th className="py-2.5 px-3 font-bold">Calificación</th>
                <th className="py-2.5 px-3 font-bold text-right">Acción Moderador</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-[#E3DFD7]/80">
              {workers
                .filter(
                  (w) =>
                    w.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                    w.neighborhood.toLowerCase().includes(searchTerm.toLowerCase())
                )
                .map((w) => (
                  <tr key={w.id} className="hover:bg-[#FBF9F6]/80 transition">
                    <td className="py-3 px-3">
                      <div className="flex items-center gap-2.5">
                        <img
                          src={w.avatar}
                          alt={w.name}
                          className="w-8 h-8 rounded-full object-cover border border-[#E5A93C]"
                        />
                        <div>
                          <span className="font-bold text-[#12263F] block">{w.name}</span>
                          <span className="text-[10px] text-[#606D7B]">{w.phone}</span>
                        </div>
                      </div>
                    </td>
                    <td className="py-3 px-3 font-medium text-[#12263F]">{w.specialty}</td>
                    <td className="py-3 px-3 text-[#606D7B]">{w.neighborhood}</td>
                    <td className="py-3 px-3">
                      {w.senaCertified ? (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-[#7b5500] font-bold text-[10px]">
                          <Award className="w-3 h-3 text-[#E5A93C]" />
                          Verificado CIES
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 text-[10px]">
                          Sin verificar
                        </span>
                      )}
                    </td>
                    <td className="py-3 px-3">
                      <span className="font-bold text-[#12263F]">★ {w.rating.toFixed(1)}</span>
                      <span className="text-[10px] text-[#606D7B] ml-1">({w.completedJobs} trab.)</span>
                    </td>
                    <td className="py-3 px-3 text-right">
                      <button
                        type="button"
                        onClick={() => onToggleVerification(w.id)}
                        className={`px-2.5 py-1 rounded-lg text-[11px] font-bold transition cursor-pointer ${
                          w.senaCertified
                            ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200'
                            : 'bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs'
                        }`}
                      >
                        {w.senaCertified ? 'Suspender Insignia' : 'Aprobar Certificado'}
                      </button>
                    </td>
                  </tr>
                ))}
            </tbody>
          </table>
        </div>
      </div>

      {/* System Audit Logs (TRD Section 4) */}
      <div className="bg-white rounded-2xl border border-[#E3DFD7] p-5 shadow-xs space-y-3">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Activity className="w-4 h-4 text-emerald-600" />
            <h3 className="text-sm font-bold text-[#12263F] uppercase tracking-wider">
              Trazabilidad y Registro de Auditoría (schema_reputacion.registro_auditoria)
            </h3>
          </div>
          <span className="text-xs text-[#606D7B]">{auditLogs.length} eventos registrados</span>
        </div>

        <div className="space-y-2">
          {auditLogs.map((log) => (
            <div
              key={log.id}
              className="p-3 bg-[#FBF9F6] border border-[#E3DFD7] rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs"
            >
              <div className="flex items-start sm:items-center gap-2.5">
                <span className="px-2 py-0.5 bg-[#12263F] text-white font-mono text-[10px] rounded font-bold">
                  {log.action}
                </span>
                <span className="font-mono text-[11px] text-[#606D7B]">[{log.schema}]</span>
                <p className="text-[#12263F] font-medium">{log.details}</p>
              </div>

              <div className="flex items-center gap-2 text-[10px] text-[#606D7B] shrink-0 self-end sm:self-center">
                <span>Por: <strong className="text-[#12263F]">{log.user}</strong></span>
                <span>•</span>
                <span>{log.timestamp}</span>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
};
