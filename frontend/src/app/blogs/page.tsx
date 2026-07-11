"use client";

import { useState } from "react";
import Link from "next/link";
import { Calendar, Clock, ArrowRight, Search, Tag, TrendingUp, BookOpen } from "lucide-react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";

const CATEGORIES = ["All", "Industry News", "Fuel Prices", "Regulations", "Business Tips", "Technology"];

const BLOGS = [
  {
    slug: "diesel-price-india-2024",
    category: "Fuel Prices",
    title: "Diesel Prices in India: What to Expect in 2024",
    excerpt:
      "A deep dive into the factors driving diesel price fluctuations in India — from global crude benchmarks to refinery margins and state-level levies.",
    author: "Rahul Mehta",
    date: "Jun 28, 2026",
    readTime: "6 min read",
    tag: "Featured",
    bg: "from-amber-500 to-orange-600",
  },
  {
    slug: "b2b-fuel-procurement-tips",
    category: "Business Tips",
    title: "5 Ways to Cut Fuel Costs for Your Fleet Business",
    excerpt:
      "Bulk purchasing, scheduled deliveries, and smart vendor selection can collectively reduce your operational fuel spend by 15–20%.",
    author: "Priya Sharma",
    date: "Jun 22, 2026",
    readTime: "4 min read",
    tag: "Popular",
    bg: "from-green-500 to-emerald-600",
  },
  {
    slug: "ev-transition-logistics",
    category: "Technology",
    title: "EV Transition in Logistics: Is Your Business Ready?",
    excerpt:
      "As electric commercial vehicles gain traction, fuel-dependent fleets must plan their transition strategy now. We break down the timeline.",
    author: "Amit Nair",
    date: "Jun 18, 2026",
    readTime: "7 min read",
    tag: "New",
    bg: "from-blue-500 to-indigo-600",
  },
  {
    slug: "gst-fuel-regulations",
    category: "Regulations",
    title: "GST on Fuel: Current Regime and What Could Change",
    excerpt:
      "Petroleum products remain outside GST net, but recent parliamentary discussions hint at possible inclusion. Here is what that means for businesses.",
    author: "Rahul Mehta",
    date: "Jun 12, 2026",
    readTime: "5 min read",
    tag: null,
    bg: "from-purple-500 to-violet-600",
  },
  {
    slug: "hsd-vs-cng-fleet",
    category: "Industry News",
    title: "HSD vs CNG for Commercial Fleets: A Cost Comparison",
    excerpt:
      "Running a mixed fleet? We compare TCO, infrastructure costs, and availability to help you make the right fuel choice for each vehicle class.",
    author: "Priya Sharma",
    date: "Jun 5, 2026",
    readTime: "8 min read",
    tag: null,
    bg: "from-teal-500 to-cyan-600",
  },
  {
    slug: "fuelcab-vendor-network",
    category: "Industry News",
    title: "How FuelCab's Vendor Network Ensures Quality Supply",
    excerpt:
      "From certification checks to real-time delivery tracking, our multi-vendor model guarantees consistent quality and competitive pricing for buyers.",
    author: "FuelCab Team",
    date: "May 30, 2026",
    readTime: "3 min read",
    tag: null,
    bg: "from-rose-500 to-pink-600",
  },
];

export default function BlogsPage() {
  const [search, setSearch] = useState("");
  const [activeCategory, setActiveCategory] = useState("All");

  const filtered = BLOGS.filter((b) => {
    const matchCat = activeCategory === "All" || b.category === activeCategory;
    const matchSearch =
      !search ||
      b.title.toLowerCase().includes(search.toLowerCase()) ||
      b.excerpt.toLowerCase().includes(search.toLowerCase());
    return matchCat && matchSearch;
  });

  return (
    <div className="min-h-screen bg-[#fafbfa] flex flex-col">
      <Navbar />

      <main className="flex-1">
        {/* Hero */}
        <section className="bg-gradient-to-r from-[#155c32] to-[#0d3a1f] py-14 px-4">
          <div className="max-w-4xl mx-auto text-center">
            <div className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/20 mb-4">
              <BookOpen className="w-3.5 h-3.5 text-[#33b248]" />
              <span className="text-xs font-bold uppercase tracking-widest text-[#33b248]">Knowledge Hub</span>
            </div>
            <h1 className="text-3xl sm:text-4xl font-extrabold text-white mb-3">FuelCab Insights</h1>
            <p className="text-gray-300 text-sm max-w-xl mx-auto">
              Industry news, fuel price updates, regulatory changes, and expert tips for fleet and logistics businesses.
            </p>

            {/* Search */}
            <div className="relative mt-8 max-w-md mx-auto">
              <Search className="absolute left-4 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
              <input
                type="text"
                placeholder="Search articles..."
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                className="w-full h-12 pl-11 pr-4 rounded-xl bg-white/10 border border-white/20 text-sm text-white placeholder-gray-400 focus:outline-none focus:border-[#33b248] focus:bg-white/15 transition"
              />
            </div>
          </div>
        </section>

        <section className="max-w-6xl mx-auto px-4 py-10">
          {/* Category filters */}
          <div className="flex flex-wrap gap-2 mb-8">
            {CATEGORIES.map((cat) => (
              <button
                key={cat}
                onClick={() => setActiveCategory(cat)}
                className={`px-4 py-2 rounded-full text-xs font-semibold border transition-all duration-150 ${
                  activeCategory === cat
                    ? "bg-[#155c32] border-[#155c32] text-white"
                    : "bg-white border-[#e7ece8] text-[#555] hover:border-[#155c32]/40"
                }`}
              >
                {cat}
              </button>
            ))}
          </div>

          {/* Stats row */}
          <div className="flex gap-6 mb-8 text-xs text-[#888]">
            <span className="flex items-center gap-1.5">
              <TrendingUp className="w-3.5 h-3.5 text-[#155c32]" />
              {filtered.length} Articles
            </span>
            <span className="flex items-center gap-1.5">
              <Tag className="w-3.5 h-3.5 text-[#155c32]" />
              {CATEGORIES.length - 1} Categories
            </span>
          </div>

          {/* Grid */}
          {filtered.length > 0 ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filtered.map((blog) => (
                <Link
                  key={blog.slug}
                  href={`/blogs/${blog.slug}`}
                  className="group bg-white rounded-2xl border border-[#e7ece8] overflow-hidden shadow-sm hover:shadow-xl hover:shadow-[#155c32]/8 hover:-translate-y-1 transition-all duration-300"
                >
                  {/* Gradient thumbnail */}
                  <div className={`h-36 bg-gradient-to-br ${blog.bg} relative flex items-end p-4`}>
                    {blog.tag && (
                      <span className="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-white/20 backdrop-blur text-white text-[10px] font-bold uppercase tracking-wider">
                        {blog.tag}
                      </span>
                    )}
                    <span className="px-2.5 py-1 rounded-full bg-white/20 backdrop-blur text-white text-[10px] font-semibold">
                      {blog.category}
                    </span>
                  </div>

                  {/* Content */}
                  <div className="p-5">
                    <h2 className="font-bold text-[#1a1a1a] text-sm leading-snug mb-2 group-hover:text-[#155c32] transition-colors line-clamp-2">
                      {blog.title}
                    </h2>
                    <p className="text-xs text-[#777] leading-relaxed line-clamp-3 mb-4">
                      {blog.excerpt}
                    </p>
                    <div className="flex items-center justify-between text-[10px] text-[#aaa] border-t border-[#f0f0f0] pt-3">
                      <span className="flex items-center gap-1.5">
                        <Calendar className="w-3 h-3" /> {blog.date}
                      </span>
                      <span className="flex items-center gap-1.5">
                        <Clock className="w-3 h-3" /> {blog.readTime}
                      </span>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          ) : (
            <div className="text-center py-20 text-[#bbb]">
              <BookOpen className="w-10 h-10 mx-auto mb-3" />
              <p className="font-semibold">No articles match your search.</p>
            </div>
          )}

          {/* CTA */}
          <div className="mt-14 bg-gradient-to-r from-[#155c32] to-[#0d3a1f] rounded-2xl p-8 text-center">
            <h3 className="text-xl font-bold text-white mb-2">Ready to Order Fuel?</h3>
            <p className="text-gray-300 text-sm mb-6">Get bulk diesel and commercial fuel delivered to your site, today.</p>
            <Link
              href="/order"
              className="inline-flex items-center gap-2 h-11 px-7 rounded-xl bg-[#33b248] text-white font-semibold text-sm hover:bg-[#2a9a3d] transition hover:-translate-y-px"
            >
              Order Now <ArrowRight className="w-4 h-4" />
            </Link>
          </div>
        </section>
      </main>

      <Footer />
    </div>
  );
}
