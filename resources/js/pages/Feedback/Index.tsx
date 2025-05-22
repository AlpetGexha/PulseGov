import { useState, useEffect } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import { Label } from "@/components/ui/label"
import {
    ThumbsUp,
    ThumbsDown,
    MessageSquare,
    Filter,
    Plus,
    X,
    Tag,
    Calendar,
    ChevronUp,
    ChevronDown,
    AlertTriangle,
    Lightbulb,
    HelpCircle,
    Award
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
import { toast } from 'sonner';

export default function FeedbackForum({ feedbacks, auth, categories }) {
    // State for filter controls
    const [activeTab, setActiveTab] = useState('all');
    const [sort, setSort] = useState('latest');
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [draftFeedback, setDraftFeedback] = useState({
        title: '',
        body: '',
        service: '',
        feedback_type: 'suggestion',
    });

    // Form handling
    const { data, setData, post, processing, reset, errors } = useForm({
        title: '',
        body: '',
        service: '',
        feedback_type: 'suggestion',
    });

    // Load draft from localStorage on component mount
    useEffect(() => {
        const savedDraft = localStorage.getItem('feedbackDraft');
        if (savedDraft) {
            const parsedDraft = JSON.parse(savedDraft);
            setDraftFeedback(parsedDraft);
        }
    }, []);

    // Save draft to localStorage when modal is closed
    const handleCloseModal = () => {
        if (data.title || data.body) {
            localStorage.setItem('feedbackDraft', JSON.stringify(data));
            toast.info('Draft saved!');
        }
        setIsModalOpen(false);
    };

    // Load draft when opening modal
    const handleOpenModal = () => {
        if (draftFeedback.title || draftFeedback.body) {
            setData(draftFeedback);
        }
        setIsModalOpen(true);
    };

    // Submit form
    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('feedback.store'), {
            onSuccess: () => {
                setIsModalOpen(false);
                reset();
                localStorage.removeItem('feedbackDraft');
                toast.success('Feedback submitted successfully!');
            },
            onError: (errors) => {
                console.error(errors);
                const errorMessage = Object.values(errors).flat().join(', ');
                toast.error(`Failed to submit feedback: ${errorMessage}`);
            }
        });
    };

    // Handle voting
    const handleVote = (feedbackId, voteType) => {
        if (!auth.user) {
            toast.error('Please log in to vote');
            return;
        }

        post(route('feedback.vote', { id: feedbackId, type: voteType }), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Vote recorded!');
            },
            onError: () => {
                toast.error('Failed to record vote');
            }
        });
    };

    // Get feedback type icon
    const getFeedbackTypeIcon = (type) => {
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

    // Get sentiment color
    const getSentimentColor = (sentiment) => {
        switch (sentiment) {
            case 'positive':
                return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
            case 'negative':
                return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
            case 'neutral':
            default:
                return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
        }
    };

    // Calculate total votes
    const getTotalVotes = (feedback) => {
        const upvotes = feedback.votes?.filter(v => v.vote_type === 'upvote').length || 0;
        const downvotes = feedback.votes?.filter(v => v.vote_type === 'downvote').length || 0;
        return upvotes - downvotes;
    };

    // Filter feedbacks based on active tab
    const filteredFeedback = feedbacks?.data ? feedbacks.data.filter(feedback => {
        if (activeTab === 'all') return true;
        return feedback.feedback_type === activeTab;
    }) : [];

    // Sort feedbacks based on selected sort option
    const sortedFeedback = filteredFeedback.sort((a, b) => {
        switch (sort) {
            case 'votes':
                return getTotalVotes(b) - getTotalVotes(a);
            case 'comments':
                return (b.comments?.length || 0) - (a.comments?.length || 0);
            case 'oldest':
                return new Date(a.created_at) - new Date(b.created_at);
            case 'latest':
            default:
                return new Date(b.created_at) - new Date(a.created_at);
        }
    });

    return (
        <>
            <Head title="Community Feedback | PulseGov" />

            <div className="container max-w-6xl py-8">
                <div className="mb-8 flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Community Feedback</h1>
                        <p className="text-muted-foreground">
                            Share your ideas, suggestions, and experiences with public services
                        </p>
                    </div>

                    <Button
                        onClick={handleOpenModal}
                        className="gap-2 bg-[#2E79B5] hover:bg-[#2568A0]"
                    >
                        <Plus className="h-4 w-4" />
                        Share Feedback
                    </Button>
                </div>

                {/* Filters and Tabs */}
                <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <Tabs
                        defaultValue="all"
                        value={activeTab}
                        onValueChange={setActiveTab}
                        className="w-full sm:w-auto"
                    >
                        <TabsList className="w-full sm:w-auto">
                            <TabsTrigger value="all">All</TabsTrigger>
                            <TabsTrigger value="suggestion">Suggestions</TabsTrigger>
                            <TabsTrigger value="complaint">Issues</TabsTrigger>
                            <TabsTrigger value="question">Questions</TabsTrigger>
                            <TabsTrigger value="compliment">Compliments</TabsTrigger>
                        </TabsList>
                    </Tabs>

                    <div className="flex gap-2">
                        <Select value={sort} onValueChange={setSort}>
                            <SelectTrigger className="w-[160px]">
                                <SelectValue placeholder="Sort by" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="latest">Latest first</SelectItem>
                                <SelectItem value="oldest">Oldest first</SelectItem>
                                <SelectItem value="votes">Most votes</SelectItem>
                                <SelectItem value="comments">Most comments</SelectItem>
                            </SelectContent>
                        </Select>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline" size="icon">
                                    <Filter className="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem>
                                    Filter by department
                                </DropdownMenuItem>
                                <DropdownMenuItem>
                                    Filter by status
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </div>

                {/* Feedback List */}
                <div className="space-y-4">
                    {sortedFeedback.length === 0 ? (
                        <div className="rounded-lg border border-dashed p-8 text-center">
                            <p className="text-muted-foreground">No feedback found. Be the first to share!</p>
                        </div>
                    ) : (
                        sortedFeedback.map((feedback) => (
                            <Card key={feedback.id} className="overflow-hidden">
                                <div className="flex">
                                    {/* Voting Section */}
                                    <div className="flex w-16 flex-col items-center border-r bg-muted/20 py-4">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => handleVote(feedback.id, 'upvote')}
                                        >
                                            <ChevronUp className="h-5 w-5" />
                                        </Button>

                                        <div className="py-1 text-center font-medium">
                                            {getTotalVotes(feedback)}
                                        </div>

                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            onClick={() => handleVote(feedback.id, 'downvote')}
                                        >
                                            <ChevronDown className="h-5 w-5" />
                                        </Button>
                                    </div>

                                    {/* Content Section */}
                                    <div className="flex-1">
                                        <CardHeader className="pb-2">
                                            <div className="mb-2 flex flex-wrap gap-2">
                                                <Badge variant="outline" className="flex items-center gap-1">
                                                    {getFeedbackTypeIcon(feedback.feedback_type)}
                                                    <span className="capitalize">
                                                        {feedback.feedback_type}
                                                    </span>
                                                </Badge>

                                                {feedback.sentiment && (
                                                    <Badge className={`${getSentimentColor(feedback.sentiment)}`}>
                                                        {feedback.sentiment}
                                                    </Badge>
                                                )}

                                                {feedback.department_assigned && (
                                                    <Badge variant="secondary">
                                                        {feedback.department_assigned}
                                                    </Badge>
                                                )}
                                            </div>

                                            <Link
                                                href={route('feedback.show', feedback.id)}
                                                className="text-xl font-semibold hover:text-blue-600 hover:underline"
                                            >
                                                {feedback.title}
                                            </Link>

                                            <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                                                <span>Posted by {feedback.user?.name || 'Anonymous'}</span>
                                                <span>•</span>
                                                <span className="flex items-center gap-1">
                                                    <Calendar className="h-3 w-3" />
                                                    {new Date(feedback.created_at).toLocaleDateString()}
                                                </span>
                                                <span>•</span>
                                                <span className="flex items-center gap-1">
                                                    <Tag className="h-3 w-3" />
                                                    {feedback.service || 'General'}
                                                </span>
                                            </div>
                                        </CardHeader>

                                        <CardContent className="pb-2">
                                            <div className="line-clamp-3 text-sm">
                                                {feedback.body}
                                            </div>
                                        </CardContent>

                                        <CardFooter className="pt-0">
                                            <div className="flex items-center gap-4">
                                                <Link
                                                    href={route('feedback.show', feedback.id)}
                                                    className="flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                                                >
                                                    <MessageSquare className="h-4 w-4" />
                                                    {feedback.comments?.length || 0}
                                                    <span className="hidden sm:inline">
                                                        &nbsp;comments
                                                    </span>
                                                </Link>

                                                <div className="flex items-center gap-1 text-sm text-muted-foreground">
                                                    <span className="hidden sm:inline">Status:</span>
                                                    <Badge variant="outline" className="capitalize">
                                                        {feedback.status?.label || 'Under Review'}
                                                    </Badge>
                                                </div>
                                            </div>
                                        </CardFooter>
                                    </div>
                                </div>
                            </Card>
                        ))
                    )}
                </div>

                {/* Pagination */}
                {feedbacks?.links && feedbacks.links.length > 3 && (
                    <div className="mt-6 flex justify-center gap-2">
                        {feedbacks.links.map((link, i) => (
                            <Link
                                key={i}
                                href={link.url || '#'}
                                className={`px-4 py-2 text-sm ${link.active
                                        ? 'bg-blue-600 text-white'
                                        : link.url
                                            ? 'bg-white text-blue-600 hover:bg-blue-100'
                                            : 'cursor-default text-gray-400'
                                    } rounded border`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        ))}
                    </div>
                )}
            </div>

            {/* Create Feedback Modal */}
            <Dialog open={isModalOpen} onOpenChange={setIsModalOpen}>
                <DialogContent className="sm:max-w-[550px]">
                    <DialogHeader>
                        <DialogTitle>Share Your Feedback</DialogTitle>
                        <DialogDescription>
                            Help improve government services by sharing your experience, ideas, or concerns.
                        </DialogDescription>
                    </DialogHeader>

                    <form onSubmit={handleSubmit} className="space-y-4">
                        <div className="space-y-2">
                            <label htmlFor="feedback_type" className="text-sm font-medium">
                                Feedback Type
                            </label>
                            <Select
                                value={data.feedback_type}
                                onValueChange={(value) => setData('feedback_type', value)}
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="Select feedback type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="suggestion">
                                        <div className="flex items-center">
                                            <Lightbulb className="mr-2 h-4 w-4" />
                                            Suggestion
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="complaint">
                                        <div className="flex items-center">
                                            <AlertTriangle className="mr-2 h-4 w-4" />
                                            Issue/Complaint
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="question">
                                        <div className="flex items-center">
                                            <HelpCircle className="mr-2 h-4 w-4" />
                                            Question
                                        </div>
                                    </SelectItem>
                                    <SelectItem value="compliment">
                                        <div className="flex items-center">
                                            <Award className="mr-2 h-4 w-4" />
                                            Compliment
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            {errors.feedback_type && <p className="text-sm text-red-600">{errors.feedback_type}</p>}
                        </div>

                        <div className="space-y-2">
                            <label htmlFor="title" className="text-sm font-medium">
                                Title
                            </label>
                            <Input
                                id="title"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                placeholder="Summarize your feedback in a brief title"
                            />
                            {errors.title && <p className="text-sm text-red-600">{errors.title}</p>}
                        </div>

                        <div className="space-y-2">
                            <label htmlFor="body" className="text-sm font-medium">
                                Details
                            </label>
                            <Textarea
                                id="body"
                                value={data.body}
                                onChange={(e) => setData('body', e.target.value)}
                                placeholder="Provide more details about your feedback..."
                                rows={5}
                            />
                            {errors.body && <p className="text-sm text-red-600">{errors.body}</p>}
                        </div>

                        <div className="space-y-2">
                            <label htmlFor="service" className="text-sm font-medium">
                                Related Service (Optional)
                            </label>
                            <Input
                                id="service"
                                value={data.service || ''}
                                onChange={(e) => setData('service', e.target.value)}
                                placeholder="Which public service is this about?"
                            />
                        </div>

                        <div>
                            <div className="grid w-full max-w-sm items-center gap-1.5">
                                <Label htmlFor="picture">Picture</Label>
                                <Input id="picture" type="file" />
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
                                className="gap-2 bg-[#2E79B5] hover:bg-[#2568A0]"
                                disabled={processing}
                            >
                                Submit Feedback
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
