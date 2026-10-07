import React from 'react';
import { Wifi, Battery, Signal, ArrowLeft } from 'lucide-react';

interface DeviceFrameProps {
  isMobileDevice: boolean;
  children: React.ReactNode;
}

export const DeviceFrame: React.FC<DeviceFrameProps> = ({ isMobileDevice, children }) => {
  if (!isMobileDevice) {
    return <div className="w-full min-h-screen">{children}</div>;
  }

  return (
    <div className="min-h-screen bg-[#1c2833] py-6 sm:py-10 px-2 sm:px-4 flex items-center justify-center">
      {/* Android Smartphone Container */}
      <div className="w-full max-w-[425px] h-[890px] bg-white rounded-[44px] shadow-[0_25px_60px_-15px_rgba(0,0,0,0.5)] border-[10px] border-[#12263F] overflow-hidden flex flex-col relative ring-1 ring-white/20">
        {/* Android Punch Hole & Status Bar */}
        <div className="bg-[#12263F] text-white px-6 pt-3 pb-2 flex items-center justify-between text-xs select-none shrink-0 z-40">
          <div className="text-[11px] font-bold tracking-tight">15:30</div>
          
          {/* Camera notch */}
          <div className="w-4 h-4 rounded-full bg-black/60 border border-white/10 shrink-0" />

          {/* Android status icons */}
          <div className="flex items-center gap-1.5 text-white/90">
            <Signal className="w-3 h-3" />
            <Wifi className="w-3 h-3" />
            <Battery className="w-3.5 h-3.5 fill-current" />
          </div>
        </div>

        {/* Inner App Content */}
        <div className="flex-1 overflow-y-auto bg-[#FBF9F6] relative">
          {children}
        </div>

        {/* Android Gesture Navigation Bar */}
        <div className="bg-white/95 backdrop-blur-md py-2 flex items-center justify-center shrink-0 border-t border-[#E3DFD7]">
          <div className="w-28 h-1 bg-[#12263F]/40 rounded-full" />
        </div>
      </div>
    </div>
  );
};
