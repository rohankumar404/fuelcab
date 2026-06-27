import { Flame } from "lucide-react";
import Link from "next/link";

export default function AnnouncementBar() {
  return (
    <div
      className="w-full bg-[#0d3a1f] text-white px-4 sm:px-6 lg:px-8 xl:px-12"
      style={{ height: "42px" }}
    >
      <div className="mx-auto max-w-[1400px] h-full flex items-center justify-between gap-4">
        {/* Left — announcement text */}
        <div className="flex items-center gap-2 min-w-0">
          <Flame className="w-3.5 h-3.5 text-[#ffb400] fill-[#ffb400] flex-shrink-0" />
          <p className="text-[11px] sm:text-xs font-medium text-gray-100 truncate">
            <span className="font-semibold text-white">Minimum order quantity is 100 Liters.</span>
            <span className="hidden sm:inline"> Reliable fuel delivery for your business.</span>
          </p>
        </div>

        {/* Right — quick links */}
        <nav
          className="hidden md:flex items-center gap-5 flex-shrink-0"
          aria-label="Top bar navigation"
        >
          {["About Us", "Careers", "Contact Us"].map((item) => (
            <Link
              key={item}
              href={`#${item.toLowerCase().replace(/\s+/g, "-")}`}
              className="text-[11px] text-gray-300 hover:text-white transition-colors duration-150 font-medium"
            >
              {item}
            </Link>
          ))}
        </nav>
      </div>
    </div>
  );
}
