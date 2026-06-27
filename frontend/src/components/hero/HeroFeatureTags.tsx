import { Package, Truck, ShieldCheck } from "lucide-react";

const features = [
  {
    icon: <Package className="w-5 h-5 text-[#155c32]" />,
    label: "Min. Order",
    value: "100 Liters",
  },
  {
    icon: <Truck className="w-5 h-5 text-[#155c32]" />,
    label: "Fast Delivery",
    value: "On-Time, Safely",
  },
  {
    icon: <ShieldCheck className="w-5 h-5 text-[#155c32]" />,
    label: "Quality",
    value: "Assured Fuel",
  },
];

export default function HeroFeatureTags() {
  return (
    <div className="grid grid-cols-3 gap-x-6 gap-y-4 pt-8 border-t border-[#e7ece8] w-full max-w-[480px]">
      {features.map(({ icon, label, value }) => (
        <div key={label} className="flex flex-col gap-1.5">
          <div className="flex items-center gap-1.5">
            {icon}
            <span className="text-[10px] font-bold uppercase tracking-wider text-[#1a1a1a]">
              {label}
            </span>
          </div>
          <span className="text-xs text-[#555555]">{value}</span>
        </div>
      ))}
    </div>
  );
}
