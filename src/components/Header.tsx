import React from 'react';
import { Role } from '../types';
import { 
  Flame, 
  MapPin, 
  Briefcase, 
  UserCheck, 
  ShieldCheck, 
  Smartphone, 
  Monitor, 
  Search,
  CheckCircle,
  Menu,
  X
} from 'lucide-react';

interface HeaderProps {
  currentRole: Role;
  onChangeRole: (role: Role) => void;
  activeTab: 'explorar' | 'solicitudes' | 'trabajador' | 'admin';
  onChangeTab: (tab: 'explorar' | 'solicitudes' | 'trabajador' | 'admin') => void;
  pendingRequestsCount: number;
  selectedNeighborhood: string;
  onChangeNeighborhood: (n: string) => void;
  neighborhoods: string[];
  isMobileDeviceFrame: boolean;
  onToggleDeviceFrame: () => void;
}

export const Header: React.FC<HeaderProps> = ({
  currentRole,
  onChangeRole,
  activeTab,
  onChangeTab,
  pendingRequestsCount,
  selectedNeighborhood,
  onChangeNeighborhood,
  neighborhoods,
  isMobileDeviceFrame,
  onToggleDeviceFrame,
}) => {
  const [mobileMenuOpen, setMobileMenuOpen] = React.useState(false);

  return (
    <header className="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-[#E3DFD7] shadow-xs">
      {/* Top Banner: SENA ADSO reference & Quick Switcher */}
      <div className="bg-[#12263F] text-white text-xs px-3 sm:px-6 py-1.5 flex flex-wrap items-center justify-between gap-2">
        <div className="flex items-center gap-2">
          <span className="inline-block px-1.5 py-0.5 bg-[#D96528] rounded font-bold text-[10px] tracking-wider uppercase">
            SENA ADSO
          </span>
          <span className="hidden sm:inline text-white/80">
            Ficha 3413988 • Intermediación Laboral Neiva, Huila
          </span>
          <span className="sm:hidden text-white/80 truncate">
            Neiva, Huila • ADSO 3413988
          </span>
        </div>

        {/* Device Frame Viewport Toggle & Current Role indicator */}
        <div className="flex items-center gap-3 ml-auto">
          <button
            type="button"
            onClick={onToggleDeviceFrame}
            className="flex items-center gap-1 px-2 py-0.5 rounded bg-white/10 hover:bg-white/20 text-white/90 text-[11px] font-medium transition cursor-pointer"
            title="Alternar entre simulación móvil Android y vista web escritorio"
          >
            {isMobileDeviceFrame ? (
              <>
                <Monitor className="w-3 h-3 text-[#E5A93C]" />
                <span className="hidden xs:inline">Ver Web Completa</span>
              </>
            ) : (
              <>
                <Smartphone className="w-3 h-3 text-[#E5A93C]" />
                <span className="hidden xs:inline">Simular Móvil Android</span>
              </>
            )}
          </button>

          {/* Quick Role Switch Pills */}
          <div className="flex items-center bg-black/30 p-0.5 rounded-full border border-white/10">
            <button
              type="button"
              onClick={() => {
                onChangeRole('CLIENTE');
                if (activeTab === 'trabajador' || activeTab === 'admin') onChangeTab('explorar');
              }}
              className={`px-2 py-0.5 rounded-full text-[10px] font-semibold transition cursor-pointer ${
                currentRole === 'CLIENTE'
                  ? 'bg-[#D96528] text-white shadow-xs'
                  : 'text-white/70 hover:text-white'
              }`}
            >
              Cliente
            </button>
            <button
              type="button"
              onClick={() => {
                onChangeRole('TRABAJADOR');
                onChangeTab('trabajador');
              }}
              className={`px-2 py-0.5 rounded-full text-[10px] font-semibold transition cursor-pointer ${
                currentRole === 'TRABAJADOR'
                  ? 'bg-[#E5A93C] text-[#12263F] shadow-xs'
                  : 'text-white/70 hover:text-white'
              }`}
            >
              Trabajador
            </button>
            <button
              type="button"
              onClick={() => {
                onChangeRole('ADMINISTRADOR');
                onChangeTab('admin');
              }}
              className={`px-2 py-0.5 rounded-full text-[10px] font-semibold transition cursor-pointer ${
                currentRole === 'ADMINISTRADOR'
                  ? 'bg-emerald-600 text-white shadow-xs'
                  : 'text-white/70 hover:text-white'
              }`}
            >
              Admin
            </button>
          </div>
        </div>
      </div>

      {/* Main Navbar */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
        {/* Brand / Logo */}
        <div 
          onClick={() => onChangeTab('explorar')}
          className="flex items-center gap-2.5 cursor-pointer group select-none"
        >
          <div className="w-10 h-10 rounded-xl bg-gradient-to-br from-[#D96528] to-[#C25319] flex items-center justify-center text-white shadow-md shadow-[#D96528]/20 group-hover:scale-105 transition-transform">
            <Flame className="w-6 h-6 fill-amber-300 text-white" />
          </div>
          <div>
            <div className="flex items-center gap-1.5">
              <span className="font-extrabold text-lg sm:text-xl text-[#12263F] tracking-tight">
                Échele <span className="text-[#D96528]">Camello</span>
              </span>
            </div>
            <p className="text-[11px] font-medium text-[#606D7B] leading-none flex items-center gap-1">
              <span>Manos a la obra en Neiva</span>
            </p>
          </div>
        </div>

        {/* Neighborhood Selector */}
        <div className="hidden md:flex items-center gap-1.5 bg-[#FBF9F6] border border-[#E3DFD7] px-3 py-1.5 rounded-xl text-xs">
          <MapPin className="w-3.5 h-3.5 text-[#D96528]" />
          <span className="text-[#606D7B] font-medium">Ubicación:</span>
          <select
            value={selectedNeighborhood}
            onChange={(e) => onChangeNeighborhood(e.target.value)}
            aria-label="Seleccionar barrio de Neiva"
            className="bg-transparent font-semibold text-[#12263F] focus:outline-none cursor-pointer"
          >
            <option value="todos">Toda Neiva (13 Barrios)</option>
            {neighborhoods.map((n) => (
              <option key={n} value={n}>
                Barrio {n}
              </option>
            ))}
          </select>
        </div>

        {/* Navigation Tabs */}
        <nav className="hidden sm:flex items-center gap-1 lg:gap-2">
          <button
            type="button"
            onClick={() => onChangeTab('explorar')}
            className={`flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition cursor-pointer ${
              activeTab === 'explorar'
                ? 'bg-[#12263F] text-white shadow-xs'
                : 'text-[#606D7B] hover:text-[#12263F] hover:bg-[#FBF9F6]'
            }`}
          >
            <Search className="w-4 h-4" />
            <span>Buscar Camellador</span>
          </button>

          <button
            type="button"
            onClick={() => onChangeTab('solicitudes')}
            className={`flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition cursor-pointer relative ${
              activeTab === 'solicitudes'
                ? 'bg-[#12263F] text-white shadow-xs'
                : 'text-[#606D7B] hover:text-[#12263F] hover:bg-[#FBF9F6]'
            }`}
          >
            <Briefcase className="w-4 h-4" />
            <span>Mis Camellos</span>
            {pendingRequestsCount > 0 && (
              <span className="inline-flex items-center justify-center min-w-5 h-5 px-1 bg-[#D96528] text-white text-[10px] font-bold rounded-full animate-bounce">
                {pendingRequestsCount}
              </span>
            )}
          </button>

          {currentRole === 'TRABAJADOR' && (
            <button
              type="button"
              onClick={() => onChangeTab('trabajador')}
              className={`flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition cursor-pointer ${
                activeTab === 'trabajador'
                  ? 'bg-[#D96528] text-white shadow-xs'
                  : 'text-[#606D7B] hover:text-[#12263F] hover:bg-[#FBF9F6]'
              }`}
            >
              <UserCheck className="w-4 h-4" />
              <span>Mi Perfil & Servicios</span>
            </button>
          )}

          {currentRole === 'ADMINISTRADOR' && (
            <button
              type="button"
              onClick={() => onChangeTab('admin')}
              className={`flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs sm:text-sm font-semibold transition cursor-pointer ${
                activeTab === 'admin'
                  ? 'bg-emerald-700 text-white shadow-xs'
                  : 'text-[#606D7B] hover:text-[#12263F] hover:bg-[#FBF9F6]'
              }`}
            >
              <ShieldCheck className="w-4 h-4" />
              <span>Auditoría ADSO</span>
            </button>
          )}
        </nav>

        {/* Mobile Hamburger Button */}
        <button
          type="button"
          onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
          aria-label={mobileMenuOpen ? 'Cerrar menú' : 'Abrir menú'}
          className="sm:hidden p-2 rounded-lg text-[#12263F] hover:bg-[#FBF9F6] border border-[#E3DFD7]"
        >
          {mobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
        </button>
      </div>

      {/* Mobile Drawer Menu */}
      {mobileMenuOpen && (
        <div className="sm:hidden bg-white border-b border-[#E3DFD7] px-4 py-3 space-y-2 animate-in slide-in-from-top-2">
          <div className="p-2 bg-[#FBF9F6] rounded-lg border border-[#E3DFD7] mb-2">
            <label className="block text-[11px] font-medium text-[#606D7B] mb-1">
              Barrio en Neiva:
            </label>
            <select
              value={selectedNeighborhood}
              onChange={(e) => {
                onChangeNeighborhood(e.target.value);
                setMobileMenuOpen(false);
              }}
              aria-label="Seleccionar barrio de Neiva en menú móvil"
              className="w-full bg-white border border-[#E3DFD7] rounded-md py-1.5 px-2 text-xs font-semibold text-[#12263F]"
            >
              <option value="todos">Toda Neiva (13 Barrios)</option>
              {neighborhoods.map((n) => (
                <option key={n} value={n}>
                  Barrio {n}
                </option>
              ))}
            </select>
          </div>

          <button
            type="button"
            onClick={() => {
              onChangeTab('explorar');
              setMobileMenuOpen(false);
            }}
            className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold ${
              activeTab === 'explorar' ? 'bg-[#12263F] text-white' : 'text-[#606D7B]'
            }`}
          >
            <span className="flex items-center gap-2">
              <Search className="w-4 h-4" />
              Explorar Técnicos
            </span>
          </button>

          <button
            type="button"
            onClick={() => {
              onChangeTab('solicitudes');
              setMobileMenuOpen(false);
            }}
            className={`w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold ${
              activeTab === 'solicitudes' ? 'bg-[#12263F] text-white' : 'text-[#606D7B]'
            }`}
          >
            <span className="flex items-center gap-2">
              <Briefcase className="w-4 h-4" />
              Mis Camellos
            </span>
            {pendingRequestsCount > 0 && (
              <span className="px-2 py-0.5 bg-[#D96528] text-white text-xs rounded-full">
                {pendingRequestsCount}
              </span>
            )}
          </button>

          {currentRole === 'TRABAJADOR' && (
            <button
              type="button"
              onClick={() => {
                onChangeTab('trabajador');
                setMobileMenuOpen(false);
              }}
              className={`w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold ${
                activeTab === 'trabajador' ? 'bg-[#D96528] text-white' : 'text-[#606D7B]'
              }`}
            >
              <UserCheck className="w-4 h-4" />
              Mi Perfil & Catálogo
            </button>
          )}

          {currentRole === 'ADMINISTRADOR' && (
            <button
              type="button"
              onClick={() => {
                onChangeTab('admin');
                setMobileMenuOpen(false);
              }}
              className={`w-full flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold ${
                activeTab === 'admin' ? 'bg-emerald-700 text-white' : 'text-[#606D7B]'
              }`}
            >
              <ShieldCheck className="w-4 h-4" />
              Panel de Auditoría ADSO
            </button>
          )}
        </div>
      )}
    </header>
  );
};
