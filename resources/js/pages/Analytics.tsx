import React, { useState } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Progress } from '@/components/ui/progress';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    TrendingUp,
    TrendingDown,
    Brain,
    AlertTriangle,
    Calendar,
    Users,
    MessageSquare,
    ThumbsUp,
    ChevronRight,
    Sparkles,
    Target,
    MapPin,
    Clock,
    BarChart3,
    PieChart as PieChartIcon,
    Activity,
    Zap,
    FileText,
    Info,
    Lightbulb,
    CheckCircle,
    XCircle,
    RefreshCw,
    Star
} from 'lucide-react';

interface FeedbackTopic {
    id: string;
    topic: string;
    category: string;
    description: string;
    urgency_score: number;
    sentiment_score: number;
    frequency: number;
    impact_score: number;
    priority_score: number;
    department: string;
    feedback_count: number;
    locations: string[];
    timeframe: string;
    trend: 'up' | 'down' | 'stable';
    recommended_action: string;
    ai_summary: string;
    related_keywords: string[];
    citizen_voices: {
        positive: number;
        negative: number;
        neutral: number;
    };
}

interface AnalyticsData {
    prioritized_topics: FeedbackTopic[];
    insights: {
        total_feedback: number;
        analyzed_feedback: number;
        top_concerns: string[];
        sentiment_distribution: {
            positive: number;
            negative: number;
            neutral: number;
        };
        urgency_distribution: {
            critical: number;
            high: number;
            medium: number;
            low: number;
        };
        department_workload: Record<string, number>;
        location_hotspots: Array<{
            location: string;
            count: number;
            avg_urgency: number;
        }>;
        trending_topics: Array<{
            topic: string;
            change: number;
            trend: 'up' | 'down' | 'stable';
        }>;
    };
    ai_recommendations: {
        immediate_actions: string[];
        long_term_strategies: string[];
        resource_allocation: string[];
        communication_strategies: string[];
    };
    performance_metrics: {
        response_time: number;
        resolution_rate: number;
        citizen_satisfaction: number;
        engagement_rate: number;
    };
    generated_at: string;
}

interface AnalyticsProps {
    analytics: AnalyticsData;
    auth: any;
}

const COLORS = ['#3b82f6', '#ef4444', '#f59e0b', '#10b981', '#8b5cf6', '#f97316', '#06b6d4', '#84cc16'];

export default function Analytics({ analytics, auth }: AnalyticsProps) {
    const [selectedTopic, setSelectedTopic] = useState<FeedbackTopic | null>(null);
    const [isLoading, setIsLoading] = useState(false);
    const [activeTab, setActiveTab] = useState('priorities');

    const handleRefresh = async () => {
        setIsLoading(true);
        // Simulate API call
        setTimeout(() => {
            setIsLoading(false);
        }, 2000);
    };

    const getPriorityColor = (score: number) => {
        if (score >= 80) return 'bg-red-500';
        if (score >= 60) return 'bg-orange-500';
        if (score >= 40) return 'bg-yellow-500';
        return 'bg-green-500';
    };

    const getTrendIcon = (trend: 'up' | 'down' | 'stable') => {
        switch (trend) {
            case 'up': return <TrendingUp className="h-4 w-4 text-red-500" />;
            case 'down': return <TrendingDown className="h-4 w-4 text-green-500" />;
            default: return <Activity className="h-4 w-4 text-gray-500" />;
        }
    };

    const formatDate = (date: string) => {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <AppLayout>
            <Head title="Analytics Dashboard" />

            <div className="space-y-6 container mx-auto px-4 sm:px-6 lg:px-8 mt-8">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                            AI-Powered Analytics
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            Smart insights and prioritized feedback analysis
                        </p>
                    </div>
                    <div className="flex items-center gap-4 mt-4 sm:mt-0">                    <div className="text-sm text-gray-500 dark:text-gray-400">
                        <Clock className="h-4 w-4 inline mr-1" />
                        Last updated: {formatDate(analytics.generated_at)}
                    </div>
                        <Button
                            onClick={handleRefresh}
                            disabled={isLoading}
                            className="bg-[#2E79B5] hover:bg-[#2568A0]"
                        >
                            {isLoading ? (
                                <RefreshCw className="h-4 w-4 animate-spin mr-2" />
                            ) : (
                                <RefreshCw className="h-4 w-4 mr-2" />
                            )}
                            Refresh
                        </Button>
                    </div>
                </div>

                {/* Key Metrics */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Total Feedback</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">
                                        {analytics.insights.total_feedback.toLocaleString()}
                                    </p>
                                </div>
                                <MessageSquare className="h-8 w-8 text-[#2E79B5]" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">AI Analyzed</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">
                                        {analytics.insights.analyzed_feedback.toLocaleString()}
                                    </p>
                                    <p className="text-xs text-gray-500 dark:text-gray-400">
                                        {((analytics.insights.analyzed_feedback / analytics.insights.total_feedback) * 100).toFixed(1)}% coverage
                                    </p>
                                </div>
                                <Brain className="h-8 w-8 text-purple-500" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Response Time</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">
                                        {analytics.performance_metrics.response_time}h
                                    </p>
                                </div>
                                <Clock className="h-8 w-8 text-orange-500" />
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardContent className="p-6">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-sm font-medium text-gray-600 dark:text-gray-400">Satisfaction</p>
                                    <p className="text-2xl font-bold text-gray-900 dark:text-white">
                                        {analytics.performance_metrics.citizen_satisfaction}%
                                    </p>
                                </div>
                                <ThumbsUp className="h-8 w-8 text-green-500" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Main Content Tabs */}
                <Tabs value={activeTab} onValueChange={setActiveTab} className="space-y-6">
                    <TabsList className="grid w-full grid-cols-4">
                        <TabsTrigger value="priorities">
                            <Target className="h-4 w-4 mr-2" />
                            Priorities
                        </TabsTrigger>
                        <TabsTrigger value="insights">
                            <Brain className="h-4 w-4 mr-2" />
                            Insights
                        </TabsTrigger>
                        <TabsTrigger value="trends">
                            <TrendingUp className="h-4 w-4 mr-2" />
                            Trends
                        </TabsTrigger>
                        <TabsTrigger value="recommendations">
                            <Lightbulb className="h-4 w-4 mr-2" />
                            Actions
                        </TabsTrigger>
                    </TabsList>

                    {/* Priorities Tab */}
                    <TabsContent value="priorities" className="space-y-6">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Priority Topics List */}
                            <Card className="lg:col-span-2">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Target className="h-5 w-5" />
                                        AI-Prioritized Topics
                                    </CardTitle>
                                    <CardDescription>
                                        Topics ranked by AI analysis considering urgency, impact, and citizen sentiment
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {analytics.prioritized_topics.slice(0, 10).map((topic, index) => (
                                        <div
                                            key={topic.id}
                                            className={`p-4 rounded-lg border cursor-pointer transition-all ${
                                                selectedTopic?.id === topic.id
                                                    ? 'border-[#2E79B5] bg-blue-50 dark:bg-blue-900/20'
                                                    : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600'
                                            }`}
                                            onClick={() => setSelectedTopic(topic)}
                                        >
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1">
                                                    <div className="flex items-center gap-3 mb-2">
                                                        <div className="flex items-center gap-2">
                                                            <span className="text-sm font-medium text-gray-500 dark:text-gray-400">
                                                                #{index + 1}
                                                            </span>
                                                            <div className={`w-3 h-3 rounded-full ${getPriorityColor(topic.priority_score)}`} />
                                                        </div>
                                                        <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                                            {topic.topic}
                                                        </h3>
                                                        {getTrendIcon(topic.trend)}
                                                    </div>

                                                    <p className="text-gray-600 dark:text-gray-400 mb-3">
                                                        {topic.description}
                                                    </p>

                                                    <div className="flex flex-wrap items-center gap-2 mb-3">
                                                        <Badge variant="outline" className="flex items-center gap-1">
                                                            <MapPin className="h-3 w-3" />
                                                            {topic.department}
                                                        </Badge>
                                                        <Badge variant="secondary">
                                                            {topic.feedback_count} reports
                                                        </Badge>
                                                        <Badge variant="outline" className="flex items-center gap-1">
                                                            <Users className="h-3 w-3" />
                                                            {topic.locations.length} locations
                                                        </Badge>
                                                    </div>

                                                    <div className="flex items-center gap-4 mb-2">
                                                        <div className="flex-1">
                                                            <div className="flex items-center justify-between text-sm mb-1">
                                                                <span className="text-gray-600 dark:text-gray-400">Priority Score</span>
                                                                <span className="font-medium">{topic.priority_score}/100</span>
                                                            </div>
                                                            <Progress value={topic.priority_score} className="h-2" />
                                                        </div>
                                                    </div>
                                                </div>
                                                <ChevronRight className="h-5 w-5 text-gray-400" />
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>

                            {/* Topic Details */}
                            {selectedTopic && (
                                <Card className="lg:col-span-2">
                                    <CardHeader>
                                        <CardTitle className="flex items-center gap-2">
                                            <FileText className="h-5 w-5" />
                                            Topic Analysis: {selectedTopic.topic}
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="space-y-6">
                                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                <p className="text-sm text-gray-600 dark:text-gray-400">Urgency Score</p>
                                                <p className="text-2xl font-bold text-red-600">{selectedTopic.urgency_score}/100</p>
                                            </div>
                                            <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                <p className="text-sm text-gray-600 dark:text-gray-400">Impact Score</p>
                                                <p className="text-2xl font-bold text-orange-600">{selectedTopic.impact_score}/100</p>
                                            </div>
                                            <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                                <p className="text-sm text-gray-600 dark:text-gray-400">Frequency</p>
                                                <p className="text-2xl font-bold text-blue-600">{selectedTopic.frequency}</p>
                                            </div>
                                        </div>

                                        <div>
                                            <h4 className="font-semibold mb-2">AI Summary</h4>
                                            <p className="text-gray-600 dark:text-gray-400">{selectedTopic.ai_summary}</p>
                                        </div>

                                        <div>
                                            <h4 className="font-semibold mb-2">Recommended Action</h4>
                                            <Alert>
                                                <Lightbulb className="h-4 w-4" />
                                                <AlertDescription>
                                                    {selectedTopic.recommended_action}
                                                </AlertDescription>
                                            </Alert>
                                        </div>

                                        <div>
                                            <h4 className="font-semibold mb-2">Citizen Sentiment</h4>
                                            <div className="grid grid-cols-3 gap-2">
                                                <div className="p-2 bg-green-50 dark:bg-green-900/20 rounded text-center">
                                                    <p className="text-sm text-green-600 dark:text-green-400">Positive</p>
                                                    <p className="font-bold text-green-700 dark:text-green-300">
                                                        {selectedTopic.citizen_voices.positive}%
                                                    </p>
                                                </div>
                                                <div className="p-2 bg-gray-50 dark:bg-gray-800 rounded text-center">
                                                    <p className="text-sm text-gray-600 dark:text-gray-400">Neutral</p>
                                                    <p className="font-bold text-gray-700 dark:text-gray-300">
                                                        {selectedTopic.citizen_voices.neutral}%
                                                    </p>
                                                </div>
                                                <div className="p-2 bg-red-50 dark:bg-red-900/20 rounded text-center">
                                                    <p className="text-sm text-red-600 dark:text-red-400">Negative</p>
                                                    <p className="font-bold text-red-700 dark:text-red-300">
                                                        {selectedTopic.citizen_voices.negative}%
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <h4 className="font-semibold mb-2">Related Keywords</h4>
                                            <div className="flex flex-wrap gap-2">
                                                {selectedTopic.related_keywords.map((keyword, index) => (
                                                    <Badge key={index} variant="outline">
                                                        {keyword}
                                                    </Badge>
                                                ))}
                                            </div>
                                        </div>
                                    </CardContent>
                                </Card>
                            )}
                        </div>
                    </TabsContent>

                    {/* Insights Tab */}
                    <TabsContent value="insights" className="space-y-6">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Sentiment Distribution */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Sentiment Distribution</CardTitle>
                                    <CardDescription>Overall citizen sentiment analysis</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="h-[300px]">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie
                                                    data={[
                                                        { name: 'Positive', value: analytics.insights.sentiment_distribution.positive, color: '#10b981' },
                                                        { name: 'Neutral', value: analytics.insights.sentiment_distribution.neutral, color: '#6b7280' },
                                                        { name: 'Negative', value: analytics.insights.sentiment_distribution.negative, color: '#ef4444' }
                                                    ]}
                                                    cx="50%"
                                                    cy="50%"
                                                    innerRadius={60}
                                                    outerRadius={100}
                                                    paddingAngle={2}
                                                    dataKey="value"
                                                >
                                                    {[
                                                        { name: 'Positive', value: analytics.insights.sentiment_distribution.positive, color: '#10b981' },
                                                        { name: 'Neutral', value: analytics.insights.sentiment_distribution.neutral, color: '#6b7280' },
                                                        { name: 'Negative', value: analytics.insights.sentiment_distribution.negative, color: '#ef4444' }
                                                    ].map((entry, index) => (
                                                        <Cell key={`cell-${index}`} fill={entry.color} />
                                                    ))}
                                                </Pie>
                                                <Tooltip />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Urgency Distribution */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Urgency Distribution</CardTitle>
                                    <CardDescription>Feedback urgency levels</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="h-[300px]">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <BarChart
                                                data={[
                                                    { name: 'Critical', value: analytics.insights.urgency_distribution.critical, color: '#ef4444' },
                                                    { name: 'High', value: analytics.insights.urgency_distribution.high, color: '#f59e0b' },
                                                    { name: 'Medium', value: analytics.insights.urgency_distribution.medium, color: '#3b82f6' },
                                                    { name: 'Low', value: analytics.insights.urgency_distribution.low, color: '#10b981' }
                                                ]}
                                            >
                                                <CartesianGrid strokeDasharray="3 3" />
                                                <XAxis dataKey="name" />
                                                <YAxis />
                                                <Tooltip />
                                                <Bar dataKey="value" radius={[4, 4, 0, 0]}>
                                                    {[
                                                        { name: 'Critical', value: analytics.insights.urgency_distribution.critical, color: '#ef4444' },
                                                        { name: 'High', value: analytics.insights.urgency_distribution.high, color: '#f59e0b' },
                                                        { name: 'Medium', value: analytics.insights.urgency_distribution.medium, color: '#3b82f6' },
                                                        { name: 'Low', value: analytics.insights.urgency_distribution.low, color: '#10b981' }
                                                    ].map((entry, index) => (
                                                        <Cell key={`cell-${index}`} fill={entry.color} />
                                                    ))}
                                                </Bar>
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Department Workload */}
                            <Card className="lg:col-span-2">
                                <CardHeader>
                                    <CardTitle>Department Workload</CardTitle>
                                    <CardDescription>Feedback distribution across departments</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="h-[300px]">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <BarChart
                                                data={Object.entries(analytics.insights.department_workload).map(([dept, count]) => ({
                                                    name: dept,
                                                    value: count
                                                }))}
                                            >
                                                <CartesianGrid strokeDasharray="3 3" />
                                                <XAxis dataKey="name" />
                                                <YAxis />
                                                <Tooltip />
                                                <Bar dataKey="value" fill="#2E79B5" radius={[4, 4, 0, 0]} />
                                            </BarChart>
                                        </ResponsiveContainer>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    {/* Trends Tab */}
                    <TabsContent value="trends" className="space-y-6">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Trending Topics */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Trending Topics</CardTitle>
                                    <CardDescription>Topics gaining or losing momentum</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {analytics.insights.trending_topics.map((topic, index) => (
                                        <div key={index} className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                            <div className="flex items-center gap-3">
                                                {getTrendIcon(topic.trend)}
                                                <span className="font-medium">{topic.topic}</span>
                                            </div>
                                            <div className="flex items-center gap-2">
                                                <span className={`text-sm ${
                                                    topic.trend === 'up' ? 'text-red-600' :
                                                    topic.trend === 'down' ? 'text-green-600' : 'text-gray-600'
                                                }`}>
                                                    {topic.change > 0 ? '+' : ''}{topic.change}%
                                                </span>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>

                            {/* Location Hotspots */}
                            <Card>
                                <CardHeader>
                                    <CardTitle>Location Hotspots</CardTitle>
                                    <CardDescription>Areas with highest feedback volume</CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    {analytics.insights.location_hotspots.map((location, index) => (
                                        <div key={index} className="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                            <div className="flex items-center gap-3">
                                                <MapPin className="h-4 w-4 text-[#2E79B5]" />
                                                <div>
                                                    <p className="font-medium">{location.location}</p>
                                                    <p className="text-sm text-gray-600 dark:text-gray-400">
                                                        Avg. Urgency: {location.avg_urgency.toFixed(1)}
                                                    </p>
                                                </div>
                                            </div>
                                            <Badge variant="outline">
                                                {location.count} reports
                                            </Badge>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>

                    {/* Recommendations Tab */}
                    <TabsContent value="recommendations" className="space-y-6">
                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {/* Immediate Actions */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <AlertTriangle className="h-5 w-5 text-red-500" />
                                        Immediate Actions
                                    </CardTitle>
                                    <CardDescription>
                                        Priority actions requiring urgent attention
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {analytics.ai_recommendations.immediate_actions.map((action, index) => (
                                        <div key={index} className="p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                                            <div className="flex items-start gap-3">
                                                <AlertTriangle className="h-4 w-4 text-red-500 mt-0.5" />
                                                <p className="text-sm text-red-900 dark:text-red-100">{action}</p>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>

                            {/* Long-term Strategies */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Target className="h-5 w-5 text-blue-500" />
                                        Long-term Strategies
                                    </CardTitle>
                                    <CardDescription>
                                        Strategic recommendations for sustained improvement
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {analytics.ai_recommendations.long_term_strategies.map((strategy, index) => (
                                        <div key={index} className="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                                            <div className="flex items-start gap-3">
                                                <Target className="h-4 w-4 text-blue-500 mt-0.5" />
                                                <p className="text-sm text-blue-900 dark:text-blue-100">{strategy}</p>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>

                            {/* Resource Allocation */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Users className="h-5 w-5 text-green-500" />
                                        Resource Allocation
                                    </CardTitle>
                                    <CardDescription>
                                        Recommended resource distribution
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {analytics.ai_recommendations.resource_allocation.map((allocation, index) => (
                                        <div key={index} className="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                                            <div className="flex items-start gap-3">
                                                <Users className="h-4 w-4 text-green-500 mt-0.5" />
                                                <p className="text-sm text-green-900 dark:text-green-100">{allocation}</p>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>

                            {/* Communication Strategies */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <MessageSquare className="h-5 w-5 text-purple-500" />
                                        Communication Strategies
                                    </CardTitle>
                                    <CardDescription>
                                        Recommended citizen engagement approaches
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    {analytics.ai_recommendations.communication_strategies.map((strategy, index) => (
                                        <div key={index} className="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                                            <div className="flex items-start gap-3">
                                                <MessageSquare className="h-4 w-4 text-purple-500 mt-0.5" />
                                                <p className="text-sm text-purple-900 dark:text-purple-100">{strategy}</p>
                                            </div>
                                        </div>
                                    ))}
                                </CardContent>
                            </Card>
                        </div>
                    </TabsContent>
                </Tabs>
            </div>
        </AppLayout>
    );
}
