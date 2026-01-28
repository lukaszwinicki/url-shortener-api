import type { Metadata } from "next";
import "./globals.css";
import { Providers } from "./providers";
import { Toaster } from "sonner";
import { Inter } from "next/font/google";
import { Particles } from "@/components/ui/particles";

const inter = Inter({ subsets: ["latin"] });

export const metadata: Metadata = {
  title: "ShortURL - Free URL Shortener",
  description: "Create short URLs with custom aliases and track clicks",
};

export default function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  return (
    <html lang="en">
      <body className={inter.className}>
        <Particles className="fixed inset-0 -z-10" quantity={100} color="#667eea" />
        <Providers>{children}</Providers>
        <Toaster theme="dark" />
      </body>
    </html>
  );
}
