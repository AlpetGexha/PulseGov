import { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import {
    ChevronLeft,
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
    Send,
    MapPin
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Textarea } from '@/components/ui/textarea';
import { toast } from 'sonner';
import AppLayout from '@/layouts/app-layout';
import VotingCard from '@/components/VotingCard';

interface FeedbackShowProps {
    feedback: any;
    auth: any;
}

export default function FeedbackShow({ feedback, auth }: FeedbackShowProps) {
    const [replyingTo, setReplyingTo] = useState<number | null>(null);

    // Form handling for comments
    const { data, setData, post, processing, reset, errors } = useForm({
        content: '',
        parent_id: null as number | null,
    });

    // Submit comment
    const handleSubmitComment = (e: React.FormEvent) => {
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

    // Get feedback type icon
    const getFeedbackTypeIcon = (type: string) => {
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
    const getSentimentColor = (sentiment: string) => {
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

    // Format date
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    // Start replying to a comment
    const startReply = (commentId: number) => {
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
    const renderAvatar = (user: any) => (
        <Avatar>
            <AvatarImage src={user?.profile_photo_url} alt={user?.name} />
            <AvatarFallback>{user?.name?.charAt(0) || 'U'}</AvatarFallback>
        </Avatar>
    );

    // Nested comments renderer
    const renderComments = (comments: any[], depth = 0) => {
        if (!comments || !comments.length) return null;

        return comments.map(comment => (
            <div
                key={comment.id}
                className={`${depth > 0 ? 'ml-8 border-l-2 border-gray-200 pl-4' : ''}`}
            >
                <div className="bg-white rounded-lg p-4 shadow-sm border border-gray-100 hover:shadow-md transition-shadow">
                    <div className="flex items-start gap-3">
                        <Avatar className="h-8 w-8 flex-shrink-0">
                            <AvatarImage src={comment.user?.profile_photo_url} alt={comment.user?.name} />
                            <AvatarFallback className="text-xs bg-gray-500 text-white">
                                {comment.user?.name?.charAt(0) || 'U'}
                            </AvatarFallback>
                        </Avatar>

                        <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 mb-2">
                                <span className="font-medium text-gray-900 dark:text-white">
                                    {comment.user?.name || 'Anonymous'}
                                </span>
                                <span className="text-xs text-gray-500">
                                    {formatDate(comment.created_at)}
                                </span>
                                {comment.is_pinned && (
                                    <Badge variant="secondary" className="text-xs bg-blue-100 text-blue-800">
                                        Pinned
                                    </Badge>
                                )}
                            </div>

                            <div className="text-gray-700 dark:text-gray-300 mb-3 leading-relaxed">
                                {comment.content.split('\n').map((line: string, index: number) => (
                                    <p key={index} className="mb-2 last:mb-0">
                                        {line}
                                    </p>
                                ))}
                            </div>

                            <div className="flex items-center gap-3">
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-7 px-3 text-xs text-gray-500 hover:text-[#2E79B5] hover:bg-blue-50"
                                    onClick={() => startReply(comment.id)}
                                >
                                    Reply
                                </Button>

                                {comment.replies && comment.replies.length > 0 && (
                                    <span className="text-xs text-gray-500">
                                        {comment.replies.length} {comment.replies.length === 1 ? 'reply' : 'replies'}
                                    </span>
                                )}
                            </div>

                            {/* Reply Form */}
                            {replyingTo === comment.id && (
                                <div className="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                    <form onSubmit={handleSubmitComment}>
                                        <div className="space-y-3">
                                            <Textarea
                                                value={data.content}
                                                onChange={(e) => setData('content', e.target.value)}
                                                placeholder="Write your reply..."
                                                className="min-h-[80px] resize-none border-gray-300 focus:border-[#2E79B5] focus:ring-[#2E79B5]"
                                                rows={3}
                                            />

                                            <div className="flex justify-end gap-2">
                                                <Button
                                                    type="button"
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={cancelReply}
                                                    className="text-gray-500 hover:text-gray-700"
                                                >
                                                    Cancel
                                                </Button>

                                                <Button
                                                    type="submit"
                                                    size="sm"
                                                    disabled={processing || !data.content.trim()}
                                                    className="bg-[#2E79B5] hover:bg-[#2568A0] text-white gap-2"
                                                >
                                                    <Send className="h-3 w-3" />
                                                    Reply
                                                </Button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Nested replies */}
                {comment.replies && comment.replies.length > 0 && (
                    <div className="mt-4 space-y-3">
                        {renderComments(comment.replies, depth + 1)}
                    </div>
                )}
            </div>
        ));
    };

    return (
        <AppLayout>
            <Head title={`${feedback.title} | PulseGov Feedback`} />            <div className="space-y-6 container mx-auto px-4 sm:px-6 lg:px-8 mt-8">
                {/* Header Section */}
                <div className="flex items-center justify-between">
                    <Link
                        href={route('feedback.index')}
                        className="inline-flex items-center text-sm font-medium text-gray-600 hover:text-[#2E79B5] transition-colors"
                    >
                        <ChevronLeft className="mr-2 h-4 w-4" />
                        Back to Feedback
                    </Link>

                    <div className="flex items-center space-x-3">
                        <Button
                            variant="outline"
                            size="sm"
                            className="hidden sm:flex items-center gap-2"
                            onClick={shareFeedback}
                        >
                            <Share2 className="h-4 w-4" />
                            Share
                        </Button>

                        <div className="text-sm text-gray-500">
                            ID: <span className="font-mono">{feedback.tracking_code}</span>
                        </div>
                    </div>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* Main Content */}
                        <div className="lg:col-span-2 space-y-6">
                            {/* Feedback Card */}
                            <Card className="shadow-sm border">
                                <CardHeader className="pb-4">
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-center space-x-3">
                                            <div className="p-2 rounded-full bg-[#2E79B5] text-white">
                                                {getFeedbackTypeIcon(feedback.feedback_type)}
                                            </div>
                                            <div>
                                                <Badge
                                                    variant="outline"
                                                    className="mb-2"
                                                >
                                                    {feedback.feedback_type.charAt(0).toUpperCase() + feedback.feedback_type.slice(1)}
                                                </Badge>
                                                <h1 className="text-2xl font-bold text-gray-900 dark:text-white leading-tight">
                                                    {feedback.title}
                                                </h1>
                                            </div>
                                        </div>

                                        <div className="flex items-center space-x-2">
                                            <Badge
                                                variant="secondary"
                                                className="bg-green-100 text-green-800"
                                            >
                                                {feedback.status?.label || 'Under Review'}
                                            </Badge>
                                        </div>
                                    </div>
                                </CardHeader>

                                <CardContent className="space-y-6">
                                    {/* Meta Information */}
                                    <div className="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div className="flex items-center gap-2">
                                            <Avatar className="h-7 w-7">
                                                <AvatarImage src={feedback.user?.profile_photo_url} alt={feedback.user?.name} />
                                                <AvatarFallback className="text-xs bg-[#2E79B5] text-white">
                                                    {feedback.user?.name?.charAt(0) || 'A'}
                                                </AvatarFallback>
                                            </Avatar>
                                            <span className="font-medium">{feedback.user?.name || 'Anonymous'}</span>
                                        </div>

                                        <div className="flex items-center gap-2">
                                            <Calendar className="h-4 w-4" />
                                            <span>{formatDate(feedback.created_at)}</span>
                                        </div>

                                        {feedback.location && (
                                            <div className="flex items-center gap-2">
                                                <MapPin className="h-4 w-4" />
                                                <span>{feedback.location}</span>
                                            </div>
                                        )}

                                        {feedback.service && (
                                            <div className="flex items-center gap-2">
                                                <Tag className="h-4 w-4" />
                                                <span>{feedback.service}</span>
                                            </div>
                                        )}
                                    </div>

                                    <Separator />

                                    {/* Feedback Content */}
                                    <div className="prose prose-lg max-w-none dark:prose-invert">
                                        <div className="text-gray-800 dark:text-gray-200 leading-relaxed">
                                            {feedback.body.split('\n').map((paragraph: string, index: number) => (
                                                <p key={index} className="mb-4 last:mb-0">
                                                    {paragraph}
                                                </p>
                                            ))}
                                        </div>
                                    </div>

                                    {/* AI Analysis */}
                                    {feedback.ai_analysis_details?.summary && (
                                        <div className="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                                            <div className="flex items-start gap-3">
                                                <div className="p-2 bg-blue-500 rounded-lg">
                                                    <Lightbulb className="h-4 w-4 text-white" />
                                                </div>
                                                <div className="flex-1">
                                                    <h3 className="font-semibold text-blue-900 dark:text-blue-200 mb-2">AI Summary</h3>
                                                    <p className="text-blue-800 dark:text-blue-300 text-sm">
                                                        {feedback.ai_analysis_details.summary}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    )}

                                    {/* Tags */}
                                    {feedback.ai_analysis_details?.suggested_tags && (
                                        <div className="flex flex-wrap gap-2">
                                            {feedback.ai_analysis_details.suggested_tags.map((tag, index) => (
                                                <Badge key={index} variant="secondary" className="bg-gray-100 hover:bg-gray-200">
                                                    {tag}
                                                </Badge>
                                            ))}
                                        </div>
                                    )}
                                </CardContent>
                            </Card>

                            {/* Comments Section */}
                            <Card className="shadow-sm border">
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <h2 className="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                            <MessageSquare className="h-5 w-5" />
                                            Comments ({feedback.comments?.length || 0})
                                        </h2>
                                    </div>
                                </CardHeader>

                                <CardContent className="space-y-6">
                                    {/* Comment Form */}
                                    {auth.user ? (
                                        <div className="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                                            <div className="flex items-start gap-3">
                                                <Avatar className="h-8 w-8">
                                                    <AvatarImage src={auth.user?.profile_photo_url} alt={auth.user?.name} />
                                                    <AvatarFallback className="text-xs bg-[#2E79B5] text-white">
                                                        {auth.user?.name?.charAt(0) || 'U'}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <div className="flex-1">
                                                    <form onSubmit={handleSubmitComment} className="space-y-3">
                                                        <Textarea
                                                            value={data.content}
                                                            onChange={(e) => setData('content', e.target.value)}
                                                            placeholder="Share your thoughts on this feedback..."
                                                            className="min-h-[100px] resize-none"
                                                            rows={3}
                                                        />
                                                        {errors.content && (
                                                            <p className="text-sm text-red-600">{errors.content}</p>
                                                        )}
                                                        <div className="flex justify-end">
                                                            <Button
                                                                type="submit"
                                                                disabled={processing || !data.content.trim()}
                                                                className="bg-[#2E79B5] hover:bg-[#2568A0] text-white gap-2"
                                                            >
                                                                <Send className="h-4 w-4" />
                                                                Post Comment
                                                            </Button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    ) : (
                                        <div className="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg">
                                            <MessageSquare className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                            <p className="text-gray-600 dark:text-gray-400 mb-4">
                                                Join the conversation by logging in
                                            </p>
                                            <div className="space-x-4">
                                                <Button asChild variant="outline">
                                                    <Link href={route('login')}>Login</Link>
                                                </Button>
                                                <Button asChild className="bg-[#2E79B5] hover:bg-[#2568A0]">
                                                    <Link href={route('register')}>Register</Link>
                                                </Button>
                                            </div>
                                        </div>
                                    )}

                                    <Separator />

                                    {/* Comments List */}
                                    <div className="space-y-4">
                                        {feedback.comments && feedback.comments.length > 0 ? (
                                            renderComments(feedback.comments.filter(c => !c.parent_id))
                                        ) : (
                                            <div className="text-center py-8">
                                                <MessageSquare className="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                                <p className="text-gray-600 dark:text-gray-400">
                                                    No comments yet. Be the first to share your thoughts!
                                                </p>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Voting Card */}
                            <VotingCard feedback={feedback} auth={auth} />

                            {/* Feedback Info */}
                            <Card className="shadow-sm border">
                                <CardHeader>
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                        Feedback Details
                                    </h3>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-gray-600">Type</span>
                                        <Badge variant="outline" className="border-[#2E79B5] text-[#2E79B5]">
                                            {feedback.feedback_type.charAt(0).toUpperCase() + feedback.feedback_type.slice(1)}
                                        </Badge>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-gray-600">Status</span>
                                        <Badge variant="secondary" className="bg-green-100 text-green-800">
                                            {feedback.status?.label || 'Under Review'}
                                        </Badge>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-gray-600">Visibility</span>
                                        <Badge variant={feedback.is_public ? "default" : "secondary"}>
                                            {feedback.is_public ? 'Public' : 'Private'}
                                        </Badge>
                                    </div>

                                    <div className="flex items-center justify-between">
                                        <span className="text-sm text-gray-600">Tracking Code</span>
                                        <span className="text-sm font-mono bg-gray-100 px-2 py-1 rounded">
                                            {feedback.tracking_code}
                                        </span>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Quick Actions */}
                            <Card className="shadow-sm border">
                                <CardHeader>
                                    <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                                        Actions
                                    </h3>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <Button
                                        variant="outline"
                                        className="w-full justify-start"
                                        onClick={shareFeedback}
                                    >
                                        <Share2 className="mr-2 h-4 w-4" />
                                        Share Feedback
                                    </Button>

                                    {auth.user && (
                                        <Button
                                            variant="outline"
                                            className="w-full justify-start text-amber-600 hover:text-amber-700"
                                        >
                                            <Flag className="mr-2 h-4 w-4" />
                                            Report Issue
                                        </Button>
                                    )}

                                    {auth.user && auth.user.id === feedback.user_id && (
                                        <Button
                                            variant="outline"
                                            className="w-full justify-start text-[#2E79B5] hover:text-[#2568A0]"
                                        >
                                            <User className="mr-2 h-4 w-4" />
                                            Edit Feedback
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
        </AppLayout>
    );
}
