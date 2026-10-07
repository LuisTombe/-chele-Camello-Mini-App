import React from 'react';
import { Category } from '../types';
import { 
  Wrench, 
  Zap, 
  Wind, 
  Key, 
  Paintbrush, 
  Hammer, 
  Bike, 
  Scissors, 
  Layers 
} from 'lucide-react';

interface SpecialtyChipsProps {
  categories: Category[];
  selectedCategory: string | null;
  onSelectCategory: (categoryId: string | null) => void;
}

export const SpecialtyChips: React.FC<SpecialtyChipsProps> = ({
  categories,
  selectedCategory,
  onSelectCategory,
}) => {
  const getCategoryIcon = (iconName: string) => {
    switch (iconName) {
      case 'Wrench': return Wrench;
      case 'Zap': return Zap;
      case 'Wind': return Wind;
      case 'Key': return Key;
      case 'Paintbrush': return Paintbrush;
      case 'Hammer': return Hammer;
      case 'Bike': return Bike;
      case 'Scissors': return Scissors;
      default: return Wrench;
    }
  };

  return (
    <div className="w-full overflow-x-auto pb-2 scrollbar-none">
      <div className="flex items-center gap-2 min-w-max">
        {/* All / Todos Chip */}
        <button
          type="button"
          onClick={() => onSelectCategory(null)}
          className={`flex items-center gap-2 px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all duration-200 cursor-pointer ${
            selectedCategory === null
              ? 'bg-[#12263F] text-white border border-[#12263F] shadow-sm'
              : 'bg-white text-[#606D7B] border border-[#E3DFD7] hover:border-[#12263F] hover:text-[#12263F]'
          }`}
        >
          <Layers className="w-3.5 h-3.5" />
          <span>Todos los Oficios</span>
        </button>

        {/* Dynamic Category Chips */}
        {categories.map((cat) => {
          const Icon = getCategoryIcon(cat.iconName);
          const isSelected = selectedCategory === cat.id;
          return (
            <button
              key={cat.id}
              type="button"
              onClick={() => onSelectCategory(isSelected ? null : cat.id)}
              className={`flex items-center gap-2 px-4 py-2 rounded-full text-xs sm:text-sm font-semibold transition-all duration-200 cursor-pointer ${
                isSelected
                  ? 'bg-[#12263F] text-white border border-[#12263F] shadow-sm'
                  : 'bg-white text-[#606D7B] border border-[#E3DFD7] hover:border-[#12263F] hover:text-[#12263F]'
              }`}
            >
              <Icon className={`w-3.5 h-3.5 ${isSelected ? 'text-[#E5A93C]' : 'text-[#606D7B]'}`} />
              <span>{cat.name}</span>
              <span className={`text-[10px] px-1.5 py-0.5 rounded-full ${
                isSelected ? 'bg-white/20 text-white' : 'bg-[#FBF9F6] text-[#606D7B]'
              }`}>
                {cat.technicianCount}
              </span>
            </button>
          );
        })}
      </div>
    </div>
  );
};
