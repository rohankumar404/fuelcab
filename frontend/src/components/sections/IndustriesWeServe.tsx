"use client";

import React from "react";
import Image from "next/image";
import Link from "next/link";
import { motion } from "framer-motion";
import { ArrowRight, PhoneCall } from "lucide-react";
import { buttonVariants } from "@/components/ui/button";
import { cn } from "@/lib/utils";

interface Industry {
  name: string;
  description: string;
  image: string;
}

const INDUSTRIES: Industry[] = [
  {
    name: "Construction & Infrastructure",
    description: "On-site diesel refuelling for excavators, generators, and heavy development machinery.",
    image: "https://images.unsplash.com/photo-1578328819058-b69f3a3b0f6b?auto=format&fit=crop&q=80&w=600",
  },
  {
    name: "Manufacturing & Factories",
    description: "Consistent fuel supply for uninterrupted operation of boilers, assembly loops, and generators.",
    image: "https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&q=80&w=600",
  },
  {
    name: "Mining Operations",
    description: "Industrial fuel logistics managed for remote mineral extraction fields and transport fleets.",
    image: "https://images.unsplash.com/photo-1579847255504-450f14067c74?auto=format&fit=crop&q=80&w=600",
  },
  {
    name: "Agriculture & Farming",
    description: "Bulk diesel delivered directly to farms for tractors, harvesters, and irrigation pumps.",
    image: "https://images.unsplash.com/photo-1595974482597-4b8da8879bc5?auto=format&fit=crop&q=80&w=600",
  },
  {
    name: "Logistics & Transport",
    description: "Fleet fuel management solutions with tracking systems for long-haul shipping lines.",
    image: "https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&q=80&w=600",
  },
  {
    name: "Warehouses & Fulfillment",
    description: "Powering backup generators and heavy forklift systems to maintain logistics flows.",
    image: "https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&q=80&w=600",
  },
  {
    name: "Power Plants & Energy",
    description: "Emergency support fuel and start-up supply solutions for major power generation nodes.",
    image: "https://images.unsplash.com/photo-1540324155974-7265d7cb6d1b?auto=format&fit=crop&q=80&w=600",
  },
  {
    name: "Telecom Infrastructure",
    description: "Scheduled refueling logs for remote cell towers keeping communications active.",
    image: "https://images.unsplash.com/photo-1520052205735-5d9c22e43773?auto=format&fit=crop&q=80&w=600",
  },
  {
    name: "Road & Bridge Infrastructure",
    description: "On-the-go fuel stations set up dynamically for highway construction projects.",
    image: "https://images.unsplash.com/photo-1541888946425-d81bb19240f5?auto=format&fit=crop&q=80&w=600",
  },
];

export default function IndustriesWeServe() {
  return (
    <section
      id="industries"
      className="py-24 bg-white border-b border-[#e7ece8] relative overflow-hidden"
      aria-label="Industries We Serve"
    >
      <div className="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 xl:px-12 relative z-10">
        
        {/* Section Title */}
        <div className="text-center max-w-[650px] mx-auto mb-16">
          <motion.span
            initial={{ opacity: 0, y: 10 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-xs font-bold uppercase tracking-widest text-[#33b248] mb-3 block"
          >
            Industries We Serve
          </motion.span>
          <motion.h2
            initial={{ opacity: 0, y: 15 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.1 }}
            className="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1a1a1a] mb-4"
          >
            Fueling Every Industry Sector
          </motion.h2>
          <motion.p
            initial={{ opacity: 0, y: 15 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.2 }}
            className="text-[#555555] text-sm sm:text-base leading-relaxed"
          >
            We supply high-grade fuel directly to your operational hubs, keeping diverse business divisions powered.
          </motion.p>
        </div>

        {/* Swipe Indicator for Mobile */}
        <div className="flex sm:hidden items-center justify-center gap-2 mb-4 text-[#555555] text-xs">
          <span>Swipe to explore industries</span>
          <ArrowRight className="w-3 h-3 animate-pulse" />
        </div>

        {/* Industries Grid / Mobile Slider */}
        <div
          className="flex overflow-x-auto snap-x snap-mandatory gap-6 pb-6 sm:grid sm:grid-cols-2 lg:grid-cols-3 scrollbar-hide sm:overflow-visible"
          role="region"
          aria-label="List of industries served"
        >
          {INDUSTRIES.map((industry, index) => (
            <motion.div
              key={industry.name}
              initial={{ opacity: 0, y: 24 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: index * 0.05, duration: 0.5 }}
              className="snap-center shrink-0 w-[300px] sm:w-auto"
            >
              <div className="relative h-[340px] rounded-3xl overflow-hidden group shadow-md hover:shadow-xl transition-all duration-300">
                {/* Background Image */}
                <Image
                  src={industry.image}
                  alt={industry.name}
                  fill
                  className="object-cover transition-transform duration-500 group-hover:scale-110"
                  sizes="(max-width: 640px) 300px, (max-width: 1024px) 50vw, 33vw"
                />

                {/* Dark Gradient Overlay */}
                <div className="absolute inset-0 bg-gradient-to-t from-[#0d3a1f] via-[#0d3a1f]/50 to-transparent transition-opacity duration-300 group-hover:via-[#0d3a1f]/60" />

                {/* Content Panel */}
                <div className="absolute inset-0 p-6 flex flex-col justify-end text-left text-white">
                  <h3 className="text-lg font-bold tracking-tight mb-2">
                    {industry.name}
                  </h3>
                  
                  {/* Sliding details */}
                  <p className="text-xs text-gray-200 line-clamp-2 mb-4 opacity-90 group-hover:line-clamp-none transition-all duration-300">
                    {industry.description}
                  </p>

                  <Link
                    href={`/industries/${industry.name.toLowerCase().replace(/[^a-z0-9]+/g, "-")}`}
                    className="inline-flex items-center gap-1 text-[11px] font-bold text-[#33b248] hover:text-white uppercase tracking-wider transition-colors duration-200 group/link"
                  >
                    <span>Learn More</span>
                    <ArrowRight className="w-3.5 h-3.5 group-hover/link:translate-x-1 transition-transform" />
                  </Link>
                </div>
              </div>
            </motion.div>
          ))}
        </div>

        {/* ─── Bottom CTA section ─── */}
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          whileInView={{ opacity: 1, y: 0 }}
          viewport={{ once: true }}
          transition={{ delay: 0.1, duration: 0.5 }}
          className="mt-16 bg-gradient-to-r from-[#155c32] to-[#0d3a1f] rounded-[24px] p-8 md:p-10 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 text-left"
        >
          <div>
            <h4 className="text-xl md:text-2xl font-bold tracking-tight mb-2">
              Need Bulk Diesel or Customized Fuel Logistics?
            </h4>
            <p className="text-xs md:text-sm text-gray-200">
              Get in touch with our energy experts for custom scheduling, specialized pricing, and bulk delivery details.
            </p>
          </div>
          
          <Link
            href="/contact"
            className={cn(
              buttonVariants({ variant: "default" }),
              "bg-[#33b248] text-white hover:bg-white hover:text-[#155c32] h-12 px-6 rounded-xl font-bold text-xs flex items-center justify-center gap-2 group transition-all duration-300"
            )}
          >
            <PhoneCall className="w-4 h-4" />
            <span>Talk to Fuel Experts</span>
          </Link>
        </motion.div>

      </div>
    </section>
  );
}
