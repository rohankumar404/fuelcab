"use client";

import React from "react";
import { motion } from "framer-motion";
import Link from "next/link";
import { ArrowRight, Flame, Droplet, Wind, Zap, CheckCircle2 } from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

interface PromoCategory {
  id: string;
  title: string;
  description: string;
  icon: React.ComponentType<{ className?: string }>;
  fuels: string[];
  status: "active" | "soon";
  colorClass: string;
}

const MARKETPLACE_CATEGORIES: PromoCategory[] = [
  {
    id: "solid",
    title: "Solid Fuels",
    description: "Eco-friendly solid energy alternatives with high calorific value.",
    icon: Flame,
    fuels: ["Biomass Briquettes", "Bio Coal", "Rice Husk", "Wood Chips", "Coffee Husk"],
    status: "active",
    colorClass: "from-[#ef476f] to-[#d90429]",
  },
  {
    id: "liquid",
    title: "Liquid Fuels",
    description: "Premium bio-fuels, diesel replacements, and heavy commercial furnace oils.",
    icon: Droplet,
    fuels: ["Bio Diesel (B-100)", "Furnace Oil", "LDO / Bio-LDO", "Base Oil", "Acid Oil"],
    status: "active",
    colorClass: "from-[#00b4d8] to-[#0077b6]",
  },
  {
    id: "gas",
    title: "Gas Fuels",
    description: "High-efficiency natural gases and gaseous fuel alternatives.",
    icon: Wind,
    fuels: ["CNG", "LPG", "Bio-CNG / CBG", "LNG", "Industrial Gases"],
    status: "active",
    colorClass: "from-[#4ea8de] to-[#5390d9]",
  },
  {
    id: "ev",
    title: "EV Charging",
    description: "Advanced fleet charging infrastructure and electric mobility services.",
    icon: Zap,
    fuels: ["Coming Soon", "Fleet Solutions", "DC Fast Chargers", "AC Charging Hubs"],
    status: "soon",
    colorClass: "from-[#33b248] to-[#155c32]",
  },
];

export default function MarketplacePromo() {
  return (
    <section
      id="marketplace-promo"
      className="py-24 bg-[#f4f8f5] border-t border-b border-[#e7ece8] relative overflow-hidden"
      aria-label="FuelCab Marketplace Highlight"
    >
      {/* Premium subtle mesh gradient overlay */}
      <div className="absolute inset-0 z-0 pointer-events-none opacity-40">
        <div className="absolute -top-1/4 -right-1/4 w-[600px] h-[600px] rounded-full bg-[#33b248]/10 blur-3xl" />
        <div className="absolute -bottom-1/4 -left-1/4 w-[600px] h-[600px] rounded-full bg-[#155c32]/10 blur-3xl" />
      </div>

      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">
        
        {/* Grid layout for Title/Description + Explore Actions */}
        <div className="grid lg:grid-cols-12 gap-12 lg:gap-16 items-center mb-16">
          <div className="lg:col-span-8 space-y-4">
            <motion.span
              initial={{ opacity: 0, y: 10 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              className="text-xs font-bold uppercase tracking-widest text-[#33b248] block"
            >
              FuelCab Marketplace
            </motion.span>
            
            <motion.h2
              initial={{ opacity: 0, y: 15 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.1 }}
              className="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight text-[#1a1a1a]"
            >
              One Marketplace.<br className="hidden sm:inline" /> Multiple Energy Solutions.
            </motion.h2>
            
            <motion.p
              initial={{ opacity: 0, y: 15 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: 0.2 }}
              className="text-[#555555] text-base max-w-3xl leading-relaxed"
            >
              Discover industrial fuels, alternative fuels, biomass, liquid fuels, and gas solutions from verified, compliance-approved suppliers across India. Enjoy streamlined commerce under one central transaction core.
            </motion.p>
          </div>

          {/* Action CTAs */}
          <div className="lg:col-span-4 flex flex-wrap gap-4 lg:justify-end">
            <motion.div
              initial={{ opacity: 0, scale: 0.95 }}
              whileInView={{ opacity: 1, scale: 1 }}
              viewport={{ once: true }}
              transition={{ delay: 0.3 }}
              className="flex flex-col sm:flex-row lg:flex-col xl:flex-row gap-4 w-full sm:w-auto"
            >
              <Link
                href="/marketplace"
                className={cn(
                  buttonVariants({ variant: "default", size: "lg" }),
                  "h-12 px-6 rounded-xl bg-[#155c32] text-white font-semibold text-sm hover:bg-[#0d3a1f] hover:shadow-lg hover:shadow-[#155c32]/25 transition-all duration-200 flex items-center justify-center gap-2 group/btn"
                )}
                aria-label="Explore energy solutions in the marketplace"
              >
                Explore Marketplace
                <ArrowRight className="w-4 h-4 transition-transform duration-200 group-hover/btn:translate-x-1" />
              </Link>
              
              <Link
                href="/vendor/register"
                className={cn(
                  buttonVariants({ variant: "outline", size: "lg" }),
                  "h-12 px-6 rounded-xl border-[#155c32]/35 text-[#155c32] font-semibold text-sm hover:border-[#155c32] hover:bg-[#155c32]/5 transition-all duration-200 flex items-center justify-center"
                )}
                aria-label="Onboard your company to sell on the marketplace"
              >
                Become a Vendor
              </Link>
            </motion.div>
          </div>
        </div>

        {/* Category Preview Grid */}
        <div className="grid sm:grid-cols-2 xl:grid-cols-4 gap-6">
          {MARKETPLACE_CATEGORIES.map((category, index) => {
            const Icon = category.icon;
            const isSoon = category.status === "soon";

            return (
              <motion.div
                key={category.id}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: index * 0.1, duration: 0.5 }}
                className={cn(
                  "bg-white rounded-2xl p-6 border border-[#e7ece8] hover:border-[#33b248] transition-all duration-300 hover:shadow-xl hover:shadow-[#155c32]/4 group relative flex flex-col justify-between h-[360px]",
                  isSoon && "opacity-85 hover:border-[#e7ece8] hover:shadow-none"
                )}
              >
                {/* Visual card header */}
                <div>
                  <div className="flex justify-between items-start mb-5">
                    <div className={cn("w-11 h-11 rounded-xl flex items-center justify-center bg-gradient-to-br shadow-sm", category.colorClass)}>
                      <Icon className="w-5 h-5 text-white stroke-2" />
                    </div>
                    
                    {isSoon && (
                      <span className="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-full tracking-wider bg-[#e7ece8] text-[#555555] border border-transparent">
                        Coming Soon
                      </span>
                    )}
                  </div>

                  <h3 className="text-lg font-bold text-[#1a1a1a] mb-2 group-hover:text-[#155c32] transition-colors duration-150">
                    {category.title}
                  </h3>
                  
                  <p className="text-xs text-[#555555] leading-relaxed mb-6">
                    {category.description}
                  </p>
                </div>

                {/* Sub items / featured items display */}
                <div className="border-t border-[#e7ece8] pt-4 mt-auto">
                  <span className="text-[10px] uppercase font-bold text-[#555555]/85 tracking-widest mb-2.5 block">
                    {isSoon ? "Solutions Roadmap" : "Popular Fuel Items"}
                  </span>
                  
                  <ul className="space-y-1.5" aria-label={`Subcategories under ${category.title}`}>
                    {category.fuels.slice(0, 3).map((fuel) => (
                      <li key={fuel} className="flex items-center gap-2 text-xs font-medium text-[#1a1a1a]">
                        <CheckCircle2 className={cn("w-3.5 h-3.5", isSoon ? "text-[#555555]/40" : "text-[#33b248]")} />
                        <span>{fuel}</span>
                      </li>
                    ))}
                  </ul>
                </div>

                {/* Arrow indicator for interactive cards */}
                {!isSoon && (
                  <div className="absolute bottom-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none">
                    <ArrowRight className="w-4 h-4 text-[#155c32]" />
                  </div>
                )}
              </motion.div>
            );
          })}
        </div>

        {/* Verified Banner */}
        <motion.div
          initial={{ opacity: 0, y: 15 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ delay: 0.4 }}
          className="mt-12 p-6 rounded-2xl bg-white border border-[#e7ece8] flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left"
        >
          <div className="space-y-1">
            <h4 className="text-sm font-bold text-[#1a1a1a]">Are you an energy supplier looking to expand?</h4>
            <p className="text-xs text-[#555555]">Apply to become a FuelCab Marketplace partner. Reach verified industrial clients directly.</p>
          </div>
          <Link
            href="/vendor/register"
            className="text-xs font-extrabold text-[#155c32] hover:text-[#33b248] transition-colors duration-150 flex items-center gap-1 group/link"
          >
            Start Vendor Application
            <ArrowRight className="w-3.5 h-3.5 transition-transform duration-200 group-hover/link:translate-x-0.5" />
          </Link>
        </motion.div>
        
      </div>
    </section>
  );
}
