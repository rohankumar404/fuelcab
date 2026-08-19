"use client";

import React from "react";
import Link from "next/link";
import { motion } from "framer-motion";
import {
  ArrowRight,
  Zap,
  ShieldCheck,
  Truck,
  PhoneCall,
} from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

const TRUST_BADGES = [
  { icon: Truck, label: "On-Site Delivery" },
  { icon: ShieldCheck, label: "Quality Certified" },
  { icon: Zap, label: "Emergency Orders" },
];

export default function CTASection() {
  return (
    <section
      id="cta"
      className="relative py-28 bg-[#0d3a1f] overflow-hidden"
      aria-label="Get started with FuelCab"
    >
      {/* ── Background decorations ── */}
      <div className="absolute inset-0 pointer-events-none z-0" aria-hidden="true">
        {/* Radial glow top-left */}
        <div className="absolute -top-1/4 -left-1/4 w-[700px] h-[700px] rounded-full bg-[#33b248]/10 blur-3xl" />
        {/* Radial glow bottom-right */}
        <div className="absolute -bottom-1/4 -right-1/4 w-[700px] h-[700px] rounded-full bg-[#155c32]/20 blur-3xl" />

        {/* Geometric SVG grid */}
        <svg
          className="absolute inset-0 w-full h-full opacity-[0.04]"
          xmlns="http://www.w3.org/2000/svg"
        >
          <defs>
            <pattern
              id="cta-grid"
              width="80"
              height="80"
              patternUnits="userSpaceOnUse"
            >
              <path
                d="M 80 0 L 0 0 0 80"
                fill="none"
                stroke="white"
                strokeWidth="1"
              />
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#cta-grid)" />
        </svg>

        {/* Animated pulsing ring */}
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full border border-white/5 animate-pulse" />
        <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[900px] h-[900px] rounded-full border border-white/[0.03]" />
      </div>

      <div className="relative z-10 max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12">
        
        {/* ── Main content ── */}
        <div className="text-center max-w-[780px] mx-auto">
          {/* Label pill */}
          <motion.div
            initial={{ opacity: 0, y: 12 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-[#33b248]/15 border border-[#33b248]/30 mb-8"
          >
            <span className="w-1.5 h-1.5 rounded-full bg-[#33b248] animate-pulse" />
            <span className="text-[11px] font-bold uppercase tracking-widest text-[#33b248]">
              Start Your First Order Today
            </span>
          </motion.div>

          {/* Headline */}
          <motion.h2
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.1 }}
            className="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.06] mb-6"
          >
            Fuel Your Business.{" "}
            <span className="text-[#33b248] relative inline-block">
              On Demand.
              <span
                className="absolute -bottom-1 left-0 w-full h-[3px] rounded-full bg-[#33b248]/50"
                aria-hidden="true"
              />
            </span>
          </motion.h2>

          {/* Subtitle */}
          <motion.p
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.2 }}
            className="text-gray-300 text-base sm:text-lg leading-relaxed mb-10 max-w-[560px] mx-auto"
          >
            Join 500+ companies powering their operations with verified,
            quality-certified fuel delivered directly to your site — minimum 100
            litres, anytime.
          </motion.p>

          {/* Action Buttons */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.3 }}
            className="flex flex-col sm:flex-row items-center justify-center gap-4 mb-14"
          >
            {/* Primary */}
            <Link
              href="/order"
              id="cta-order-now"
              className={cn(
                buttonVariants({ variant: "default", size: "lg" }),
                "h-14 px-8 rounded-2xl bg-[#33b248] text-white font-bold text-sm hover:bg-white hover:text-[#0d3a1f] hover:shadow-2xl hover:shadow-[#33b248]/30 transition-all duration-300 hover:-translate-y-1 group flex items-center gap-2"
              )}
            >
              <span>Order Fuel Now</span>
              <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </Link>

            {/* Emergency */}
            <Link
              href="/order?type=emergency"
              id="cta-emergency"
              className="h-14 px-8 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 text-white font-bold text-sm hover:bg-white/15 hover:border-white/30 transition-all duration-300 hover:-translate-y-1 flex items-center gap-2"
            >
              <Zap className="w-4 h-4 text-[#ffb400]" />
              <span>Emergency Order</span>
            </Link>

            {/* Contact */}
            <Link
              href="/vendor/register"
              id="cta-vendor"
              className={cn(
                buttonVariants({ variant: "outline", size: "lg" }),
                "h-14 px-8 rounded-2xl border-white/20 text-white font-bold text-sm hover:border-[#33b248] hover:text-[#33b248] transition-all duration-300 hover:-translate-y-1 flex items-center gap-2 bg-transparent"
              )}
            >
              <PhoneCall className="w-4 h-4" />
              <span>Become a Vendor</span>
            </Link>
          </motion.div>

          {/* Trust badges */}
          <motion.div
            initial={{ opacity: 0, y: 16 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.45 }}
            className="flex flex-wrap items-center justify-center gap-6 border-t border-white/10 pt-10"
          >
            {TRUST_BADGES.map(({ icon: Icon, label }) => (
              <div
                key={label}
                className="flex items-center gap-2.5 text-gray-300"
              >
                <div className="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center">
                  <Icon className="w-4 h-4 text-[#33b248]" />
                </div>
                <span className="text-xs font-semibold tracking-wide">{label}</span>
              </div>
            ))}

            {/* Divider */}
            <span className="hidden sm:block w-px h-5 bg-white/15" />

            {/* Rating */}
            <div className="flex items-center gap-2 text-gray-300">
              <div className="flex text-[#ffb400] gap-0.5">
                {[...Array(5)].map((_, i) => (
                  <svg
                    key={i}
                    className="w-3.5 h-3.5 fill-current"
                    viewBox="0 0 24 24"
                  >
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                  </svg>
                ))}
              </div>
              <span className="text-xs font-bold text-white">4.9</span>
              <span className="text-xs text-gray-400">/ 5 Rating</span>
            </div>
          </motion.div>
        </div>

      </div>
    </section>
  );
}
