"use client";

import React, { useEffect, useState } from "react";
import Image from "next/image";
import { motion } from "framer-motion";
import { Star, Award, CheckCircle } from "lucide-react";
import { cn } from "@/lib/utils";

interface Testimonial {
  name: string;
  role: string;
  company: string;
  rating: number;
  review: string;
  fuelPurchased: string;
  industry: string;
  photo: string;
}

interface Stat {
  target: number;
  suffix: string;
  label: string;
}

const TESTIMONIALS: Testimonial[] = [
  {
    name: "Rajesh Sharma",
    role: "Fleet Operations Manager",
    company: "V-Trans Logistics",
    rating: 5,
    review: "Refuelling 40 long-haul trucks used to take hours of manual logs and detours. With FuelCab, the tanker meets our fleet at the hub overnight. Quality and volume are transparent.",
    fuelPurchased: "Diesel HSD (4,500L)",
    industry: "Logistics & Transport",
    photo: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=150",
  },
  {
    name: "Vikram Malhotra",
    role: "Project Director",
    company: "L&T Infrastructure",
    rating: 5,
    review: "Our road projects run 24/7 in remote sites. FuelCab delivers diesel to our excavators and generators on schedule. It has completely eliminated generator downtime.",
    fuelPurchased: "Diesel & Oils (8,200L)",
    industry: "Construction",
    photo: "https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=150",
  },
  {
    name: "Nikhil Gupta",
    role: "Plant Head",
    company: "Aditya Manufacturing",
    rating: 5,
    review: "We rely heavily on our backup generators to keep the assembly line moving. The scheduled delivery service on FuelCab ensures our tanks are always topped up before power cuts hit.",
    fuelPurchased: "Diesel HSD (2,000L)",
    industry: "Manufacturing",
    photo: "https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?auto=format&fit=crop&q=80&w=150",
  },
  {
    name: "Arjun Verma",
    role: "Procurement Lead",
    company: "GMR Energy Systems",
    rating: 5,
    review: "The multi-vendor bidding system is a game-changer. We get competitive prices and automated compliance documents right inside the dashboard. Auditing is incredibly simple now.",
    fuelPurchased: "High Speed Diesel (12,000L)",
    industry: "Power Plants",
    photo: "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&q=80&w=150",
  },
];

const LOGOS = [
  "V-TRANS Logistics",
  "L&T Infra",
  "UltraTech Cement",
  "Tata Motors",
  "Reliance Logistics",
  "GMR Group",
];

const STATS: Stat[] = [
  { target: 100, suffix: "K+", label: "Litres Delivered" },
  { target: 500, suffix: "+", label: "Companies Powered" },
  { target: 98, suffix: "%", label: "On-Time Delivery" },
  { target: 50, suffix: "+", label: "Verified Vendors" },
];

function CountUp({ target, suffix, label }: Stat) {
  const [count, setCount] = useState(0);

  useEffect(() => {
    let start = 0;
    const duration = 2000; // ms
    const increment = Math.ceil(target / (duration / 16)); // ~60fps
    
    const timer = setInterval(() => {
      start += increment;
      if (start >= target) {
        setCount(target);
        clearInterval(timer);
      } else {
        setCount(start);
      }
    }, 16);

    return () => clearInterval(timer);
  }, [target]);

  return (
    <div className="flex flex-col items-center">
      <span className="text-3xl sm:text-4xl font-extrabold text-white tracking-tight">
        {count}
        {suffix}
      </span>
      <span className="text-xs text-gray-300 uppercase tracking-widest mt-2 text-center">
        {label}
      </span>
    </div>
  );
}

export default function Testimonials() {
  return (
    <section
      id="testimonials"
      className="py-24 bg-[#f4f8f5] border-b border-[#e7ece8] relative overflow-hidden"
      aria-label="Customer Testimonials"
    >
      {/* Background decorations */}
      <div className="absolute inset-0 pointer-events-none opacity-20 z-0">
        <div className="absolute top-[-10%] right-[-5%] w-[400px] h-[400px] rounded-full bg-[#33b248]/10 blur-3xl" />
        <div className="absolute bottom-[-10%] left-[-5%] w-[400px] h-[400px] rounded-full bg-[#155c32]/10 blur-3xl" />
      </div>

      <div className="max-w-[1400px] mx-auto relative z-10">
        
        {/* ─── Top: Trusted Company Logos Slider ─── */}
        <div className="w-full border-b border-[#e7ece8] pb-12 mb-16 px-4 sm:px-6 lg:px-8">
          <p className="text-center text-xs font-bold uppercase tracking-widest text-[#555555] mb-8">
            Trusted by Enterprise Fleet & Infrastructure Operators
          </p>
          
          {/* Infinite logo track */}
          <div className="relative w-full overflow-hidden flex items-center">
            <div className="absolute left-0 top-0 bottom-0 w-24 bg-gradient-to-r from-[#f4f8f5] to-transparent z-10 pointer-events-none" />
            <div className="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-[#f4f8f5] to-transparent z-10 pointer-events-none" />
            
            <div className="flex gap-16 animate-[infinite-scroll_25s_linear_infinite] whitespace-nowrap min-w-full">
              {[...LOGOS, ...LOGOS].map((logo, idx) => (
                <span
                  key={idx}
                  className="text-lg font-bold text-gray-400 hover:text-[#155c32] tracking-wider transition-colors duration-200"
                >
                  {logo}
                </span>
              ))}
            </div>
          </div>
        </div>

        {/* ─── Middle: Header with Google Review Rating ─── */}
        <div className="px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-8 mb-16">
          <div className="text-left max-w-[550px]">
            <span className="text-xs font-bold uppercase tracking-widest text-[#33b248] mb-3 block">
              Success Stories
            </span>
            <h2 className="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1a1a1a]">
              What Our B2B Partners Say
            </h2>
          </div>

          {/* Rating Badges */}
          <div className="flex flex-col sm:flex-row items-center gap-4 bg-white/70 backdrop-blur-md border border-[#e7ece8] p-5 rounded-2xl shadow-sm">
            <div className="flex items-center gap-2">
              <div className="flex text-[#ffb400]">
                {[...Array(5)].map((_, i) => (
                  <Star key={i} className="w-5 h-5 fill-current" />
                ))}
              </div>
              <span className="font-extrabold text-lg text-[#1a1a1a]">4.9</span>
            </div>
            
            <div className="hidden sm:block w-[1px] h-8 bg-gray-200" />
            
            <div className="flex items-center gap-2 text-xs font-bold text-[#555555]">
              <Award className="w-5 h-5 text-[#155c32]" />
              <span>Google Verified Reviews</span>
            </div>
          </div>
        </div>

        {/* ─── Testimonial Grid ─── */}
        <div className="px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-20">
          {TESTIMONIALS.map((testimonial, idx) => (
            <motion.div
              key={testimonial.name}
              initial={{ opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: idx * 0.05, duration: 0.5 }}
              whileHover={{ y: -8 }}
              className="bg-white/80 backdrop-blur-md rounded-2xl p-6 border border-[#e7ece8] shadow-sm flex flex-col justify-between hover:border-[#33b248] hover:shadow-lg transition-all duration-300 group cursor-default"
            >
              <div>
                {/* Stars */}
                <div className="flex text-[#ffb400] mb-4 gap-1">
                  {[...Array(testimonial.rating)].map((_, i) => (
                    <Star key={i} className="w-4 h-4 fill-current group-hover:scale-110 transition-transform duration-200" />
                  ))}
                </div>

                {/* Review */}
                <p className="text-xs text-[#555555] leading-relaxed mb-6 italic">
                  &ldquo;{testimonial.review}&rdquo;
                </p>
              </div>

              {/* User Bio */}
              <div className="border-t border-[#e7ece8] pt-4 mt-4">
                <div className="flex items-center gap-3 mb-3">
                  <div className="relative w-10 h-10 rounded-full overflow-hidden border border-gray-200">
                    <Image
                      src={testimonial.photo}
                      alt={testimonial.name}
                      fill
                      className="object-cover"
                      sizes="40px"
                    />
                  </div>
                  <div>
                    <h4 className="text-xs font-bold text-[#1a1a1a] leading-none mb-1">
                      {testimonial.name}
                    </h4>
                    <span className="text-[10px] text-gray-400 block leading-none">
                      {testimonial.role}, {testimonial.company}
                    </span>
                  </div>
                </div>

                {/* Logistics Metadata */}
                <div className="flex flex-wrap gap-2">
                  <span className="text-[9px] font-bold bg-[#155c32]/5 text-[#155c32] px-2 py-0.5 rounded-full">
                    {testimonial.fuelPurchased}
                  </span>
                  <span className="text-[9px] font-bold bg-[#33b248]/5 text-[#33b248] px-2 py-0.5 rounded-full">
                    {testimonial.industry}
                  </span>
                </div>
              </div>
            </motion.div>
          ))}
        </div>

        {/* ─── Bottom: Stats Banner (Dark Green) ─── */}
        <div className="px-4 sm:px-6 lg:px-8">
          <div className="bg-gradient-to-r from-[#155c32] to-[#0d3a1f] rounded-[24px] p-8 md:p-12 shadow-2xl relative overflow-hidden">
            
            {/* Soft backdrop logo pattern */}
            <div className="absolute right-0 bottom-0 opacity-5 pointer-events-none translate-x-1/4 translate-y-1/4">
              <CheckCircle className="w-[300px] h-[300px] text-white" />
            </div>

            <div className="grid grid-cols-2 lg:grid-cols-4 gap-8 relative z-10">
              {STATS.map((stat) => (
                <CountUp key={stat.label} {...stat} />
              ))}
            </div>
          </div>
        </div>

      </div>
      
      {/* Inject custom infinite scroll CSS keyframe directly */}
      <style jsx global>{`
        @keyframes infinite-scroll {
          from { transform: translateX(0); }
          to { transform: translateX(-50%); }
        }
      `}</style>
    </section>
  );
}
