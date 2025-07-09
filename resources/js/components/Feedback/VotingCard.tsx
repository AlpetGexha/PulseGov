import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import { ChevronUp, ChevronDown } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { toast } from 'sonner';

interface Vote {
    id: number;
    user_id: number;
    feedback_id: number;
    vote: 'upvote' | 'downvote';
}

interface VotingCardProps {
    feedback: {
        id: number;
        votes?: Vote[];
    };
    auth: {
        user?: {
            id: number;
            name: string;
        };
    };
}

export default function VotingCard({ feedback, auth }: VotingCardProps) {
    const [votes, setVotes] = useState<Vote[]>(feedback.votes || []);
    
    // Form handling for votes
    const voteForm = useForm({
        feedback_id: feedback.id,
        vote: '',
    });

    // Get user's current vote
    const getUserVote = (): string | null => {
        if (!auth.user || !votes.length) return null;
        const userVote = votes.find((vote: Vote) => vote.user_id === auth.user!.id);
        return userVote?.vote || null;
    };

    // Calculate total votes
    const getTotalVotes = (): number => {
        const upvotes = votes.filter(v => v.vote === 'upvote').length || 0;
        const downvotes = votes.filter(v => v.vote === 'downvote').length || 0;
        return upvotes - downvotes;
    };

    // Handle voting
    const handleVote = (voteType: 'upvote' | 'downvote') => {
        if (!auth.user) {
            toast.error('Please log in to vote');
            return;
        }

        const currentVote = getUserVote();
        
        // Optimistically update the UI
        setVotes(prevVotes => {
            const filteredVotes = prevVotes.filter(v => v.user_id !== auth.user!.id);
            
            // If clicking the same vote type, remove it (toggle off)
            if (currentVote === voteType) {
                return filteredVotes;
            }
            
            // Add new vote
            return [
                ...filteredVotes,
                {
                    id: Date.now(), // Temporary ID
                    user_id: auth.user!.id,
                    feedback_id: feedback.id,
                    vote: voteType
                }
            ];
        });

        // Set the vote data and submit
        voteForm.setData({
            feedback_id: feedback.id,
            vote: voteType
        });

        voteForm.post(route('feedback.vote'), {
            preserveScroll: true,
            onSuccess: (page: any) => {
                const message = page.props.flash?.success || 'Vote recorded successfully';
                toast.success(message);
                
                // Update votes with actual data from server if available
                if (page.props.feedback?.votes) {
                    setVotes(page.props.feedback.votes);
                }
            },
            onError: (errors: any) => {
                // Revert optimistic update on error
                setVotes(feedback.votes || []);
                
                console.error(errors);
                const message = errors.message || 'Failed to record vote';
                toast.error(message);
            }
        });
    };

    return (
        <Card className="shadow-sm border">
            <CardHeader>
                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">
                    Community Support
                </h3>
            </CardHeader>
            <CardContent>
                <div className="text-center space-y-4">
                    <div className="flex items-center justify-center space-x-4">
                        <Button
                            variant={getUserVote() === 'upvote' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => handleVote('upvote')}
                            className={`flex items-center gap-2 transition-all ${
                                getUserVote() === 'upvote' 
                                    ? 'bg-green-500 hover:bg-green-600 text-white border-green-500' 
                                    : 'hover:bg-green-50 hover:text-green-600 hover:border-green-300'
                            }`}
                            disabled={!auth.user || voteForm.processing}
                        >
                            <ChevronUp className="h-4 w-4" />
                            {getUserVote() === 'upvote' ? 'Supported' : 'Support'}
                        </Button>
                        
                        <div className="text-center">
                            <div className="text-2xl font-bold text-[#2E79B5]">
                                {getTotalVotes()}
                            </div>
                            <div className="text-xs text-gray-500">votes</div>
                        </div>
                        
                        <Button
                            variant={getUserVote() === 'downvote' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => handleVote('downvote')}
                            className={`flex items-center gap-2 transition-all ${
                                getUserVote() === 'downvote' 
                                    ? 'bg-red-500 hover:bg-red-600 text-white border-red-500' 
                                    : 'hover:bg-red-50 hover:text-red-600 hover:border-red-300'
                            }`}
                            disabled={!auth.user || voteForm.processing}
                        >
                            <ChevronDown className="h-4 w-4" />
                            {getUserVote() === 'downvote' ? 'Disagreed' : 'Disagree'}
                        </Button>
                    </div>
                    
                    {!auth.user ? (
                        <p className="text-xs text-gray-500">
                            Login to vote and show your support
                        </p>
                    ) : (
                        <p className="text-xs text-gray-500">
                            {getUserVote() === 'upvote' && 'You supported this feedback. Click Support again to remove your vote.'}
                            {getUserVote() === 'downvote' && 'You disagreed with this feedback. Click Disagree again to remove your vote.'}
                            {!getUserVote() && 'Click to support or disagree with this feedback'}
                        </p>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
