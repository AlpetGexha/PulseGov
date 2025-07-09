import { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    MessageSquare,
    Filter,
    Plus,
    Tag,
    Calendar,
    AlertTriangle,
    Lightbulb,
    HelpCircle,
    Award,
    Eye,
    EyeOff,
    ChevronLeft,
    ChevronRight
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogFooter,
    DialogClose,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { toast } from 'sonner';
import AppLayout from '@/layouts/app-layout';

export default function FeedbackIndex({ feedbacks, auth }) {
    // State for filter controls
    const [activeTab, setActiveTab] = useState('all');
    const [sort, setSort] = useState('latest');
    const [isModalOpen, setIsModalOpen] = useState(false);

    // Form handling
    const { data, setData, post, processing, reset, errors } = useForm({
        title: '',
        body: '',
        service: '',
        feedback_type: 'suggestion',
        is_public: false, // Default to private
    });

    // Submit feedback
    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('feedback.store'), {
            onSuccess: () => {
                reset();
                setIsModalOpen(false);
                toast.success('Feedback submitted successfully!');
            },
            onError: (errors) => {
                toast.error('Please check your input and try again.');
            }
        });
    };

    // Reset form when modal closes
    const handleCloseModal = () => {
        reset();
        setIsModalOpen(false);
    };

    // Helper functions
    const getFeedbackTypeIcon = (type) => {
        switch (type) {
            case 'suggestion':
                return <Lightbulb className="h-4 w-4" />;
            case 'complaint':
                return <AlertTriangle className="h-4 w-4" />;
            case 'question':
                return <HelpCircle className="h-4 w-4" />;
            case 'compliment':
                return <Award className="h-4 w-4" />;
            default:
                return <MessageSquare className="h-4 w-4" />;
        }
    };

    const getStatusColor = (status) => {
        switch (status) {
            case 'resolved':
                return 'bg-green-100 text-green-800';
            case 'in_progress':
                return 'bg-blue-100 text-blue-800';
            case 'under_review':
                return 'bg-yellow-100 text-yellow-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    };

    const formatDate = (date) => {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    };

    // Filter feedbacks based on active tab
    const filteredFeedbacks = feedbacks.data.filter(feedback => {
        if (activeTab === 'all') return true;
        return feedback.feedback_type === activeTab;
    });

    return (
        <AppLayout>
            <Head title="Community Feedback" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Community Feedback</h1>
                        <p className="text-muted-foreground mt-1">
                            Share your ideas, suggestions, and experiences with public services
                        </p>
                    </div>

                    <Button
                        onClick={() => setIsModalOpen(true)}
                        className="mt-4 sm:mt-0 gap-2 bg-[#2E79B5] hover:bg-[#2568A0]"
                    >
                        <Plus className="h-4 w-4" />
                        Share Feedback
                    </Button>
                </div>

                {/* Filters and Tabs */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <Tabs
                        defaultValue="all"
                        value={activeTab}
                        onValueChange={setActiveTab}
                        className="w-full sm:w-auto"
                    >
                        <TabsList className="grid w-full grid-cols-5 sm:w-auto">
                            <TabsTrigger value="all">All</TabsTrigger>
                            <TabsTrigger value="suggestion">Ideas</TabsTrigger>
                            <TabsTrigger value="complaint">Issues</TabsTrigger>
                            <TabsTrigger value="question">Questions</TabsTrigger>
                            <TabsTrigger value="compliment">Praise</TabsTrigger>
                        </TabsList>
                    </Tabs>

                    <div className="flex gap-2">
                        <Select value={sort} onValueChange={setSort}>
                            <SelectTrigger className="w-[140px]">
                                <SelectValue placeholder="Sort by" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="latest">Latest</SelectItem>
                                <SelectItem value="oldest">Oldest</SelectItem>
                                <SelectItem value="comments">Most discussed</SelectItem>
                            </SelectContent>
                        </Select>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" size="icon">
                                    <Filter className="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuItem>Status: All</DropdownMenuItem>
                                <DropdownMenuItem>Status: Under Review</DropdownMenuItem>
                                <DropdownMenuItem>Status: In Progress</DropdownMenuItem>
                                <DropdownMenuItem>Status: Resolved</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                {/* Feedback Cards */}
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {filteredFeedbacks.map((feedback) => (
                        <Card key={feedback.id} className="group hover:shadow-lg transition-shadow duration-200">
                            <CardHeader className="pb-3">
                                <div className="flex flex-wrap items-center gap-2 mb-3">
                                    <Badge variant="outline" className="flex items-center gap-1">
                                        {getFeedbackTypeIcon(feedback.feedback_type)}
                                        <span className="capitalize">{feedback.feedback_type}</span>
                                    </Badge>

                                    <Badge className={getStatusColor(feedback.status)}>
                                        {feedback.status?.replace('_', ' ').toUpperCase() || 'UNDER REVIEW'}
                                    </Badge>

                                    {!feedback.is_public && (
                                        <Badge variant="secondary" className="flex items-center gap-1">
                                            <EyeOff className="h-3 w-3" />
                                            Private
                                        </Badge>
                                    )}
                                </div>

                                <Link
                                    href={route('feedback.show', feedback.id)}
                                    className="text-lg font-semibold hover:text-blue-600 hover:underline line-clamp-2"
                                >
                                    {feedback.title}
                                </Link>

                                <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                                    <span>By {feedback.user?.name || 'Anonymous'}</span>
                                    <span>•</span>
                                    <span className="flex items-center gap-1">
                                        <Calendar className="h-3 w-3" />
                                        {formatDate(feedback.created_at)}
                                    </span>
                                    {feedback.service && (
                                        <>
                                            <span>•</span>
                                            <span className="flex items-center gap-1">
                                                <Tag className="h-3 w-3" />
                                                {feedback.service}
                                            </span>
                                        </>
                                    )}
                                </div>
                            </CardHeader>

                            <CardContent className="pb-3">
                                <p className="text-sm text-muted-foreground line-clamp-3">
                                    {feedback.body}
                                </p>
                            </CardContent>

                            <CardFooter className="pt-0">
                                <div className="flex items-center justify-between w-full">
                                    <Link
                                        href={route('feedback.show', feedback.id)}
                                        className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                                    >
                                        <MessageSquare className="h-4 w-4" />
                                        {feedback.comments?.length || 0}
                                        <span className="hidden sm:inline">comments</span>
                                    </Link>

                                    <Badge variant="outline" className="text-xs">
                                        #{feedback.tracking_code}
                                    </Badge>
                                </div>
                            </CardFooter>
                        </Card>
                    ))}
                </div>

                {/* Pagination */}
                {feedbacks.links && feedbacks.links.length > 3 && (
                    <div className="flex justify-center items-center gap-2 mt-8">
                        {feedbacks.links.map((link, index) => {
                            if (link.url === null) {
                                return (
                                    <Button
                                        key={index}
                                        variant="outline"
                                        size="sm"
                                        disabled
                                        className="px-3 py-1"
                                    >
                                        {link.label === '&laquo; Previous' ? (
                                            <ChevronLeft className="h-4 w-4" />
                                        ) : link.label === 'Next &raquo;' ? (
                                            <ChevronRight className="h-4 w-4" />
                                        ) : (
                                            <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                        )}
                                    </Button>
                                );
                            }

                            return (
                                <Link key={index} href={link.url}>
                                    <Button
                                        variant={link.active ? "default" : "outline"}
                                        size="sm"
                                        className="px-3 py-1"
                                    >
                                        {link.label === '&laquo; Previous' ? (
                                            <ChevronLeft className="h-4 w-4" />
                                        ) : link.label === 'Next &raquo;' ? (
                                            <ChevronRight className="h-4 w-4" />
                                        ) : (
                                            <span dangerouslySetInnerHTML={{ __html: link.label }} />
                                        )}
                                    </Button>
                                </Link>
                            );
                        })}
                    </div>
                )}

                {/* Empty state */}
                {filteredFeedbacks.length === 0 && (
                    <div className="text-center py-12">
                        <MessageSquare className="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                        <h3 className="text-lg font-semibold mb-2">No feedback found</h3>
                        <p className="text-muted-foreground mb-4">
                            {activeTab === 'all'
                                ? "Be the first to share your feedback with the community!"
                                : `No ${activeTab} feedback yet. Be the first to contribute!`
                            }
                        </p>
                        <Button onClick={() => setIsModalOpen(true)} className="bg-[#2E79B5] hover:bg-[#2568A0]">
                            <Plus className="h-4 w-4 mr-2" />
                            Share Feedback
                        </Button>
                    </div>
                )}
            </div>

            {/* Create Feedback Modal */}
            <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
                <DialogContent className="sm:max-w-[600px]">
                    <DialogHeader>
                        <DialogTitle>Share Your Feedback</DialogTitle>
                        <DialogDescription>
                            Help improve public services by sharing your experience, suggestions, or concerns.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        {/* Feedback Type */}
                        <div className="space-y-2">
                            <Label htmlFor="feedback_type">What type of feedback is this?</Label>
                            <Select value={data.feedback_type} onValueChange={(value) => setData('feedback_type', value)}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select feedback type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="suggestion">
                                        <div className="flex items-center gap-2">
                                            <Lightbulb className="h-4 w-4" />
                                            Suggestion/Idea
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="complaint">
                                        <div className="flex items-center gap-2">
                                            <AlertTriangle className="h-4 w-4" />
                                            Issue/Complaint
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="question">
                                        <div className="flex items-center gap-2">
                                            <HelpCircle className="h-4 w-4" />
                                            Question
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="compliment">
                                        <div className="flex items-center gap-2">
                                            <Award className="h-4 w-4" />
                                            Praise/Compliment
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.feedback_type && <p className="text-sm text-red-600">{errors.feedback_type}</p>}
                        </div>

                        {/* Title */}
                        <div className="space-y-2">
                            <Label htmlFor="title">Title</Label>
                            <Input
                                id="title"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                placeholder="Summarize your feedback in a brief title"
                                className="w-full"
                            />
                            {errors.title && <p className="text-sm text-red-600">{errors.title}</p>}
                        </div>

                        {/* Body */}
                        <div className="space-y-2">
                            <Label htmlFor="body">Details</Label>
                            <Textarea
                                id="body"
                                value={data.body}
                                onChange={(e) => setData('body', e.target.value)}
                                placeholder="Provide more details about your feedback..."
                                rows={4}
                                className="w-full"
                            />
                            {errors.body && <p className="text-sm text-red-600">{errors.body}</p>}
                        </div>

                        {/* Service */}
                        <div className="space-y-2">
                            <Label htmlFor="service">Related Service (Optional)</Label>
                            <Input
                                id="service"
                                value={data.service || ''}
                                onChange={(e) => setData('service', e.target.value)}
                                placeholder="Which public service is this about?"
                            />
                        </div>

                        {/* Privacy Setting */}
                        <div className="flex items-center space-x-3 p-3 rounded-md border">
                            <Checkbox
                                id="is_public"
                                checked={data.is_public}
                                onCheckedChange={(checked) => setData('is_public', checked)}
                            />
                            <div className="flex-1">
                                <Label htmlFor="is_public" className="text-sm font-medium cursor-pointer">
                                    Make this feedback public
                                </Label>
                                <p className="text-xs text-muted-foreground mt-1">
                                    {data.is_public ? (
                                        <>
                                            <Eye className="h-3 w-3 inline mr-1" />
                                            Your feedback will be visible to other community members
                                        </>
                                    ) : (
                                        <>
                                            <EyeOff className="h-3 w-3 inline mr-1" />
                                            Your feedback will be private and only visible to administrators
                                        </>
                                    )}
                                </p>
                            </div>
                        </div>

                        <DialogFooter className="gap-2 sm:gap-0">
                            <DialogClose asChild>
                                <Button
                                    type="button"
                                    variant="outline"
                                    onClick={handleCloseModal}
                                >
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button
                                type="submit"
                                className="bg-[#2E79B5] hover:bg-[#2568A0]"
                                disabled={processing}
                            >
                                {processing ? 'Submitting...' : 'Submit Feedback'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
