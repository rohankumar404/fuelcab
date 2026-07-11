"use client";

import React, { useRef } from "react";
import { motion, useScroll, useSpring, useTransform } from "framer-motion";
import {
  Flame,
  Sliders,
  Building,
  Clock,
  Navigation,
  ShieldCheck,
} from "lucide-react";
import { cn } from "@/lib/utils";

interface Step {
  number: number;
  title: string;
  description: string;
  icon: React.ComponentType<{ className?: string }>;
  color: string;
}

const STEPS: Step[] = [
  {
    number: 1,
    title: "Select Fuel",
    description: "Choose your required fuel type (Diesel HSD, AdBlue/DEF, or specialized lubricants) from our premium verified selection.",
    icon: Flame,
    color: "bg-[#155c32]/10 text-[#155c32] border-[#155c32]/25",
  },
  {
    number: 2,
    title: "Choose Quantity",
    description: "Specify the exact volume needed for your tanks (minimum 100 liters) using our smart pricing calculator.",
    icon: Sliders,
    color: "bg-[#33b248]/10 text-[#33b248] border-[#33b248]/25",
  },
  {
    number: 3,
    title: "Select Vendor",
    description: "Compare bids and select the most competitive local vendor based on pricing, delivery times, and user ratings.",
    icon: Building,
    color: "bg-[#155c32]/10 text-[#155c32] border-[#155c32]/25",
  },
  {
    number: 4,
    title: "Schedule Delivery",
    description: "Pick a date and time slot that fits your operations, avoiding downtime with flexible scheduling.",
    icon: Clock,
    color: "bg-[#33b248]/10 text-[#33b248] border-[#33b248]/25",
  },
  {
    number: 5,
    title: "Track Tanker Live",
    description: "Follow the dispatched tanker on a real-time GPS map as it makes its way directly to your location.",
    icon: Navigation,
    color: "bg-[#155c32]/10 text-[#155c32] border-[#155c32]/25",
  },
  {
    number: 6,
    title: "Receive Fuel",
    description: "Inspect the automated quality certificate, supervise refuelling, and authorize payment securely.",
    icon: ShieldCheck,
    color: "bg-[#33b248]/10 text-[#33b248] border-[#33b248]/25",
  },
];

export default function HowItWorks() {
  const containerRef = useRef<HTMLDivElement>(null);
  
  // Hook scroll progress for timeline animation
  const { scrollYProgress } = useScroll({
    target: containerRef,
    offset: ["start end", "end end"],
  });

  const scaleY = useSpring(scrollYProgress, {
    stiffness: 100,
    damping: 30,
    restDelta: 0.001,
  });

  const scaleX = useSpring(scrollYProgress, {
    stiffness: 100,
    damping: 30,
    restDelta: 0.001,
  });

  return (
    <section
      id="how-it-works"
      ref={containerRef}
      className="py-24 bg-[#f4f8f5] border-t border-b border-[#e7ece8] relative overflow-hidden"
      aria-label="How FuelCab Works"
    >
      {/* Subtle Industrial Background SVG pattern */}
      <div className="absolute inset-0 z-0 pointer-events-none opacity-5">
        <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
          <pattern id="industrial-grid" width="60" height="60" patternUnits="userSpaceOnUse">
            <path d="M 60 0 L 0 0 0 60" fill="none" stroke="#155c32" strokeWidth="1.5" />
            <circle cx="60" cy="60" r="2" fill="#155c32" />
          </pattern>
          <rect width="100%" height="100%" fill="url(#industrial-grid)" />
        </svg>
      </div>

      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">
        
        {/* Header */}
        <div className="text-center max-w-[650px] mx-auto mb-20">
          <motion.span
            initial={{ opacity: 0, y: 10 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-xs font-bold uppercase tracking-widest text-[#33b248] mb-3 block"
          >
            How It Works
          </motion.span>
          <motion.h2
            initial={{ opacity: 0, y: 15 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.1 }}
            className="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1a1a1a] mb-4"
          >
            Simple. Fast. Reliable.
          </motion.h2>
          <motion.p
            initial={{ opacity: 0, y: 15 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.2 }}
            className="text-[#555555] text-sm sm:text-base leading-relaxed"
          >
            Ordering fuel for your business operations takes just six steps.
          </motion.p>
        </div>

        {/* ─── Desktop: Horizontal Timeline (lg screens) ─── */}
        <div className="hidden lg:block relative pt-12">
          {/* Animated line grows while scrolling */}
          <div className="absolute top-[82px] left-[8%] right-[8%] h-[2px] bg-gray-200" />
          <motion.div
            style={{ scaleX, originX: 0 }}
            className="absolute top-[82px] left-[8%] right-[8%] h-[2.5px] bg-[#33b248] shadow-sm shadow-[#33b248]"
          />

          <div className="grid grid-cols-6 gap-6 relative z-10">
            {STEPS.map((step, index) => {
              const Icon = step.icon;
              return (
                <motion.div
                  key={step.number}
                  initial={{ opacity: 0, y: 35 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: index * 0.1, duration: 0.6 }}
                  className="flex flex-col items-center text-center group cursor-default"
                >
                  {/* Step Icon */}
                  <div
                    className={cn(
                      "w-[68px] h-[68px] rounded-full bg-white border border-[#e7ece8] shadow-md flex items-center justify-center mb-5",
                      "group-hover:border-[#33b248] group-hover:shadow-[#33b248]/15 group-hover:-translate-y-1.5 transition-all duration-300"
                    )}
                  >
                    <Icon className="w-6 h-6 text-[#155c32] group-hover:scale-110 transition-transform duration-300" />
                  </div>

                  {/* Badge Number */}
                  <span className="text-[11px] font-extrabold text-[#33b248] uppercase tracking-wider mb-2">
                    Step {step.number}
                  </span>

                  {/* Title */}
                  <h3 className="text-sm font-bold text-[#1a1a1a] mb-2 tracking-tight">
                    {step.title}
                  </h3>

                  {/* Description */}
                  <p className="text-[11px] text-[#555555] leading-relaxed px-1">
                    {step.description}
                  </p>
                </motion.div>
              );
            })}
          </div>
        </div>

        {/* ─── Tablet: 2 Columns (md to lg) ─── */}
        <div className="hidden md:grid lg:hidden grid-cols-2 gap-8 relative z-10">
          {STEPS.map((step, index) => {
            const Icon = step.icon;
            return (
              <motion.div
                key={step.number}
                initial={{ opacity: 0, y: 30 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: index * 0.08, duration: 0.5 }}
                className="flex gap-4 bg-white rounded-2xl p-6 border border-[#e7ece8] shadow-sm hover:border-[#33b248] hover:shadow-md transition-all duration-300 group"
              >
                <div className="flex-shrink-0">
                  <div className="w-12 h-12 rounded-xl bg-[#f4f8f5] flex items-center justify-center border border-[#e7ece8] group-hover:border-[#33b248] transition-colors">
                    <Icon className="w-5 h-5 text-[#155c32] group-hover:scale-110 transition-transform" />
                  </div>
                </div>
                <div>
                  <span className="text-[10px] font-extrabold text-[#33b248] uppercase tracking-widest block mb-1">
                    Step {step.number}
                  </span>
                  <h3 className="text-base font-bold text-[#1a1a1a] mb-2">
                    {step.title}
                  </h3>
                  <p className="text-xs text-[#555555] leading-relaxed">
                    {step.description}
                  </p>
                </div>
              </motion.div>
            );
          })}
        </div>

        {/* ─── Mobile: Vertical Timeline (sm screens) ─── */}
        <div className="block md:hidden relative pl-8">
          {/* Timeline Line */}
          <div className="absolute left-[15px] top-4 bottom-4 w-[2px] bg-gray-200" />
          <motion.div
            style={{ scaleY, originY: 0 }}
            className="absolute left-[15px] top-4 bottom-4 w-[2px] bg-[#33b248] shadow-sm"
          />

          <div className="flex flex-col gap-10">
            {STEPS.map((step, index) => {
              const Icon = step.icon;
              return (
                <motion.div
                  key={step.number}
                  initial={{ opacity: 0, x: -15 }}
                  whileInView={{ opacity: 1, x: 0 }}
                  viewport={{ once: true }}
                  transition={{ delay: index * 0.05, duration: 0.4 }}
                  className="relative flex flex-col items-start text-left"
                >
                  {/* Circle Indicator on Line */}
                  <div className="absolute -left-[33px] top-1.5 w-6 h-6 rounded-full bg-white border-2 border-[#33b248] flex items-center justify-center z-10 shadow-sm">
                    <span className="text-[10px] font-bold text-[#155c32]">{step.number}</span>
                  </div>

                  {/* Icon Block */}
                  <div className="w-10 h-10 rounded-xl bg-white border border-[#e7ece8] flex items-center justify-center mb-3">
                    <Icon className="w-4 h-4 text-[#155c32]" />
                  </div>

                  {/* Content */}
                  <h3 className="text-base font-bold text-[#1a1a1a] mb-1">
                    {step.title}
                  </h3>
                  <p className="text-xs text-[#555555] leading-relaxed">
                    {step.description}
                  </p>
                </motion.div>
              );
            })}
          </div>
        </div>

      </div>
    </section>
  );
}
