import Link from "next/link";
import { ArrowLeft, Calendar, Clock, Tag, Share2 } from "lucide-react";
import Navbar from "@/components/layout/Navbar";
import Footer from "@/components/layout/Footer";

const BLOGS: Record<string, {
  category: string; title: string; author: string; date: string;
  readTime: string; bg: string; content: string;
}> = {
  "diesel-price-india-2024": {
    category: "Fuel Prices",
    title: "Diesel Prices in India: What to Expect in 2024",
    author: "Rahul Mehta", date: "Jun 28, 2026", readTime: "6 min read",
    bg: "from-amber-500 to-orange-600",
    content: `Diesel prices in India are shaped by a complex interplay of global crude oil benchmarks, refinery margins, state-level levies, and central excise duties. As a B2B buyer, understanding these dynamics allows you to plan your procurement better.

## Global Crude Oil Benchmark

India primarily imports Brent and West Texas Intermediate (WTI) crude. A $10 rise in the Brent benchmark typically translates into roughly ₹6–8/litre increase in diesel retail prices after accounting for refinery margins and logistics.

## State vs. Central Duties

Central excise on diesel currently stands at ₹21.80/litre. Over this, each state adds its own VAT. States like Maharashtra (24%) and Rajasthan (22%) have significantly higher rates than UP (17.5%), making bulk procurement cross-border sourcing a viable strategy for businesses near state borders.

## Forecasting 2024 Prices

Given OPEC+ production agreements and India's strategic petroleum reserve builds, analysts expect modest volatility between ₹92–₹98/litre for HSD throughout the year. Businesses should consider locking in long-term vendor contracts when prices dip toward the lower band.

## Tips for B2B Buyers

1. **Sign quarterly contracts** with fixed price or floating rate clauses.
2. **Monitor OMC notifications** — HPCL, BPCL, and IOC update prices fortnightly.
3. **Use FuelCab's live pricing dashboard** to benchmark vendor quotes against OMC rates.`,
  },
  "b2b-fuel-procurement-tips": {
    category: "Business Tips",
    title: "5 Ways to Cut Fuel Costs for Your Fleet Business",
    author: "Priya Sharma", date: "Jun 22, 2026", readTime: "4 min read",
    bg: "from-green-500 to-emerald-600",
    content: `Fleet fuel costs typically account for 30–40% of total operating expenses. Reducing this even marginally creates significant bottom-line impact at scale.

## 1. Bulk Purchasing Contracts

Negotiate a fixed price per litre for guaranteed monthly volumes. Vendors are often willing to offer ₹2–4/litre discounts for committed 10,000+ litre monthly orders.

## 2. Optimise Delivery Scheduling

Avoid emergency or on-demand deliveries which carry a premium. Schedule deliveries weekly or bi-weekly to amortize delivery costs and eliminate idle tanker fees.

## 3. Centralise Procurement

Consolidating fuel purchasing across all depots through a single platform like FuelCab eliminates rogue spending and ensures every litre is tracked with a GST invoice.

## 4. Track Driver Behaviour

Aggressive driving, excessive idling, and over-speeding can increase fuel consumption by up to 20%. Implement telematics and incentivise fuel-efficient behaviour.

## 5. Choose Vendors Wisely

Always buy from certified vendors. Adulterated fuel causes engine wear, increasing maintenance costs 3–5x over time. FuelCab's vendor network is quality-audited and insured.`,
  },
};

interface PageProps {
  params: Promise<{ slug: string }>;
}

export default async function BlogPostPage({ params }: PageProps) {
  const { slug } = await params;
  const blog = BLOGS[slug];

  if (!blog) {
    return (
      <div className="min-h-screen bg-[#fafbfa] flex flex-col">
        <Navbar />
        <main className="flex-1 flex items-center justify-center">
          <div className="text-center">
            <p className="text-5xl mb-4">📭</p>
            <h1 className="text-xl font-bold text-[#1a1a1a] mb-2">Article Not Found</h1>
            <p className="text-[#777] text-sm mb-6">This article doesn&apos;t exist or has been removed.</p>
            <Link href="/blogs" className="inline-flex items-center gap-2 text-sm font-semibold text-[#155c32] hover:underline">
              <ArrowLeft className="w-4 h-4" /> Back to Blogs
            </Link>
          </div>
        </main>
        <Footer />
      </div>
    );
  }

  const paragraphs = blog.content.split("\n\n");

  return (
    <div className="min-h-screen bg-[#fafbfa] flex flex-col">
      <Navbar />
      <main className="flex-1">
        {/* Hero */}
        <div className={`bg-gradient-to-r ${blog.bg} py-16 px-4`}>
          <div className="max-w-3xl mx-auto">
            <Link href="/blogs" className="inline-flex items-center gap-2 text-white/70 text-sm font-medium hover:text-white transition mb-6">
              <ArrowLeft className="w-4 h-4" /> All Articles
            </Link>
            <span className="inline-block px-3 py-1 rounded-full bg-white/20 text-white text-xs font-semibold mb-4">
              {blog.category}
            </span>
            <h1 className="text-2xl sm:text-3xl font-extrabold text-white leading-snug mb-5">{blog.title}</h1>
            <div className="flex flex-wrap gap-4 text-white/70 text-xs">
              <span className="flex items-center gap-1.5"><Tag className="w-3.5 h-3.5" /> {blog.author}</span>
              <span className="flex items-center gap-1.5"><Calendar className="w-3.5 h-3.5" /> {blog.date}</span>
              <span className="flex items-center gap-1.5"><Clock className="w-3.5 h-3.5" /> {blog.readTime}</span>
            </div>
          </div>
        </div>

        {/* Content */}
        <div className="max-w-3xl mx-auto px-4 py-10">
          <article className="bg-white rounded-2xl border border-[#e7ece8] p-8 shadow-sm prose prose-sm prose-headings:text-[#1a1a1a] prose-p:text-[#555] max-w-none">
            {paragraphs.map((para, i) => {
              if (para.startsWith("## ")) {
                return <h2 key={i} className="text-lg font-bold text-[#1a1a1a] mt-7 mb-3">{para.replace("## ", "")}</h2>;
              }
              if (para.match(/^\d+\./)) {
                const items = para.split("\n").filter(Boolean);
                return (
                  <ol key={i} className="list-decimal pl-5 space-y-2 text-sm text-[#555]">
                    {items.map((item, j) => <li key={j}>{item.replace(/^\d+\.\s\*\*[^*]+\*\*\s/, (m) => m).replace(/\*\*([^*]+)\*\*/g, "$1")}</li>)}
                  </ol>
                );
              }
              return <p key={i} className="text-sm text-[#555] leading-relaxed mb-4">{para}</p>;
            })}
          </article>

          {/* Share */}
          <div className="flex items-center gap-3 mt-6">
            <Share2 className="w-4 h-4 text-[#155c32]" />
            <span className="text-sm font-semibold text-[#1a1a1a]">Share this article</span>
          </div>

          <div className="mt-8 flex gap-3">
            <Link href="/blogs" className="inline-flex items-center gap-2 h-10 px-5 rounded-xl border border-[#e7ece8] text-sm font-semibold text-[#555] hover:border-[#155c32] hover:text-[#155c32] transition">
              <ArrowLeft className="w-4 h-4" /> All Articles
            </Link>
            <Link href="/order" className="inline-flex items-center gap-2 h-10 px-5 rounded-xl bg-[#155c32] text-white text-sm font-semibold hover:bg-[#0d3a1f] transition">
              Order Fuel Now
            </Link>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  );
}
