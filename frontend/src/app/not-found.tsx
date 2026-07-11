"use client";

import Link from "next/link";
import { Droplet, Home, ArrowLeft } from "lucide-react";

export default function NotFound() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-[#f4f8f5] via-white to-[#e8f4ec] flex items-center justify-center px-4">
      <div className="text-center max-w-md">
        {/* Logo */}
        <Link href="/" className="inline-flex items-center gap-2.5 mb-10">
          <div className="w-9 h-9 rounded-xl bg-[#155c32] flex items-center justify-center">
            <Droplet className="w-5 h-5 text-[#33b248] fill-[#33b248]" />
          </div>
          <span className="text-xl font-extrabold tracking-tight text-[#1a1a1a]">
            Fuel<span className="text-[#155c32]">Cab</span>
          </span>
        </Link>

        {/* 404 graphic */}
        <div className="relative mb-8">
          <span className="text-[9rem] font-black text-[#155c32]/10 leading-none select-none">
            404
          </span>
          <div className="absolute inset-0 flex items-center justify-center">
            <div className="w-20 h-20 rounded-full bg-[#155c32]/10 flex items-center justify-center">
              <span className="text-4xl">⛽</span>
            </div>
          </div>
        </div>

        <h1 className="text-2xl font-bold text-[#1a1a1a] mb-3">Page Not Found</h1>
        <p className="text-[#666] text-sm leading-relaxed mb-8">
          Looks like this tank is empty. The page you&apos;re looking for doesn&apos;t exist or has been moved.
        </p>

        <div className="flex gap-3 justify-center">
          <Link
            href="/"
            className="inline-flex items-center gap-2 h-11 px-6 rounded-xl bg-[#155c32] text-white font-semibold text-sm hover:bg-[#0d3a1f] hover:shadow-lg hover:shadow-[#155c32]/20 transition-all duration-200 hover:-translate-y-px"
          >
            <Home className="w-4 h-4" />
            Back to Home
          </Link>
          <button
            onClick={() => window.history.back()}
            className="inline-flex items-center gap-2 h-11 px-6 rounded-xl border border-[#e7ece8] text-[#555] font-semibold text-sm hover:border-[#155c32] hover:text-[#155c32] transition-all duration-200"
          >
            <ArrowLeft className="w-4 h-4" />
            Go Back
          </button>
        </div>
      </div>
    </div>
  );
}
