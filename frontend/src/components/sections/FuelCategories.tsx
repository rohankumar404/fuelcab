"use client";

import React from "react";
import { motion } from "framer-motion";
import {
  Droplet,
  Fuel,
  Wind,
  Flame,
  ShieldCheck,
  Wrench,
  Gauge,
  ArrowRight,
} from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

interface FuelProduct {
  id: string;
  name: string;
  description: string;
  status: "active" | "soon";
  minOrder?: string;
  icon: React.ComponentType<{ className?: string }>;
  colorClass: string;
  badgeText?: string;
}

const FUEL_PRODUCTS: FuelProduct[] = [
  {
    id: "diesel",
    name: "Diesel (HSD equivalent)",
    description: "Ultra-low sulfur premium diesel optimized for heavy machinery, generators, and logistics fleets.",
    status: "active",
    minOrder: "100 Liters",
    icon: Droplet,
    colorClass: "from-[#ffb400] to-[#ff7b00]",
    badgeText: "Most Popular",
  },
  {
    id: "cng",
    name: "CNG",
    description: "Compressed Natural Gas for eco-friendly public transport systems and green logistics operations.",
    status: "soon",
    icon: Wind,
    colorClass: "from-[#00b4d8] to-[#0077b6]",
    badgeText: "Coming Soon",
  },
  {
    id: "lpg",
    name: "LPG",
    description: "Liquefied Petroleum Gas for high-efficiency industrial heating and commercial kitchen systems.",
    status: "soon",
    icon: Flame,
    colorClass: "from-[#ef476f] to-[#d90429]",
    badgeText: "Coming Soon",
  },
  {
    id: "def",
    name: "DEF (AdBlue)",
    description: "Premium Diesel Exhaust Fluid maintaining strict emission compliance in modern SCR diesel engines.",
    status: "soon",
    icon: ShieldCheck,
    colorClass: "from-[#4ea8de] to-[#5390d9]",
    badgeText: "Coming Soon",
  },
  {
    id: "lubricants",
    name: "Lubricants",
    description: "High-grade engine oils, transmission fluids, and greases protecting heavy industrial assets.",
    status: "soon",
    icon: Wrench,
    colorClass: "from-[#ff70a6] to-[#ff9770]",
    badgeText: "Coming Soon",
  },
];

export default function FuelCategories() {
  return (
    <section
      id="products"
      className="py-24 bg-white border-t border-[#e7ece8] relative overflow-hidden"
      aria-label="Fuel Categories Range"
    >
      {/* Background decorations */}
      <div className="absolute inset-0 z-0 pointer-events-none opacity-30">
        <div className="absolute top-1/2 left-0 w-[400px] h-[400px] rounded-full bg-[#33b248]/5 blur-3xl" />
        <div className="absolute top-1/4 right-0 w-[400px] h-[400px] rounded-full bg-[#155c32]/5 blur-3xl" />
      </div>

      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">
        
        {/* Section Header */}
        <div className="text-center max-w-[650px] mx-auto mb-16">
          <motion.span
            initial={{ opacity: 0, y: 10 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-xs font-bold uppercase tracking-widest text-[#33b248] mb-3 block"
          >
            Our Fuel Range
          </motion.span>
          <motion.h2
            initial={{ opacity: 0, y: 15 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.1 }}
            className="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1a1a1a] mb-4"
          >
            Powering Every Business Need
          </motion.h2>
          <motion.p
            initial={{ opacity: 0, y: 15 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.2 }}
            className="text-[#555555] text-sm sm:text-base leading-relaxed"
          >
            While Diesel is our primary high-demand delivery product, we are actively expanding to support all major commercial fuels.
          </motion.p>
        </div>

        {/* Swipe indicator for Mobile */}
        <div className="flex md:hidden items-center justify-center gap-2 mb-4 text-[#555555] text-xs">
          <span>Swipe to view all categories</span>
          <ArrowRight className="w-3 h-3 animate-pulse" />
        </div>

        {/* Carousel / Grid Container */}
        <div 
          className="flex overflow-x-auto snap-x snap-mandatory gap-6 pb-6 md:grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 scrollbar-hide md:overflow-visible"
          role="region"
          aria-label="Fuel categories list"
        >
          {FUEL_PRODUCTS.map((product, index) => {
            const Icon = product.icon;
            const isActive = product.status === "active";

            return (
              <motion.div
                key={product.id}
                initial={{ opacity: 0, y: 24 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: index * 0.08, duration: 0.5 }}
                className="snap-center shrink-0 w-[290px] md:w-auto"
              >
                <div
                  className={cn(
                    "relative flex flex-col justify-between h-[420px] rounded-[22px] p-6 transition-all duration-300 group border text-left",
                    isActive
                      ? "bg-[#0d3a1f] text-white border-[#155c32] shadow-xl shadow-[#155c32]/10"
                      : "bg-[#ffffff] text-[#1a1a1a] border-[#e7ece8] hover:border-[#33b248] hover:shadow-lg"
                  )}
                  style={{
                    boxShadow: isActive ? "0 15px 40px -10px rgba(21, 92, 50, 0.3)" : undefined,
                  }}
                >
                  {/* Glowing background highlights on hover */}
                  {!isActive && (
                    <div className="absolute inset-0 rounded-[22px] bg-gradient-to-br from-[#33b248]/3 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none" />
                  )}

                  {/* Header Row */}
                  <div>
                    <div className="flex justify-between items-start mb-6">
                      <div
                        className={cn(
                          "w-12 h-12 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110",
                          isActive ? "bg-white/10" : "bg-[#f4f8f5]"
                        )}
                      >
                        {/* 3D-effect icon drop gradient */}
                        <div className={cn("p-2 rounded-lg bg-gradient-to-br shadow-inner", product.colorClass)}>
                          <Icon className={cn("w-6 h-6 text-white stroke-2")} />
                        </div>
                      </div>

                      {/* Status Badges */}
                      <span
                        className={cn(
                          "text-[9px] font-extrabold uppercase px-2.5 py-1 rounded-full tracking-wider",
                          isActive
                            ? "bg-[#ffb400] text-[#0d3a1f] shadow-sm shadow-[#ffb400]/40"
                            : "bg-[#e7ece8] text-[#555555]"
                        )}
                      >
                        {product.badgeText}
                      </span>
                    </div>

                    {/* Fuel Name */}
                    <h3 className="text-lg font-bold mb-3 tracking-tight">
                      {product.name}
                    </h3>

                    {/* Description */}
                    <p
                      className={cn(
                        "text-xs leading-relaxed line-clamp-4",
                        isActive ? "text-gray-200" : "text-[#555555]"
                      )}
                    >
                      {product.description}
                    </p>
                  </div>

                  {/* Footer Stats / Action Button */}
                  <div className="mt-6 border-t pt-4 border-dashed border-gray-100/10">
                    {/* Minimum Order Limit */}
                    {isActive && product.minOrder && (
                      <div className="flex items-center justify-between mb-4 text-xs">
                        <span className="text-gray-300">Minimum Order</span>
                        <span className="font-semibold text-[#ffb400]">{product.minOrder}</span>
                      </div>
                    )}

                    {/* CTA Button */}
                    <a
                      href={isActive ? "/order" : "#soon"}
                      aria-disabled={!isActive}
                      onClick={(e) => !isActive && e.preventDefault()}
                      className={cn(
                        buttonVariants({
                          variant: isActive ? "default" : "outline",
                        }),
                        "w-full h-11 rounded-xl font-bold text-xs flex items-center justify-center gap-1.5 transition-all duration-300",
                        isActive
                          ? "bg-white text-[#0d3a1f] hover:bg-[#ffb400] hover:text-[#0d3a1f] hover:shadow-lg"
                          : "border-[#e7ece8] text-gray-400 bg-gray-50/50 cursor-not-allowed"
                      )}
                    >
                      {isActive ? (
                        <>
                          <span>Order Fuel Now</span>
                          <ArrowRight className="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" />
                        </>
                      ) : (
                        <span>Notify When Available</span>
                      )}
                    </a>
                  </div>
                </div>
              </motion.div>
            );
          })}
        </div>

      </div>
    </section>
  );
}
