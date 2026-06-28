"use client";

import React, { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { ChevronDown, HelpCircle } from "lucide-react";
import { cn } from "@/lib/utils";

interface FAQItem {
  question: string;
  answer: string;
}

const FAQ_ITEMS: FAQItem[] = [
  {
    question: "What is the minimum order quantity for fuel delivery?",
    answer: "Our minimum order quantity for on-site diesel delivery is 100 Liters. This helps us ensure logistics efficiency and offer competitive commercial rates for business operators.",
  },
  {
    question: "What are the standard fuel delivery timelines?",
    answer: "We offer flexible delivery scheduling. Standard deliveries are completed within 4 to 24 hours of vendor confirmation. You can also schedule recurring deliveries at weekly or monthly intervals to avoid downtime.",
  },
  {
    question: "Which payment methods do you support?",
    answer: "We support diverse secure B2B payment methods, including net banking, NEFT/RTGS wire transfers, corporate credit cards, and our dedicated integrated FuelCab Wallet system.",
  },
  {
    question: "What geographical areas do you deliver to?",
    answer: "We currently deliver to industrial sectors, transport hubs, construction corridors, and metropolitan economic zones. You can check local coverage by entering your site pin code inside the dashboard.",
  },
  {
    question: "Do you supply emergency fuel in case of power cuts?",
    answer: "Yes, we support expedited emergency refuelling options for critical infrastructure, hospitals, and factories. Select vendors prioritize emergency requests to restore your power grids quickly.",
  },
  {
    question: "How can local fuel vendors register on FuelCab?",
    answer: "Verified fuel distributors can register by submitting business license details, oil company affiliation proofs, and tanker safety certificates. Once vetted, you can access our bidding board.",
  },
  {
    question: "How do you guarantee fuel quality and density?",
    answer: "Every dispatch comes with a digital refinery quality and density check certificate. Drivers also conduct on-site hydrometer testing upon arrival to ensure zero fuel adulteration.",
  },
  {
    question: "Are invoices compliant with GST regulations?",
    answer: "Yes, all transactions generate automated, download-ready GST invoices specifying tax breakdowns, HSN codes, and vendor credentials directly in your company accounting portal.",
  },
];

export default function FAQ() {
  const [activeIndex, setActiveIndex] = useState<number | null>(null);

  const toggleFAQ = (index: number) => {
    setActiveIndex(activeIndex === index ? null : index);
  };

  // Generate FAQPage JSON-LD schema for SEO optimization
  const faqSchema = {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": FAQ_ITEMS.map((item) => ({
      "@type": "Question",
      "name": item.question,
      "acceptedAnswer": {
        "@type": "Answer",
        "text": item.answer,
      },
    })),
  };

  return (
    <section
      id="faqs"
      className="py-24 bg-white border-b border-[#e7ece8] relative overflow-hidden"
      aria-label="Frequently Asked Questions"
    >
      {/* Schema.org FAQ Structured Data */}
      <script
        type="application/ld+json"
        dangerouslySetInnerHTML={{ __html: JSON.stringify(faqSchema) }}
      />

      {/* Decorative blobs */}
      <div className="absolute inset-0 pointer-events-none opacity-20 z-0">
        <div className="absolute top-1/3 left-0 w-[300px] h-[300px] bg-[#33b248]/5 blur-3xl" />
        <div className="absolute bottom-1/3 right-0 w-[300px] h-[300px] bg-[#155c32]/5 blur-3xl" />
      </div>

      <div className="max-w-[840px] mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        
        {/* Header */}
        <div className="text-center mb-16">
          <motion.span
            initial={{ opacity: 0, y: 10 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="text-xs font-bold uppercase tracking-widest text-[#33b248] mb-3 block"
          >
            Support Center
          </motion.span>
          <motion.h2
            initial={{ opacity: 0, y: 15 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.1 }}
            className="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1a1a1a] mb-4"
          >
            Frequently Asked Questions
          </motion.h2>
          <motion.p
            initial={{ opacity: 0, y: 15 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: 0.2 }}
            className="text-[#555555] text-sm sm:text-base leading-relaxed"
          >
            Find answers to commonly asked questions about our B2B fuel delivery logistics.
          </motion.p>
        </div>

        {/* Accordion List */}
        <div className="flex flex-col gap-4">
          {FAQ_ITEMS.map((item, index) => {
            const isOpen = activeIndex === index;

            return (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 12 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: index * 0.05, duration: 0.4 }}
                className={cn(
                  "bg-white/70 backdrop-blur-md border rounded-2xl overflow-hidden transition-all duration-300",
                  isOpen
                    ? "border-[#33b248] shadow-md shadow-[#33b248]/5 bg-white"
                    : "border-[#e7ece8] hover:border-gray-300 shadow-sm"
                )}
              >
                {/* Trigger Button */}
                <button
                  type="button"
                  onClick={() => toggleFAQ(index)}
                  className="w-full flex items-center justify-between gap-4 p-5 sm:p-6 text-left cursor-pointer transition-colors duration-200"
                  aria-expanded={isOpen}
                  aria-controls={`faq-answer-${index}`}
                  id={`faq-question-${index}`}
                >
                  <div className="flex items-center gap-3">
                    <HelpCircle className={cn("w-5 h-5 flex-shrink-0 transition-colors", isOpen ? "text-[#33b248]" : "text-[#555555]")} />
                    <span className="font-bold text-sm sm:text-base text-[#1a1a1a] tracking-tight">
                      {item.question}
                    </span>
                  </div>
                  
                  <ChevronDown
                    className={cn(
                      "w-5 h-5 text-gray-400 flex-shrink-0 transition-transform duration-300",
                      isOpen && "transform rotate-180 text-[#33b248]"
                    )}
                  />
                </button>

                {/* Animated Answer Panel */}
                <AnimatePresence initial={false}>
                  {isOpen && (
                    <motion.div
                      id={`faq-answer-${index}`}
                      role="region"
                      aria-labelledby={`faq-question-${index}`}
                      initial={{ height: 0, opacity: 0 }}
                      animate={{ height: "auto", opacity: 1 }}
                      exit={{ height: 0, opacity: 0 }}
                      transition={{ duration: 0.25, ease: "easeInOut" }}
                    >
                      <div className="px-5 pb-5 sm:px-6 sm:pb-6 text-xs sm:text-sm text-[#555555] leading-relaxed border-t border-gray-100/50 pt-4">
                        {item.answer}
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </motion.div>
            );
          })}
        </div>

      </div>
    </section>
  );
}
