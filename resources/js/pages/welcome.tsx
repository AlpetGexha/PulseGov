import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowRightCircle, BarChart3, Building2, Users, Star, ChevronDown,
    CheckCircle2, ChevronRight, Search, Megaphone, LineChart, Shield,
    Settings, MoveRight, LucideIcon, Clock, Award, Heart, LifeBuoy, MessageSquare,
    PenLine, MapPin, Brain, Zap, ThumbsUp, ThumbsDown, UserCheck, FileText,
    Mail, RefreshCw, CalendarCheck, ClipboardCheck, Lightbulb
} from 'lucide-react';
import { useAppearance } from '@/hooks/use-appearance';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Sun, Moon, Laptop } from 'lucide-react';
import { useEffect, useState, useRef } from 'react';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
    DropdownMenuSeparator,
    DropdownMenuGroup,
    DropdownMenuLabel
} from '@/components/ui/dropdown-menu';

// Feature component to make our code more reusable
interface FeatureProps {
    icon: LucideIcon;
    title: string;
    description: string;
}

const Feature = ({ icon: Icon, title, description }: FeatureProps) => {
    return (
        <div className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900">
            <div className="mb-4 inline-flex h-12 w-12 items-center justify-center rounded-md bg-blue-100 text-[#2E79B5] dark:bg-[#1E3A5A]/30 dark:text-[#4993CC]">
                <Icon className="h-6 w-6" />
            </div>
            <h3 className="mb-2 text-lg font-medium text-neutral-900 dark:text-white">{title}</h3>
            <p className="text-neutral-600 dark:text-neutral-400">{description}</p>
        </div>
    );
};

// WorkflowStep component for the visual flow diagram
interface WorkflowStepProps {
    icon: LucideIcon;
    title: string;
    description: string;
    isLast?: boolean;
    details?: {
        icon: LucideIcon;
        text: string;
    }[];
}

const WorkflowStep = ({ icon: Icon, title, description, isLast = false, details = [] }: WorkflowStepProps) => {
    return (
        <div className="relative flex flex-col items-center">
            {/* Step icon with animation */}
            <div className="z-10 mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-[#2E79B5] text-white shadow-md transition-all duration-300 hover:scale-110 dark:bg-[#4993CC]">
                <Icon className="h-8 w-8" />
            </div>

            {/* Title */}
            <h3 className="mb-2 text-center text-xl font-bold text-neutral-900 dark:text-white">{title}</h3>

            {/* Description */}
            <p className="mb-4 max-w-xs text-center text-neutral-600 dark:text-neutral-400">{description}</p>

            {/* Details */}
            {details.length > 0 && (
                <div className="mb-6 w-full max-w-xs space-y-2">
                    {details.map((detail, index) => {
                        const DetailIcon = detail.icon;
                        return (
                            <div key={index} className="flex items-start rounded-lg border border-neutral-200 bg-white/50 p-2 dark:border-neutral-800 dark:bg-neutral-900/50">
                                <DetailIcon className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5] dark:text-[#4993CC]" />
                                <span className="text-sm text-neutral-700 dark:text-neutral-300">{detail.text}</span>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Connector line */}
            {!isLast && (
                <div className="absolute left-1/2 top-16 h-[calc(100%-4rem)] w-0.5 -translate-x-1/2 bg-gradient-to-b from-[#2E79B5] to-transparent"></div>
            )}
        </div>
    );
};

// Testimonial component
interface TestimonialProps {
    quote: string;
    author: string;
    role: string;
    avatar?: string;
}

const Testimonial = ({ quote, author, role, avatar }: TestimonialProps) => {
    return (
        <div className="rounded-xl border border-neutral-200 bg-white p-6 shadow-sm transition-all hover:shadow-md dark:border-neutral-800 dark:bg-neutral-900">
            <div className="mb-4 flex items-center">
                <div className="mr-4 h-12 w-12 overflow-hidden rounded-full bg-gray-200">
                    {avatar ? (
                        <img src={avatar} alt={author} className="h-full w-full object-cover" />
                    ) : (
                        <div className="flex h-full w-full items-center justify-center bg-[#2E79B5] text-white">
                            {author.charAt(0)}
                        </div>
                    )}
                </div>
                <div>
                    <h4 className="font-medium text-neutral-900 dark:text-white">{author}</h4>
                    <p className="text-sm text-neutral-600 dark:text-neutral-400">{role}</p>
                </div>
                <div className="ml-auto flex text-yellow-400">
                    <Star className="h-5 w-5 fill-current" />
                    <Star className="h-5 w-5 fill-current" />
                    <Star className="h-5 w-5 fill-current" />
                    <Star className="h-5 w-5 fill-current" />
                    <Star className="h-5 w-5 fill-current" />
                </div>
            </div>
            <p className="italic text-neutral-600 dark:text-neutral-400">"{quote}"</p>
        </div>
    );
};

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    // Use the built-in appearance hook from Laravel
    const { appearance, setAppearance } = useAppearance();

    // Handle client-side mounting to avoid hydration mismatch
    const [mounted, setMounted] = useState(false);

    // Active section for the header
    const [activeSection, setActiveSection] = useState<string | null>(null);

    // Animation state for workflow diagram
    const [activeStep, setActiveStep] = useState(0);
    const workflowSteps = [
        { icon: PenLine, title: 'Submit Feedback' },
        { icon: Brain, title: 'AI Analysis' },
        { icon: Users, title: 'Community Discussion' },
        { icon: Building2, title: 'Government Action' },
        { icon: Mail, title: 'Citizen Updates' },
    ];

    // Refs for IntersectionObserver
    const workflowRef = useRef(null);

    useEffect(() => {
        setMounted(true);

        // Add scroll event listener to highlight active section in nav
        const handleScroll = () => {
            const sections = document.querySelectorAll('[data-section]');

            sections.forEach(section => {
                const rect = section.getBoundingClientRect();
                if (rect.top <= 100 && rect.bottom >= 100) {
                    setActiveSection(section.getAttribute('data-section'));
                }
            });
        };

        window.addEventListener('scroll', handleScroll);

        // Set up IntersectionObserver for workflow animation
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                // Start the workflow animation when the section is visible
                let step = 0;
                const interval = setInterval(() => {
                    if (step < workflowSteps.length) {
                        setActiveStep(step);
                        step++;
                    } else {
                        clearInterval(interval);
                    }
                }, 800);
            }
        }, { threshold: 0.3 });

        if (workflowRef.current) {
            observer.observe(workflowRef.current);
        }

        return () => {
            window.removeEventListener('scroll', handleScroll);
            observer.disconnect();
        };
    }, [workflowSteps.length]);

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

                        {/* Visual Workflow Diagram */}
                        <div className="mt-16" ref={workflowRef}>
                            <div className="relative mx-auto flex max-w-3xl justify-center">
                                {workflowSteps.map((step, index) => {
                                    const StepIcon = step.icon;
                                    return (
                                        <div key={index} className="relative mx-2 flex-1">
                                            {/* Connector line */}
                                            {index < workflowSteps.length - 1 && (
                                                <div className={`absolute right-0 top-6 h-0.5 w-full -translate-x-1/2 transform bg-gray-200 dark:bg-gray-700 ${
                                                    activeStep > index ? 'bg-gradient-to-r from-[#2E79B5] to-[#4993CC]' : ''
                                                }`}></div>
                                            )}

                                            {/* Circle with icon */}
                                            <div className={`relative z-10 mx-auto flex h-12 w-12 items-center justify-center rounded-full border-2 transition-all duration-500 ease-in-out ${
                                                activeStep >= index
                                                    ? 'border-[#2E79B5] bg-[#2E79B5] text-white scale-110'
                                                    : 'border-gray-300 bg-white text-gray-400 dark:border-gray-600 dark:bg-gray-800'
                                            }`}>
                                                <StepIcon className="h-6 w-6" />
                                            </div>

                                            {/* Step title */}
                                            <div className={`mt-2 text-center text-xs font-medium transition-all duration-500 ${
                                                activeStep >= index
                                                    ? 'text-[#2E79B5] dark:text-[#4993CC]'
                                                    : 'text-gray-500 dark:text-gray-400'
                                            }`}>
                                                {step.title}
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>
                            <p className="mt-8 text-center text-sm text-neutral-600 dark:text-neutral-400">
                                A simple 5-step process to improve public services with your feedback
                            </p>
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
                                A simple platform that connects citizens with government services
                            </p>
                        </div>

                        <div id="how-it-works" className="mt-12 grid gap-8 md:grid-cols-3">
                            {/* Feature 1 */}
                            <Feature
                                icon={PenLine}
                                title="Share Your Experience"
                                description="Easily submit feedback, suggestions, or report issues with public services in your community."
                            />

                            {/* Feature 2 */}
                            <Feature
                                icon={Users}
                                title="Community Collaboration"
                                description="Join other citizens to vote on important issues and discuss improvements for your neighborhood."
                            />

                            {/* Feature 3 */}
                            <Feature
                                icon={Building2}
                                title="Government Action"
                                description="Track how officials respond to your feedback and see real changes happen in your community."
                            />
                        </div>
                    </div>
                </section>

                {/* Workflow Visualization Section */}
                <section className="py-12 bg-gradient-to-r from-blue-50 to-white dark:from-[#112240]/30 dark:to-[#0a0a0a] sm:py-16 lg:py-20">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl dark:text-white">
                                The Feedback Loop
                            </h2>
                            <p className="mx-auto mt-3 max-w-2xl text-lg text-neutral-600 dark:text-neutral-300">
                                How your voice makes a difference in improving public services
                            </p>
                        </div>

                        <div className="mt-16 flex flex-col space-y-20" ref={workflowRef}>
                            {/* Step 1: Citizen Submits Feedback */}
                            <div className="flex flex-col md:flex-row items-center gap-8">
                                <div className="md:w-1/2 flex justify-center">
                                    <div className="relative group">
                                        <div className="relative z-10 rounded-xl border-2 border-[#2E79B5] bg-white p-6 shadow-md transition-all duration-300 group-hover:shadow-lg dark:bg-neutral-900">
                                            <div className="absolute -top-5 -left-5 flex h-12 w-12 items-center justify-center rounded-full bg-[#2E79B5] text-white">
                                                <PenLine className="h-6 w-6" />
                                            </div>
                                            <h3 className="mb-4 pl-8 text-lg font-medium text-neutral-900 dark:text-white">Submit Your Feedback</h3>
                                            <ul className="space-y-3">
                                                <li className="flex items-start">
                                                    <MessageSquare className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Share complaints, suggestions, or questions</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <MapPin className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Location is identified automatically or manually</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <FileText className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Attach photos or documents if needed</span>
                                                </li>
                                            </ul>
                                        </div>
                                        {/* Simple decorative elements */}
                                        <div className="absolute -bottom-2 -right-2 h-16 w-16 rounded-xl border-2 border-dashed border-[#2E79B5]/30 dark:border-[#4993CC]/30"></div>
                                    </div>
                                </div>
                                <div className="md:w-1/2">
                                    <MoveRight className="hidden md:block h-8 w-8 mb-4 text-[#2E79B5]" />
                                    <h4 className="text-xl font-bold text-neutral-900 dark:text-white">Your Voice Matters</h4>
                                    <p className="mt-2 text-neutral-600 dark:text-neutral-400">
                                        Every suggestion or concern you share helps improve services for everyone in your community. It only takes a minute to submit your thoughts!
                                    </p>
                                </div>
                            </div>

                            {/* Step 2: PulseGov Platform Processing */}
                            <div className="flex flex-col md:flex-row-reverse items-center gap-8">
                                <div className="md:w-1/2 flex justify-center">
                                    <div className="relative group">
                                        <div className="relative z-10 rounded-xl border-2 border-[#2E79B5] bg-white p-6 shadow-md transition-all duration-300 group-hover:shadow-lg dark:bg-neutral-900">
                                            <div className="absolute -top-5 -right-5 flex h-12 w-12 items-center justify-center rounded-full bg-[#2E79B5] text-white">
                                                <Brain className="h-6 w-6" />
                                            </div>
                                            <h3 className="mb-4 pr-8 text-lg font-medium text-neutral-900 dark:text-white">Smart Analysis</h3>
                                            <ul className="space-y-3">
                                                <li className="flex items-start">
                                                    <Zap className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>AI analyzes and categorizes your feedback</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <Shield className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Filters out spam and inappropriate content</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <Settings className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Groups similar issues to avoid duplication</span>
                                                </li>
                                            </ul>
                                        </div>
                                        {/* Simple decorative elements */}
                                        <div className="absolute -bottom-2 -left-2 h-16 w-16 rounded-xl border-2 border-dashed border-[#2E79B5]/30 dark:border-[#4993CC]/30"></div>
                                    </div>
                                </div>
                                <div className="md:w-1/2 text-right">
                                    <MoveRight className="hidden md:block h-8 w-8 mb-4 ml-auto rotate-180 text-[#2E79B5]" />
                                    <h4 className="text-xl font-bold text-neutral-900 dark:text-white">Smart Technology Working For You</h4>
                                    <p className="mt-2 text-neutral-600 dark:text-neutral-400">
                                        Our system organizes your feedback into categories and sends it to the right department. This ensures your concerns are addressed efficiently.
                                    </p>
                                </div>
                            </div>

                            {/* Step 3: Community Engagement */}
                            <div className="flex flex-col md:flex-row items-center gap-8">
                                <div className="md:w-1/2 flex justify-center">
                                    <div className="relative group">
                                        <div className="relative z-10 rounded-xl border-2 border-[#2E79B5] bg-white p-6 shadow-md transition-all duration-300 group-hover:shadow-lg dark:bg-neutral-900">
                                            <div className="absolute -top-5 -left-5 flex h-12 w-12 items-center justify-center rounded-full bg-[#2E79B5] text-white">
                                                <Users className="h-6 w-6" />
                                            </div>
                                            <h3 className="mb-4 pl-8 text-lg font-medium text-neutral-900 dark:text-white">Community Support</h3>
                                            <ul className="space-y-3">
                                                <li className="flex items-start">
                                                    <ThumbsUp className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Vote on important issues in your area</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <MessageSquare className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Comment and discuss with neighbors</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <Heart className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Support issues that matter most to you</span>
                                                </li>
                                            </ul>
                                        </div>
                                        {/* Simple decorative elements */}
                                        <div className="absolute -bottom-2 -right-2 h-16 w-16 rounded-xl border-2 border-dashed border-[#2E79B5]/30 dark:border-[#4993CC]/30"></div>
                                    </div>
                                </div>
                                <div className="md:w-1/2">
                                    <MoveRight className="hidden md:block h-8 w-8 mb-4 text-[#2E79B5]" />
                                    <h4 className="text-xl font-bold text-neutral-900 dark:text-white">Stronger Together</h4>
                                    <p className="mt-2 text-neutral-600 dark:text-neutral-400">
                                        Join forces with other citizens to highlight important issues. More support means more attention from government officials.
                                    </p>
                                </div>
                            </div>

                            {/* Step 4: Government Action */}
                            <div className="flex flex-col md:flex-row-reverse items-center gap-8">
                                <div className="md:w-1/2 flex justify-center">
                                    <div className="relative group">
                                        <div className="relative z-10 rounded-xl border-2 border-[#2E79B5] bg-white p-6 shadow-md transition-all duration-300 group-hover:shadow-lg dark:bg-neutral-900">
                                            <div className="absolute -top-5 -right-5 flex h-12 w-12 items-center justify-center rounded-full bg-[#2E79B5] text-white">
                                                <Building2 className="h-6 w-6" />
                                            </div>
                                            <h3 className="mb-4 pr-8 text-lg font-medium text-neutral-900 dark:text-white">Government Response</h3>
                                            <ul className="space-y-3">
                                                <li className="flex items-start">
                                                    <CheckCircle2 className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Officials review and take action</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <Clock className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Updates on status: Open, Working, Resolved</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <BarChart3 className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Track changes and view progress reports</span>
                                                </li>
                                            </ul>
                                        </div>
                                        {/* Simple decorative elements */}
                                        <div className="absolute -bottom-2 -left-2 h-16 w-16 rounded-xl border-2 border-dashed border-[#2E79B5]/30 dark:border-[#4993CC]/30"></div>
                                    </div>
                                </div>
                                <div className="md:w-1/2 text-right">
                                    <MoveRight className="hidden md:block h-8 w-8 mb-4 ml-auto rotate-180 text-[#2E79B5]" />
                                    <h4 className="text-xl font-bold text-neutral-900 dark:text-white">See Real Results</h4>
                                    <p className="mt-2 text-neutral-600 dark:text-neutral-400">
                                        Government officials review feedback, provide updates, and take action. You'll receive notifications as your issue progresses toward resolution.
                                    </p>
                                </div>
                            </div>

                            {/* Step 5: Citizen Updates */}
                            <div className="flex flex-col md:flex-row items-center gap-8">
                                <div className="md:w-1/2 flex justify-center">
                                    <div className="relative group">
                                        <div className="relative z-10 rounded-xl border-2 border-[#2E79B5] bg-white p-6 shadow-md transition-all duration-300 group-hover:shadow-lg dark:bg-neutral-900">
                                            <div className="absolute -top-5 -left-5 flex h-12 w-12 items-center justify-center rounded-full bg-[#2E79B5] text-white">
                                                <Mail className="h-6 w-6" />
                                            </div>
                                            <h3 className="mb-4 pl-8 text-lg font-medium text-neutral-900 dark:text-white">Stay Informed</h3>
                                            <ul className="space-y-3">
                                                <li className="flex items-start">
                                                    <CalendarCheck className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Receive updates about your feedback</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <UserCheck className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Confirm if the issue has been resolved</span>
                                                </li>
                                                <li className="flex items-start">
                                                    <RefreshCw className="mr-2 h-5 w-5 flex-shrink-0 text-[#2E79B5]" />
                                                    <span>Provide follow-up feedback when needed</span>
                                                </li>
                                            </ul>
                                        </div>
                                        {/* Simple decorative elements */}
                                        <div className="absolute -bottom-2 -right-2 h-16 w-16 rounded-xl border-2 border-dashed border-[#2E79B5]/30 dark:border-[#4993CC]/30"></div>
                                    </div>
                                </div>
                                <div className="md:w-1/2">
                                    <div className="flex items-center">
                                        <MoveRight className="hidden md:block h-8 w-8 mb-4 mr-2 text-[#2E79B5]" />
                                        <RefreshCw className="hidden md:block h-8 w-8 mb-4 text-[#2E79B5]" />
                                    </div>
                                    <h4 className="text-xl font-bold text-neutral-900 dark:text-white">Continuous Improvement</h4>
                                    <p className="mt-2 text-neutral-600 dark:text-neutral-400">
                                        The cycle continues as you receive updates and can provide follow-up feedback. This ongoing conversation helps make public services better for everyone.
                                    </p>
                                </div>
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

                {/* FAQ Section */}
                <section className="py-12 sm:py-16 lg:py-20">
                    <div className="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                        <div className="text-center">
                            <h2 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl dark:text-white">
                                Frequently Asked Questions
                            </h2>
                            <p className="mx-auto mt-3 max-w-2xl text-lg text-neutral-600 dark:text-neutral-300">
                                Answers to common questions about PulseGov
                            </p>
                        </div>

                        <div className="mt-12">
                            <Accordion type="single" collapsible className="w-full">
                                <AccordionItem value="item-1">
                                    <AccordionTrigger className="text-lg font-medium text-neutral-900 dark:text-white">
                                        Do I need to create an account to submit feedback?
                                    </AccordionTrigger>
                                    <AccordionContent className="text-neutral-600 dark:text-neutral-300">
                                        Yes, you need to create a free account to submit feedback. This helps us ensure the quality of submissions and allows you to track the progress of your feedback.
                                    </AccordionContent>
                                </AccordionItem>

                                <AccordionItem value="item-2">
                                    <AccordionTrigger className="text-lg font-medium text-neutral-900 dark:text-white">
                                        Will government officials actually see my feedback?
                                    </AccordionTrigger>
                                    <AccordionContent className="text-neutral-600 dark:text-neutral-300">
                                        Yes! PulseGov partners directly with government agencies to ensure your feedback reaches the right people. Officials receive regular reports and can respond directly to citizen concerns.
                                    </AccordionContent>
                                </AccordionItem>

                                <AccordionItem value="item-3">
                                    <AccordionTrigger className="text-lg font-medium text-neutral-900 dark:text-white">
                                        How long does it take to get a response?
                                    </AccordionTrigger>
                                    <AccordionContent className="text-neutral-600 dark:text-neutral-300">
                                        Response times vary depending on the type of feedback and the department involved. You'll receive updates as your feedback moves through the process, and you can always check the status in your dashboard.
                                    </AccordionContent>
                                </AccordionItem>

                                <AccordionItem value="item-4">
                                    <AccordionTrigger className="text-lg font-medium text-neutral-900 dark:text-white">
                                        Is my personal information kept private?
                                    </AccordionTrigger>
                                    <AccordionContent className="text-neutral-600 dark:text-neutral-300">
                                        Yes, your personal information is protected. While government officials can see your feedback, your personal details are only shared according to your privacy settings. You can choose to submit feedback anonymously if you prefer.
                                    </AccordionContent>
                                </AccordionItem>

                                <AccordionItem value="item-5">
                                    <AccordionTrigger className="text-lg font-medium text-neutral-900 dark:text-white">
                                        How can I support other citizens' feedback?
                                    </AccordionTrigger>
                                    <AccordionContent className="text-neutral-600 dark:text-neutral-300">
                                        You can browse public feedback on the community page and add your vote or comments to existing issues. Supporting others' feedback helps government officials identify which issues are most important to the community.
                                    </AccordionContent>
                                </AccordionItem>
                            </Accordion>
                        </div>
                    </div>
                </section>

                {/* Footer */}
                <footer className="border-t border-neutral-200 bg-white py-12 dark:border-neutral-800 dark:bg-[#0a0a0a]">
                    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div className="grid grid-cols-1 gap-8 md:grid-cols-3 lg:grid-cols-4">
                            <div>
                                <Link href="/" className="flex items-center">
                                    <img
                                        src="/logo/logo.png"
                                        alt="PulseGov"
                                        className="h-10 w-auto"
                                    />
                                </Link>
                                <p className="mt-4 text-sm text-neutral-600 dark:text-neutral-400">
                                    Empowering citizens to improve public services through direct feedback and collaboration.
                                </p>
                                <div className="mt-4 flex space-x-4">
                                    <Button variant="ghost" size="icon" aria-label="Facebook">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5 text-[#2E79B5]">
                                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                                        </svg>
                                    </Button>
                                    <Button variant="ghost" size="icon" aria-label="Twitter">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5 text-[#2E79B5]">
                                            <path d="M22 4s-.7 2.1-2 3.4c1.6 10-9.4 17.3-18 11.6 2.2.1 4.4-.6 6-2C3 15.5.5 9.6 3 5c2.2 2.6 5.6 4.1 9 4-.9-4.2 4-6.6 7-3.8 1.1 0 3-1.2 3-1.2z"></path>
                                        </svg>
                                    </Button>
                                    <Button variant="ghost" size="icon" aria-label="Instagram">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-5 w-5 text-[#2E79B5]">
                                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                        </svg>
                                    </Button>
                                </div>
                            </div>

                            <div>
                                <h3 className="text-lg font-medium text-neutral-900 dark:text-white">Quick Links</h3>
                                <ul className="mt-4 space-y-2">
                                    <li>
                                        <a href="#how-it-works" className="text-neutral-600 hover:text-[#2E79B5] dark:text-neutral-400 dark:hover:text-[#4993CC]">
                                            How It Works
                                        </a>
                                    </li>
                                    <li>
                                        <Link href="/about" className="text-neutral-600 hover:text-[#2E79B5] dark:text-neutral-400 dark:hover:text-[#4993CC]">
                                            About Us
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href="/contact" className="text-neutral-600 hover:text-[#2E79B5] dark:text-neutral-400 dark:hover:text-[#4993CC]">
                                            Contact
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href="/privacy" className="text-neutral-600 hover:text-[#2E79B5] dark:text-neutral-400 dark:hover:text-[#4993CC]">
                                            Privacy Policy
                                        </Link>
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h3 className="text-lg font-medium text-neutral-900 dark:text-white">Help Resources</h3>
                                <ul className="mt-4 space-y-2">
                                    <li>
                                        <Link href="/help" className="text-neutral-600 hover:text-[#2E79B5] dark:text-neutral-400 dark:hover:text-[#4993CC]">
                                            Help Center
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href="/guides" className="text-neutral-600 hover:text-[#2E79B5] dark:text-neutral-400 dark:hover:text-[#4993CC]">
                                            User Guides
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href="/accessibility" className="text-neutral-600 hover:text-[#2E79B5] dark:text-neutral-400 dark:hover:text-[#4993CC]">
                                            Accessibility
                                        </Link>
                                    </li>
                                </ul>
                            </div>

                            <div>
                                <h3 className="text-lg font-medium text-neutral-900 dark:text-white">Appearance</h3>
                                <div className="mt-4">
                                    <Button
                                        variant="outline"
                                        onClick={() => setAppearance('light')}
                                        className={`mr-2 ${appearance === 'light' ? 'bg-[#2E79B5] text-white' : ''}`}
                                    >
                                        <Sun className="mr-2 h-4 w-4" />
                                        Light
                                    </Button>
                                    <Button
                                        variant="outline"
                                        onClick={() => setAppearance('dark')}
                                        className={`mr-2 ${appearance === 'dark' ? 'bg-[#2E79B5] text-white' : ''}`}
                                    >
                                        <Moon className="mr-2 h-4 w-4" />
                                        Dark
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <div className="mt-8 border-t border-neutral-200 pt-8 dark:border-neutral-800">
                            <p className="text-center text-sm text-neutral-600 dark:text-neutral-400">
                                © {new Date().getFullYear()} PulseGov. All rights reserved.
                            </p>
                        </div>
                    </div>
                </footer>
            </div>
        </>
    );
}
