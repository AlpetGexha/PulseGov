import { useState } from 'react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import {
    BarChart3, LineChart, PieChart, Activity,
    ThumbsUp, AlertTriangle, Lightbulb, HelpCircle,
    Award, MessageSquare, Users, MapPin, Clock,
    TrendingUp, ArrowUpRight, ArrowDownRight, CheckCircle2
} from 'lucide-react';
import {
    BarChart, Bar, XAxis, YAxis, CartesianGrid,
    Tooltip, ResponsiveContainer, PieChart as RePieChart,
    Pie, Cell, LineChart as ReLineChart, Line, Legend,
    AreaChart, Area
} from 'recharts';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Dummy data for the dashboard
const statsData = [
    {
        title: 'Total Feedback',
        value: '1,247',
        change: '+12.5%',
        trend: 'up',
        description: 'From last month',
        icon: MessageSquare
    },
    {
        title: 'Active Citizens',
        value: '856',
        change: '+18.2%',
        trend: 'up',
        description: 'Engaged users',
        icon: Users
    },
    {
        title: 'Response Rate',
        value: '87%',
        change: '+5.1%',
        trend: 'up',
        description: 'Official responses',
        icon: Activity
    },
    {
        title: 'Resolution Time',
        value: '8.2 days',
        change: '-2.3 days',
        trend: 'up',
        description: 'Average resolution',
        icon: Clock
    }
];

const feedbackTypeData = [
    { name: 'Suggestions', value: 421, color: '#45ADF2' },
    { name: 'Issues', value: 389, color: '#FB7185' },
    { name: 'Questions', value: 256, color: '#A78BFA' },
    { name: 'Compliments', value: 181, color: '#34D399' }
];

const sentimentData = [
    { name: 'Positive', value: 42, color: '#34D399' },
    { name: 'Neutral', value: 38, color: '#A78BFA' },
    { name: 'Negative', value: 20, color: '#FB7185' }
];

const monthlyFeedbackData = [
    { name: 'Jan', suggestions: 65, issues: 78, questions: 32, compliments: 25 },
    { name: 'Feb', suggestions: 59, issues: 65, questions: 37, compliments: 28 },
    { name: 'Mar', suggestions: 80, issues: 59, questions: 42, compliments: 29 },
    { name: 'Apr', suggestions: 81, issues: 56, questions: 26, compliments: 21 },
    { name: 'May', suggestions: 56, issues: 55, questions: 30, compliments: 12 },
    { name: 'Jun', suggestions: 55, issues: 40, questions: 29, compliments: 17 },
    { name: 'Jul', suggestions: 72, issues: 42, questions: 31, compliments: 19 },
    { name: 'Aug', suggestions: 65, issues: 49, questions: 22, compliments: 24 },
    { name: 'Sep', suggestions: 53, issues: 52, questions: 25, compliments: 19 },
    { name: 'Oct', suggestions: 70, issues: 58, questions: 27, compliments: 22 },
    { name: 'Nov', suggestions: 88, issues: 62, questions: 35, compliments: 31 },
    { name: 'Dec', suggestions: 74, issues: 64, questions: 30, compliments: 29 }
];

const locationData = [
    { name: 'Downtown', value: 356, color: '#45ADF2' },
    { name: 'Westside', value: 289, color: '#A78BFA' },
    { name: 'Eastside', value: 221, color: '#FB7185' },
    { name: 'Northend', value: 198, color: '#38BDF8' },
    { name: 'Southside', value: 183, color: '#34D399' }
];

const recentFeedbackData = [
    {
        id: 1,
        title: 'Park maintenance needed in Jefferson Square',
        type: 'complaint',
        created_at: '2023-11-15T14:32:00',
        user: {
            name: 'Maria Rodriguez',
            avatar: null
        },
        votes: 24,
        comments: 8,
        status: 'In Progress',
        department: 'Parks & Recreation'
    },
    {
        id: 2,
        title: 'Traffic signal timing on Main Street needs adjustment',
        type: 'suggestion',
        created_at: '2023-11-14T09:46:00',
        user: {
            name: 'David Chen',
            avatar: null
        },
        votes: 42,
        comments: 12,
        status: 'Under Review',
        department: 'Transportation'
    },
    {
        id: 3,
        title: 'Great service at the new community center',
        type: 'compliment',
        created_at: '2023-11-13T16:15:00',
        user: {
            name: 'Sarah Johnson',
            avatar: null
        },
        votes: 18,
        comments: 4,
        status: 'Acknowledged',
        department: 'Community Services'
    },
    {
        id: 4,
        title: 'When will the library renovation be completed?',
        type: 'question',
        created_at: '2023-11-12T11:22:00',
        user: {
            name: 'Michael Thompson',
            avatar: null
        },
        votes: 15,
        comments: 6,
        status: 'Answered',
        department: 'Library Services'
    }
];

// Helper function to format date
const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

// Helper function to get feedback type icon
const getFeedbackTypeIcon = (type: string) => {
    switch (type) {
        case 'complaint':
            return <AlertTriangle className="h-4 w-4" />;
        case 'suggestion':
            return <Lightbulb className="h-4 w-4" />;
        case 'question':
            return <HelpCircle className="h-4 w-4" />;
        case 'compliment':
            return <Award className="h-4 w-4" />;
        default:
            return <MessageSquare className="h-4 w-4" />;
    }
};

// Helper function to get status color
const getStatusColor = (status: string): string => {
    switch (status) {
        case 'Resolved':
            return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'In Progress':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'Under Review':
            return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400';
        case 'Acknowledged':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400';
        case 'Answered':
            return 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
    }
};

export default function Dashboard() {
    const [timeRange, setTimeRange] = useState<string>('month');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />

            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                {/* Welcome header */}
                <div>
                    <h1 className="text-3xl font-bold">Welcome to PulseGov Dashboard</h1>
                    <p className="mt-1 text-muted-foreground">
                        Track citizen feedback and monitor engagement with your community
                    </p>
                </div>

                {/* Stats Section */}
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {statsData.map((stat, index) => {
                        const StatIcon = stat.icon;
                        return (
                            <Card key={index}>
                                <CardContent className="p-6">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <p className="text-sm font-medium text-muted-foreground">{stat.title}</p>
                                            <div className="flex items-baseline gap-2">
                                                <h3 className="text-2xl font-bold">{stat.value}</h3>
                                                <span className={`flex items-center text-xs font-medium ${
                                                    stat.trend === 'up' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'
                                                }`}>
                                                    {stat.trend === 'up' ? <ArrowUpRight className="h-3 w-3 mr-1" /> : <ArrowDownRight className="h-3 w-3 mr-1" />}
                                                    {stat.change}
                                                </span>
                                            </div>
                                            <p className="mt-1 text-xs text-muted-foreground">{stat.description}</p>
                                        </div>
                                        <div className="rounded-xl bg-blue-100 p-3 text-[#2E79B5] dark:bg-[#1E3A5A]/30 dark:text-[#4993CC]">
                                            <StatIcon className="h-5 w-5" />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>

                {/* Main charts section */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                    {/* Feedback over time chart */}
                    <Card className="lg:col-span-2">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <div>
                                <CardTitle>Feedback Trends</CardTitle>
                                <CardDescription>Feedback submission trends over time</CardDescription>
                            </div>
                            <Tabs defaultValue="month" value={timeRange} onValueChange={setTimeRange}>
                                <TabsList>
                                    <TabsTrigger value="week">Week</TabsTrigger>
                                    <TabsTrigger value="month">Month</TabsTrigger>
                                    <TabsTrigger value="year">Year</TabsTrigger>
                                </TabsList>
                            </Tabs>
                        </CardHeader>
                        <CardContent className="px-2">
                            <div className="h-[300px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <AreaChart
                                        data={monthlyFeedbackData}
                                        margin={{ top: 10, right: 30, left: 0, bottom: 0 }}
                                    >
                                        <defs>
                                            <linearGradient id="colorSuggestions" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#45ADF2" stopOpacity={0.8}/>
                                                <stop offset="95%" stopColor="#45ADF2" stopOpacity={0.1}/>
                                            </linearGradient>
                                            <linearGradient id="colorIssues" x1="0" y1="0" x2="0" y2="1">
                                                <stop offset="5%" stopColor="#FB7185" stopOpacity={0.8}/>
                                                <stop offset="95%" stopColor="#FB7185" stopOpacity={0.1}/>
                                            </linearGradient>
                                        </defs>
                                        <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#e5e7eb" />
                                        <XAxis dataKey="name" axisLine={false} tickLine={false} />
                                        <YAxis axisLine={false} tickLine={false} />
                                        <Tooltip
                                            contentStyle={{
                                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                                borderRadius: '8px',
                                                border: '1px solid #e5e7eb',
                                                boxShadow: '0 1px 3px rgba(0,0,0,0.1)'
                                            }}
                                        />
                                        <Legend />
                                        <Area
                                            type="monotone"
                                            dataKey="suggestions"
                                            stroke="#45ADF2"
                                            fillOpacity={1}
                                            fill="url(#colorSuggestions)"
                                        />
                                        <Area
                                            type="monotone"
                                            dataKey="issues"
                                            stroke="#FB7185"
                                            fillOpacity={1}
                                            fill="url(#colorIssues)"
                                        />
                                    </AreaChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Feedback type distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Feedback Distribution</CardTitle>
                            <CardDescription>Breakdown by feedback type</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[300px] w-full">
                                <ResponsiveContainer width="100%" height="100%">
                                    <RePieChart>
                                        <Pie
                                            data={feedbackTypeData}
                                            cx="50%"
                                            cy="50%"
                                            innerRadius={60}
                                            outerRadius={90}
                                            paddingAngle={4}
                                            dataKey="value"
                                            label={(entry) => entry.name}
                                            labelLine={false}
                                        >
                                            {feedbackTypeData.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={entry.color} />
                                            ))}
                                        </Pie>
                                        <Tooltip
                                            formatter={(value, name) => [`${value} (${((Number(value) / 1247) * 100).toFixed(1)}%)`, name]}
                                            contentStyle={{
                                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                                borderRadius: '8px',
                                                border: '1px solid #e5e7eb',
                                                boxShadow: '0 1px 3px rgba(0,0,0,0.1)'
                                            }}
                                        />
                                    </RePieChart>
                                </ResponsiveContainer>
                            </div>
                            <div className="mt-4 grid grid-cols-2 gap-4">
                                {feedbackTypeData.map((item) => (
                                    <div key={item.name} className="flex items-center gap-2">
                                        <div
                                            className="h-3 w-3 rounded-full"
                                            style={{ backgroundColor: item.color }}
                                        />
                                        <span className="text-sm text-muted-foreground">
                                            {item.name}: {item.value} ({((item.value / 1247) * 100).toFixed(1)}%)
                                        </span>
                                    </div>
                                ))}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Secondary charts section */}
                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {/* Location distribution */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Location Distribution</CardTitle>
                            <CardDescription>Feedback by neighborhood</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="h-[300px]">
                                <ResponsiveContainer width="100%" height="100%">
                                    <BarChart
                                        data={locationData}
                                        layout="vertical"
                                        margin={{ top: 5, right: 30, left: 20, bottom: 5 }}
                                    >
                                        <CartesianGrid strokeDasharray="3 3" horizontal={true} vertical={false} />
                                        <XAxis type="number" axisLine={false} tickLine={false} />
                                        <YAxis
                                            dataKey="name"
                                            type="category"
                                            axisLine={false}
                                            tickLine={false}
                                            width={80}
                                        />
                                        <Tooltip
                                            contentStyle={{
                                                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                                borderRadius: '8px',
                                                border: '1px solid #e5e7eb',
                                                boxShadow: '0 1px 3px rgba(0,0,0,0.1)'
                                            }}
                                        />
                                        <Bar
                                            dataKey="value"
                                            radius={[0, 4, 4, 0]}
                                        >
                                            {locationData.map((entry, index) => (
                                                <Cell key={`cell-${index}`} fill={entry.color} />
                                            ))}
                                        </Bar>
                                    </BarChart>
                                </ResponsiveContainer>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Sentiment analysis */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Sentiment Analysis</CardTitle>
                            <CardDescription>AI-analyzed sentiment distribution</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="flex h-[300px] items-center justify-center">
                                <div className="flex w-full max-w-sm flex-col items-center">
                                    <ResponsiveContainer width="100%" height={180}>
                                        <RePieChart>
                                            <Pie
                                                data={sentimentData}
                                                cx="50%"
                                                cy="50%"
                                                startAngle={180}
                                                endAngle={0}
                                                innerRadius={60}
                                                outerRadius={80}
                                                paddingAngle={4}
                                                dataKey="value"
                                            >
                                                {sentimentData.map((entry, index) => (
                                                    <Cell key={`cell-${index}`} fill={entry.color} />
                                                ))}
                                            </Pie>
                                            <Tooltip
                                                formatter={(value, name) => [`${value}%`, name]}
                                                contentStyle={{
                                                    backgroundColor: 'rgba(255, 255, 255, 0.9)',
                                                    borderRadius: '8px',
                                                    border: '1px solid #e5e7eb',
                                                    boxShadow: '0 1px 3px rgba(0,0,0,0.1)'
                                                }}
                                            />
                                        </RePieChart>
                                    </ResponsiveContainer>
                                    <div className="mt-6 grid grid-cols-3 gap-4 text-center">
                                        {sentimentData.map((item) => (
                                            <div key={item.name} className="flex flex-col items-center">
                                                <div
                                                    className="flex h-8 w-8 items-center justify-center rounded-full"
                                                    style={{ backgroundColor: item.color }}
                                                >
                                                    {item.name === 'Positive' && <ThumbsUp className="h-4 w-4 text-white" />}
                                                    {item.name === 'Neutral' && <Activity className="h-4 w-4 text-white" />}
                                                    {item.name === 'Negative' && <AlertTriangle className="h-4 w-4 text-white" />}
                                                </div>
                                                <p className="mt-1 text-sm font-medium">{item.name}</p>
                                                <p className="text-xl font-bold">{item.value}%</p>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Recent feedback section */}
                <Card>
                    <CardHeader>
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>Recent Feedback</CardTitle>
                                <CardDescription>Latest submissions from citizens</CardDescription>
                            </div>
                            <Link href="/feedback">
                                <Button variant="outline" size="sm">View All</Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {recentFeedbackData.map((feedback, index) => (
                                <div key={feedback.id}>
                                    <div className="flex items-start gap-4">
                                        <Avatar className="h-10 w-10">
                                            {feedback.user.avatar ? (
                                                <AvatarImage src={feedback.user.avatar} alt={feedback.user.name} />
                                            ) : (
                                                <AvatarFallback className="bg-blue-100 text-[#2E79B5]">
                                                    {feedback.user.name.charAt(0)}
                                                </AvatarFallback>
                                            )}
                                        </Avatar>
                                        <div className="flex-1 space-y-1">
                                            <div className="flex flex-wrap items-center gap-2">
                                                <Link href={`/feedback/${feedback.id}`} className="font-medium hover:underline">
                                                    {feedback.title}
                                                </Link>
                                                <Badge variant="outline" className="flex items-center gap-1">
                                                    {getFeedbackTypeIcon(feedback.type)}
                                                    <span className="capitalize">{feedback.type}</span>
                                                </Badge>
                                                <Badge className={getStatusColor(feedback.status)}>
                                                    {feedback.status}
                                                </Badge>
                                            </div>
                                            <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                                <span>{feedback.user.name}</span>
                                                <span>•</span>
                                                <span className="flex items-center gap-1">
                                                    <Clock className="h-3 w-3" />
                                                    {formatDate(feedback.created_at)}
                                                </span>
                                                <span>•</span>
                                                <span className="flex items-center gap-1">
                                                    <ThumbsUp className="h-3 w-3" />
                                                    {feedback.votes}
                                                </span>
                                                <span>•</span>
                                                <span className="flex items-center gap-1">
                                                    <MessageSquare className="h-3 w-3" />
                                                    {feedback.comments}
                                                </span>
                                                <span>•</span>
                                                <span className="flex items-center gap-1">
                                                    <MapPin className="h-3 w-3" />
                                                    {feedback.department}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    {index < recentFeedbackData.length - 1 && (
                                        <Separator className="mt-4" />
                                    )}
                                </div>
                            ))}
                        </div>
                    </CardContent>
                    <CardFooter className="flex justify-center">
                        <Link href="/feedback">
                            <Button variant="ghost" size="sm">
                                See all feedback
                            </Button>
                        </Link>
                    </CardFooter>
                </Card>
            </div>
        </AppLayout>
    );
}
