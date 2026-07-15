import type { ReactNode } from "react";

/**
 * Mobile-first frame. On desktop, presents a phone-like frame in the center.
 * On mobile viewports the frame fills the screen.
 */
export function MobileFrame({ children }: { children: ReactNode }) {
  return (
    <div className="min-h-screen bg-background flex justify-center md:py-8">
      <div className="w-full md:w-[420px] md:h-[900px] md:rounded-[40px] md:border-[8px] md:border-foreground md:shadow-2xl md:overflow-hidden bg-surface flex flex-col relative">
        {children}
      </div>
    </div>
  );
}
