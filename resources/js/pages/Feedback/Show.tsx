import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    ChevronLeft,
    ChevronUp,
    ChevronDown,
    MessageSquare,
    AlertTriangle,
    Lightbulb,
    HelpCircle,
    Award,
    Calendar,
    Tag,
    User,
    Flag,
    Share2,
    Send
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardFooter, CardHeader } from '@/components/ui/card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'sonner';

export default function FeedbackShow({ feedback, auth }) {
    const [replyingTo, setReplyingTo] = useState(null);

    // Form handling for comments
    const { data, setData, post, processing, reset, errors } = useForm({
        content: '',
        parent_id: null,
    });

    // Form handling for votes
    const voteForm = useForm({
        feedback_id: feedback.id,
        vote: '',
    });

    // Submit comment
    const handleSubmitComment = (e) => {
        e.preventDefault();
        post(route('feedback.comments.store', feedback.id), {
            onSuccess: () => {
                reset();
                setReplyingTo(null);
                toast.success('Comment posted successfully!');
            },
            onError: (error) => {
                toast.error('Failed to post comment.' + error.message);
            }
        });
    };

    // Handle voting
    const handleVote = (voteType) => {
        if (!auth.user) {
            toast.error('Please log in to vote');
            return;
        }

        voteForm.setData({
            feedback_id: feedback.id,
            vote: voteType
        });

        voteForm.post(route('feedback.vote'), {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Vote recorded successfully');
                // Refresh the page to update vote counts
                window.location.reload();
            },
            onError: (errors) => {
                console.error(errors);
                toast.error('Failed to record vote');
            }
        });
    };

    // Get feedback type icon
    const getFeedbackTypeIcon = (type) => {
        switch (type) {
            case 'complaint':
                return <AlertTriangle className="h-5 w-5" />;
            case 'suggestion':
                return <Lightbulb className="h-5 w-5" />;
            case 'question':
                return <HelpCircle className="h-5 w-5" />;
            case 'compliment':
                return <Award className="h-5 w-5" />;
            default:
                return <MessageSquare className="h-5 w-5" />;
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
    const getTotalVotes = () => {
        const upvotes = feedback.votes?.filter(v => v.vote === 'upvote').length || 0;
        const downvotes = feedback.votes?.filter(v => v.vote === 'downvote').length || 0;
        return upvotes - downvotes;
    };

    // Format date
    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    // Start replying to a comment
    const startReply = (commentId) => {
        setReplyingTo(commentId);
        setData('parent_id', commentId);
    };

    // Cancel reply
    const cancelReply = () => {
        setReplyingTo(null);
        setData('parent_id', null);
    };

    // Share feedback
    const shareFeedback = () => {
        navigator.clipboard.writeText(window.location.href);
        toast.success('Link copied to clipboard!');
    };

    // Render user avatar
    const renderAvatar = (user) => (
        <Avatar>
            <AvatarImage src={user?.profile_photo_url} alt={user?.name} />
            <AvatarFallback>{user?.name?.charAt(0) || 'U'}</AvatarFallback>
        </Avatar>
    );

    // Nested comments renderer
    const renderComments = (comments, depth = 0) => {
        if (!comments || !comments.length) return null;

        return comments.map(comment => (
            <div
                key={comment.id}
                className={`mb-4 ${depth > 0 ? 'ml-12' : ''}`}
            >
                <div className="flex gap-3">
                    {renderAvatar(comment.user)}

                    <div className="flex-1 space-y-1">
                        <div className="flex items-center gap-2">
                            <span className="font-medium">{comment.user?.name || 'Anonymous'}</span>
                            <span className="text-xs text-muted-foreground">
                                {formatDate(comment.created_at)}
                            </span>
                        </div>

                        <div className="rounded-md bg-muted/50 p-3">
                            <p>{comment.content}</p>
                        </div>

                        <div className="flex items-center gap-2">
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-7 px-2 text-xs"
                                onClick={() => startReply(comment.id)}
                            >
                                Reply
                            </Button>
                        </div>

                        {/* Reply Form */}
                        {replyingTo === comment.id && (
                            <div className="mt-2">
                                <form onSubmit={handleSubmitComment}>
                                    <div className="space-y-2">
                                        <Textarea
                                            id="content"
                                            value={data.content}
                                            onChange={(e) => setData('content', e.target.value)}
                                            placeholder="Write your reply..."
                                            rows={3}
                                        />

                                        <div className="flex justify-end gap-2">
                                            <Button
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                onClick={cancelReply}
                                            >
                                                Cancel
                                            </Button>

                                            <Button
                                                type="submit"
                                                size="sm"
                                                disabled={processing}
                                                className="bg-[#2E79B5] hover:bg-[#2568A0]"
                                            >
                                                <Send className="mr-1 h-3 w-3" />
                                                Reply
                                            </Button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        )}

                        {/* Nested replies */}
                        {comment.replies && renderComments(comment.replies, depth + 1)}
                    </div>
                </div>
            </div>
        ));
    };

    return (
        <>
            <Head title={`${feedback.title} | PulseGov Feedback`} />

            <div className="container max-w-6xl py-8">
                <div className="mb-6">
                    <Link
                        href={route('feedback.index')}
                        className="mb-4 inline-flex items-center text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ChevronLeft className="mr-1 h-4 w-4" />
                        Back to all feedback
                    </Link>

                    {/* Main Feedback Content */}
                    <Card>
                        <div className="flex">
                            {/* Voting Section */}
                            <div className="flex w-16 flex-col items-center border-r bg-muted/20 py-4">
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    onClick={() => handleVote('upvote')}
                                >
                                    <ChevronUp className="h-5 w-5" />
                                </Button>

                                <div className="py-1 text-center font-medium">
                                    {getTotalVotes()}
                                </div>

                                <Button
                                    variant="ghost"
                                    size="icon"
                                    onClick={() => handleVote('downvote')}
                                >
                                    <ChevronDown className="h-5 w-5" />
                                </Button>
                            </div>

                            {/* Content Section */}
                            <div className="flex-1">
                                <CardHeader>
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

                                        <Badge
                                            className="ml-auto"
                                            variant="outline"
                                        >
                                            Status: <span className="ml-1 capitalize">{feedback.status?.label || 'Under Review'}</span>
                                        </Badge>
                                    </div>

                                    <h1 className="text-2xl font-bold">{feedback.title}</h1>

                                    <div className="flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                                        <div className="flex items-center gap-2">
                                            {renderAvatar(feedback.user)}
                                            <span>{feedback.user?.name || 'Anonymous'}</span>
                                        </div>
                                        <span>•</span>
                                        <span className="flex items-center gap-1">
                                            <Calendar className="h-3 w-3" />
                                            {formatDate(feedback.created_at)}
                                        </span>
                                        <span>•</span>
                                        <span className="flex items-center gap-1">
                                            <Tag className="h-3 w-3" />
                                            {feedback.service || 'General'}
                                        </span>
                                        <span>•</span>
                                        <span className="flex items-center gap-1">
                                            <MessageSquare className="h-3 w-3" />
                                            {feedback.comments?.length || 0} comments
                                        </span>
                                    </div>
                                </CardHeader>

                                <CardContent>
                                    <div className="prose prose-sm max-w-none dark:prose-invert">
                                        <p>{feedback.body}</p>
                                    </div>

                                    {/* Tags if available */}
                                    {feedback.ai_analysis_details?.suggested_tags && (
                                        <div className="mt-4 flex flex-wrap gap-2">
                                            {feedback.ai_analysis_details.suggested_tags.map((tag, index) => (
                                                <Badge key={index} variant="secondary">
                                                    {tag}
                                                </Badge>
                                            ))}
                                        </div>
                                    )}

                                    {/* AI summary if available */}
                                    {feedback.ai_analysis_details?.summary && (
                                        <div className="mt-4 rounded-md bg-blue-50 p-3 dark:bg-blue-900/20">
                                            <p className="text-sm font-medium">AI Summary:</p>
                                            <p className="text-sm text-muted-foreground">
                                                {feedback.ai_analysis_details.summary}
                                            </p>
                                        </div>
                                    )}
                                </CardContent>

                                <CardFooter>
                                    <div className="flex w-full items-center justify-between">
                                        <div className="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="flex items-center gap-1"
                                                onClick={shareFeedback}
                                            >
                                                <Share2 className="h-4 w-4" />
                                                Share
                                            </Button>

                                            {auth.user && (
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    className="flex items-center gap-1 text-yellow-600 hover:text-yellow-700 dark:text-yellow-500"
                                                >
                                                    <Flag className="h-4 w-4" />
                                                    Report
                                                </Button>
                                            )}
                                        </div>

                                        {auth.user && auth.user.id === feedback.user_id && (
                                            <Button
                                                variant="outline"
                                                size="sm"
                                            >
                                                Edit
                                            </Button>
                                        )}
                                    </div>
                                </CardFooter>
                            </div>
                        </div>
                    </Card>
                </div>

                {/* Comments Section */}
                <div className="mt-8 space-y-6">
                    <h2 className="text-xl font-bold">Comments ({feedback.comments?.length || 0})</h2>

                    {/* Comment Form */}
                    {auth.user ? (
                        <div className="mb-6">
                            <form onSubmit={handleSubmitComment}>
                                <div className="space-y-2">
                                    <Textarea
                                        id="content"
                                        value={data.content}
                                        onChange={(e) => setData('content', e.target.value)}
                                        placeholder="Share your thoughts..."
                                        rows={3}
                                    />
                                    {errors.content && (
                                        <p className="text-sm text-red-600">{errors.content}</p>
                                    )}

                                    <div className="flex justify-end">
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                            className="gap-2 bg-[#2E79B5] hover:bg-[#2568A0]"
                                        >
                                            <MessageSquare className="h-4 w-4" />
                                            Post Comment
                                        </Button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    ) : (
                        <div className="mb-6 rounded-md bg-muted/50 p-4 text-center">
                            <p className="text-muted-foreground">
                                <Link href={route('login')} className="text-blue-600 underline">
                                    Login
                                </Link>
                                {' '}or{' '}
                                <Link href={route('register')} className="text-blue-600 underline">
                                    register
                                </Link>
                                {' '}to join the conversation
                            </p>
                        </div>
                    )}

                    <Separator />

                    {/* Comments List */}
                    <div className="space-y-6">
                        {feedback.comments && feedback.comments.length > 0 ? (
                            renderComments(feedback.comments.filter(c => !c.parent_id))
                        ) : (
                            <div className="py-4 text-center">
                                <p className="text-muted-foreground">No comments yet. Be the first to comment!</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
