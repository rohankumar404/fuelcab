import { Flame, Package, Truck, ShieldCheck } from "lucide-react";

export default function HeroFuelCard() {
  return (
    <div className="glass-dark rounded-[20px] p-5 w-[240px] shadow-2xl animate-float-card">
      {/* Header */}
      <div className="flex items-start justify-between mb-4">
        <div className="flex items-center gap-2.5">
          <div className="w-10 h-10 rounded-xl bg-[#ffb400]/20 flex items-center justify-center flex-shrink-0">
            <Flame className="w-5 h-5 text-[#ffb400] fill-[#ffb400]" />
          </div>
          <div>
            <p className="text-[10px] text-gray-300 uppercase font-bold tracking-widest leading-none mb-0.5">
              Our Top Priority
            </p>
            <h4 className="text-white font-bold text-base leading-tight tracking-tight">
              DIESEL
            </h4>
          </div>
        </div>
      </div>

      {/* Description */}
      <p className="text-gray-300 text-[11px] leading-relaxed mb-5">
        High quality diesel for uninterrupted performance of your business.
      </p>

      {/* CTA Row */}
      <a
        href="#products"
        className="flex items-center justify-between text-[#33b248] text-[11px] font-semibold hover:text-white transition-colors duration-200 group"
      >
        <span>Order Diesel Now</span>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          className="w-4 h-4 group-hover:translate-x-1 transition-transform duration-200"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          strokeWidth={2.5}
        >
          <path strokeLinecap="round" strokeLinejoin="round" d="M9 5l7 7-7 7" />
        </svg>
      </a>
    </div>
  );
}
