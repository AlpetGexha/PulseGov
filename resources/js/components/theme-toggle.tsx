import { Moon, Sun } from "lucide-react";
import { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import { useAppearance } from "@/hooks/use-appearance";
import { Tooltip, TooltipContent, TooltipTrigger } from "@/components/ui/tooltip";
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu";

export function ThemeToggle({ className }: { className?: string }) {
    const { appearance, setAppearance } = useAppearance();
    const [mounted, setMounted] = useState(false);

    // Only render the toggle client-side to avoid hydration mismatch
    useEffect(() => {
        setMounted(true);
    }, []);

    if (!mounted) {
        return <div className={className} />;
    }

    return (
        <div className={className}>
            <DropdownMenu>
                <Tooltip>
                    <TooltipTrigger asChild>
                        <DropdownMenuTrigger asChild>
                            <Button variant="ghost" size="icon">
                                {appearance === "light" ? (
                                    <Sun className="h-5 w-5 text-[#2E79B5]" />
                                ) : appearance === "dark" ? (
                                    <Moon className="h-5 w-5 text-[#2E79B5]" />
                                ) : (
                                    <div className="relative h-5 w-5">
                                        <Sun className="absolute h-5 w-5 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100 text-[#2E79B5]" />
                                        <Moon className="h-5 w-5 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0 text-[#2E79B5]" />
                                    </div>
                                )}
                                <span className="sr-only">Toggle theme</span>
                            </Button>
                        </DropdownMenuTrigger>
                    </TooltipTrigger>
                    <TooltipContent side="bottom">Toggle theme</TooltipContent>
                </Tooltip>
                <DropdownMenuContent align="end">
                    <DropdownMenuItem onClick={() => setAppearance("light")}>
                        <Sun className="mr-2 h-4 w-4 text-[#2E79B5]" />
                        <span>Light</span>
                    </DropdownMenuItem>
                    <DropdownMenuItem onClick={() => setAppearance("dark")}>
                        <Moon className="mr-2 h-4 w-4 text-[#2E79B5]" />
                        <span>Dark</span>
                    </DropdownMenuItem>
                    <DropdownMenuItem onClick={() => setAppearance("system")}>
                        <div className="relative mr-2 h-4 w-4">
                            <Sun className="absolute h-4 w-4 rotate-90 scale-0 transition-all dark:rotate-0 dark:scale-100 text-[#2E79B5]" />
                            <Moon className="h-4 w-4 rotate-0 scale-100 transition-all dark:-rotate-90 dark:scale-0 text-[#2E79B5]" />
                        </div>
                        <span>System</span>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
