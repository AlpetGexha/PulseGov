import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRightCircle, BarChart3, Building2, Users } from 'lucide-react';
import { useAppearance } from '@/hooks/use-appearance';
import { Button } from '@/components/ui/button';
import { Sun, Moon, Laptop } from 'lucide-react';
import { useEffect, useState } from 'react';
import { 
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;
    
    // Use the built-in appearance hook from Laravel
    const { appearance, setAppearance } = useAppearance();
    
    // Handle client-side mounting to avoid hydration mismatch
    const [mounted, setMounted] = useState(false);
    
    useEffect(() => {
        setMounted(true);
    }, []);

    // Safely render the theme icon based on current theme
    const renderThemeIcon = () => {
        if (!mounted) return null;
        
        if (appearance === 'light') {
            return <Sun className="h-5 w-5 text-[#2E79B5]" />;
        } else if (appearance === 'dark') {
            return <Moon className="h-5 w-5 text-[#2E79B5]" />;
        } else {
            return <Laptop className="h-5 w-5 text-[#2E79B5]" />;
        }
    };

    return (
        <>
            <Head title="PulseGov | Civic Design & Feedback">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="min-h-screen bg-gradient-to-b from-white to-blue-50 dark:from-[#0a0a0a] dark:to-[#112240]">
                {/* Navigation */}
                <header className="w-full border-b border-neutral-200 dark:border-neutral-800">
                    <div className="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                        <Link href="/" className="flex items-center">
                            <img 
                                src="/logo/logo.png" 
                                alt="PulseGov" 
                                className="h-10 w-auto"
                            />
                        </Link>
                        
                        <nav className="flex items-center gap-4">
                            <div className="relative">
                                <DropdownMenu>
                                    <DropdownMenuTrigger asChild>
                                        <Button 
                                            variant="ghost" 
                                            size="icon" 
                                            className="rounded-full"
                                            aria-label="Toggle theme"
                                        >
                                            {renderThemeIcon()}
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end">
                                        <DropdownMenuItem onClick={() => setAppearance('light')}>
                                            <Sun className="mr-2 h-4 w-4 text-[#2E79B5]" />
                                            <span>Light</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem onClick={() => setAppearance('dark')}>
                                            <Moon className="mr-2 h-4 w-4 text-[#2E79B5]" />
                                            <span>Dark</span>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem onClick={() => setAppearance('system')}>
                                            <Laptop className="mr-2 h-4 w-4 text-[#2E79B5]" />
                                            <span>System</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                            
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="rounded-md bg-[#2E79B5] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#2568A0] focus:outline-none focus:ring-2 focus:ring-[#2E79B5] focus:ring-offset-2"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="rounded-md bg-[#2E79B5] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#2568A0] focus:outline-none focus:ring-2 focus:ring-[#2E79B5] focus:ring-offset-2"
                                    >
                                        Register
                                    </Link>
                                </>
                            )}
                        </nav>
                    </div>
                </header>
                
                {/* Hero Section */}
                <section className="py-12 sm:py-16 lg:py-20">
                    <div className="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                        <h1 className="text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl lg:text-6xl dark:text-white">
                            Shape the future of
                            <span className="block text-[#2E79B5] dark:text-[#4993CC]">public services</span>
                        </h1>
                        <p className="mx-auto mt-6 max-w-2xl text-lg text-neutral-600 dark:text-neutral-300">
                            PulseGov connects citizens with government, creating a continuous feedback loop
                            that improves public services through direct civic engagement and design.
                        </p>
                        <div className="mt-10 flex justify-center gap-4">
                            <Link
                                href={route('register')}
                                className="rounded-md bg-[#2E79B5] px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-[#2568A0] focus:outline-none focus:ring-2 focus:ring-[#2E79B5] focus:ring-offset-2"
                            >
                                Get Started
                            </Link>
                            <a
                                href="#how-it-works"
                                className="rounded-md border border-neutral-300 bg-white px-6 py-3 text-base font-medium text-neutral-700 shadow-sm hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-[#2E79B5] focus:ring-offset-2 dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-300 dark:hover:bg-neutral-700"
                            >
                                Learn More
                            </a>
                        </div>
                    </div>
                </section>

                {/* Features Section */}
                <section className="py-12 sm:py-16 lg:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl dark:text-white">
                                How PulseGov Works
                            </h2>
                            <p className="mx-auto mt-3 max-w-2xl text-lg text-neutral-600 dark:text-neutral-300">
                                A seamless platform for citizens to actively participate in shaping digital public services
                            </p>
                        </div>

                        <div id="how-it-works" className="mt-12 grid gap-8 md:grid-cols-3">
                            {/* Feature 1 */}
                            <div className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                                <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-md bg-blue-100 text-[#2E79B5] dark:bg-[#1E3A5A]/30 dark:text-[#4993CC]">
                                    <Users className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-medium text-neutral-900 dark:text-white">Citizen Engagement</h3>
                                <p className="text-neutral-600 dark:text-neutral-400">
                                    Participate directly in the design and improvement of public services through surveys, feedback, and collaborative sessions.
                                </p>
                            </div>

                            {/* Feature 2 */}
                            <div className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                                <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-md bg-blue-100 text-[#2E79B5] dark:bg-[#1E3A5A]/30 dark:text-[#4993CC]">
                                    <BarChart3 className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-medium text-neutral-900 dark:text-white">Continuous Feedback Loop</h3>
                                <p className="text-neutral-600 dark:text-neutral-400">
                                    Provide ongoing feedback that directly influences service improvements, creating a responsive government ecosystem.
                                </p>
                            </div>

                            {/* Feature 3 */}
                            <div className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm dark:border-neutral-800 dark:bg-neutral-900">
                                <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-md bg-blue-100 text-[#2E79B5] dark:bg-[#1E3A5A]/30 dark:text-[#4993CC]">
                                    <Building2 className="h-6 w-6" />
                                </div>
                                <h3 className="mb-2 text-lg font-medium text-neutral-900 dark:text-white">Government Transparency</h3>
                                <p className="text-neutral-600 dark:text-neutral-400">
                                    Track how your input shapes policy decisions and service updates, with transparent reporting on implementation.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* CTA Section */}
                <section className="py-12 sm:py-16 lg:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="rounded-2xl bg-[#2E79B5] px-6 py-16 md:px-12 lg:flex lg:items-center lg:py-20">
                            <div className="lg:w-0 lg:flex-1">
                                <h2 className="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                                    Join the civic design movement
                                </h2>
                                <p className="mt-4 max-w-3xl text-lg text-blue-100">
                                    Be part of creating better public services for everyone. Your voice matters in shaping the future of government services.
                                </p>
                            </div>
                            <div className="mt-12 sm:w-full sm:max-w-md lg:mt-0 lg:ml-8">
                                <Link
                                    href={route('register')}
                                    className="flex w-full items-center justify-center rounded-md border border-transparent bg-white px-5 py-3 text-base font-medium text-[#2E79B5] shadow hover:bg-blue-50"
                                >
                                    Get Started
                                    <ArrowRightCircle className="ml-2 h-5 w-5" />
                                </Link>
                                <p className="mt-3 text-center text-sm text-blue-100">
                                    Already have an account?{' '}
                                    <Link href={route('login')} className="font-medium text-white underline">
                                        Log in
                                    </Link>
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-neutral-200 bg-white py-8 dark:border-neutral-800 dark:bg-[#0a0a0a]">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="flex flex-col items-center justify-between gap-4 md:flex-row">
                            <Link href="/" className="flex items-center">
                                <img 
                                    src="/logo/logo.png" 
                                    alt="PulseGov" 
                                    className="h-10 w-auto"
                                />
                            </Link>
                            <div className="flex items-center gap-4">
                                <div className="relative">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger asChild>
                                            <Button 
                                                variant="ghost" 
                                                size="icon" 
                                                className="rounded-full"
                                                aria-label="Toggle theme"
                                            >
                                                {renderThemeIcon()}
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent align="end">
                                            <DropdownMenuItem onClick={() => setAppearance('light')}>
                                                <Sun className="mr-2 h-4 w-4 text-[#2E79B5]" />
                                                <span>Light</span>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem onClick={() => setAppearance('dark')}>
                                                <Moon className="mr-2 h-4 w-4 text-[#2E79B5]" />
                                                <span>Dark</span>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem onClick={() => setAppearance('system')}>
                                                <Laptop className="mr-2 h-4 w-4 text-[#2E79B5]" />
                                                <span>System</span>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                                <p className="text-sm text-neutral-600 dark:text-neutral-400">
                                    © {new Date().getFullYear()} PulseGov. Empowering citizens to shape public services.
                                </p>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}