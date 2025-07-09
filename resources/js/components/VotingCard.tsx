import React, { useState } from 'react';
import { router } from '@inertiajs/react';
import { Button } from './ui/button';
import { Card, CardContent } from './ui/card';
import { ThumbsUp, ThumbsDown } from 'lucide-react';
import { cn } from '@/lib/utils';

interface VotingCardProps {
    feedback: {
        id: number;
        votes?: Array<{
            id: number;
            user_id: number;
            vote: 'upvote' | 'downvote';
        }>;
    };
    auth: {
        user: {
            id: number;
        } | null;
    };
    className?: string;
}

export default function VotingCard({ feedback, auth, className }: VotingCardProps) {
    // Calculate vote counts
    const upvotes = feedback.votes?.filter(vote => vote.vote === 'upvote').length || 0;
    const downvotes = feedback.votes?.filter(vote => vote.vote === 'downvote').length || 0;

    // Get user's current vote
    const getUserVote = () => {
        if (!auth.user || !feedback.votes) return null;
        const userVote = feedback.votes.find(vote => vote.user_id === auth.user!.id);
        return userVote ? (userVote.vote === 'upvote' ? 'up' : 'down') : null;
    };

    const [isVoting, setIsVoting] = useState(false);
    const [currentVote, setCurrentVote] = useState(getUserVote());
    const [voteCount, setVoteCount] = useState({ up: upvotes, down: downvotes });

    const handleVote = async (voteType: 'up' | 'down') => {
        if (isVoting || !auth.user) return;

        setIsVoting(true);

        try {
            // Optimistic update
            const newVote = currentVote === voteType ? null : voteType;
            const newVoteCount = { ...voteCount };

            // Remove previous vote
            if (currentVote === 'up') {
                newVoteCount.up -= 1;
            } else if (currentVote === 'down') {
                newVoteCount.down -= 1;
            }

            // Add new vote
            if (newVote === 'up') {
                newVoteCount.up += 1;
            } else if (newVote === 'down') {
                newVoteCount.down += 1;
            }

            setCurrentVote(newVote);
            setVoteCount(newVoteCount);

            // Send vote to server
            const voteTypeForServer = voteType === 'up' ? 'upvote' : 'downvote';

            await router.post('/feedback/vote', {
                feedback_id: feedback.id,
                vote_type: voteTypeForServer,
            }, {
                preserveScroll: true,
                onSuccess: (page) => {
                    // Update with server response if needed
                    const updatedFeedback = page.props.feedback as any;
                    if (updatedFeedback && updatedFeedback.votes) {
                        const newUpvotes = updatedFeedback.votes.filter((v: any) => v.vote === 'upvote').length;
                        const newDownvotes = updatedFeedback.votes.filter((v: any) => v.vote === 'downvote').length;
                        setVoteCount({ up: newUpvotes, down: newDownvotes });

                        // Update user's current vote
                        const userVote = updatedFeedback.votes.find((v: any) => v.user_id === auth.user?.id);
                        setCurrentVote(userVote ? (userVote.vote === 'upvote' ? 'up' : 'down') : null);
                    }
                },
                onError: (errors) => {
                    console.error('Vote failed:', errors);
                    // Revert optimistic update on error
                    setCurrentVote(getUserVote());
                    setVoteCount({ up: upvotes, down: downvotes });
                },
            });
        } catch (error) {
            console.error('Vote failed:', error);
            // Revert optimistic update on error
            setCurrentVote(getUserVote());
            setVoteCount({ up: upvotes, down: downvotes });
        } finally {
            setIsVoting(false);
        }
    };

    return (
        <Card className={cn("w-full", className)}>
            <CardContent className="p-6">
                <div className="flex items-center justify-center space-x-4">
                    <div className="flex items-center space-x-2">
                        <Button
                            variant={currentVote === 'up' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => handleVote('up')}
                            disabled={isVoting || !auth.user}
                            className={cn(
                                "flex items-center space-x-2 transition-all",
                                currentVote === 'up'
                                    ? "bg-green-500 hover:bg-green-600 text-white"
                                    : "hover:bg-green-50 hover:text-green-600 hover:border-green-300"
                            )}
                        >
                            <ThumbsUp className="h-4 w-4" />
                            <span>{voteCount.up}</span>
                        </Button>

                        <div className="text-sm text-gray-500 dark:text-gray-400">
                            Helpful
                        </div>
                    </div>

                    <div className="h-8 w-px bg-gray-300 dark:bg-gray-600" />

                    <div className="flex items-center space-x-2">
                        <div className="text-sm text-gray-500 dark:text-gray-400">
                            Not Helpful
                        </div>

                        <Button
                            variant={currentVote === 'down' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => handleVote('down')}
                            disabled={isVoting || !auth.user}
                            className={cn(
                                "flex items-center space-x-2 transition-all",
                                currentVote === 'down'
                                    ? "bg-red-500 hover:bg-red-600 text-white"
                                    : "hover:bg-red-50 hover:text-red-600 hover:border-red-300"
                            )}
                        >
                            <ThumbsDown className="h-4 w-4" />
                            <span>{voteCount.down}</span>
                        </Button>
                    </div>
                </div>

                <div className="mt-4 text-center">
                    <p className="text-xs text-gray-500 dark:text-gray-400">
                        {voteCount.up + voteCount.down} total votes
                    </p>
                    {!auth.user && (
                        <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Login to vote
                        </p>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
