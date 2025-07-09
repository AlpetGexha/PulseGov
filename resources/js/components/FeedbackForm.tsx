import { useState } from 'react';
import { useForm } from '@inertiajs/react';
import {
    AlertTriangle,
    Lightbulb,
    HelpCircle,
    Award,
    Plus,
    Eye,
    EyeOff,
} from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogFooter,
    DialogClose
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select';
import { toast } from 'sonner';

interface FeedbackFormProps {
    isOpen: boolean;
    onOpenChange: (open: boolean) => void;
}

export default function FeedbackForm({ isOpen, onOpenChange }: FeedbackFormProps) {
    const { data, setData, post, processing, reset, errors } = useForm({
        title: '',
        body: '',
        service: '',
        location: '',
        feedback_type: 'suggestion',
        is_public: false, // Default to private
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('feedback.store'), {
            onSuccess: () => {
                reset();
                onOpenChange(false);
                toast.success('Feedback submitted successfully!');
            },
            onError: () => {
                toast.error('Please check your input and try again.');
            }
        });
    };

    const handleClose = () => {
        reset();
        onOpenChange(false);
    };

    return (
        <Dialog open={isOpen} onOpenChange={onOpenChange}>
            <DialogContent className="sm:max-w-[600px] max-h-[90vh] overflow-y-auto">
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
                                        <Lightbulb className="h-4 w-4 text-yellow-500" />
                                        Suggestion/Idea
                                    </div>
                                </SelectItem>
                                <SelectItem value="complaint">
                                    <div className="flex items-center gap-2">
                                        <AlertTriangle className="h-4 w-4 text-red-500" />
                                        Issue/Complaint
                                    </div>
                                </SelectItem>
                                <SelectItem value="question">
                                    <div className="flex items-center gap-2">
                                        <HelpCircle className="h-4 w-4 text-blue-500" />
                                        Question
                                    </div>
                                </SelectItem>
                                <SelectItem value="compliment">
                                    <div className="flex items-center gap-2">
                                        <Award className="h-4 w-4 text-green-500" />
                                        Praise/Compliment
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        {errors.feedback_type && <p className="text-sm text-red-600">{errors.feedback_type}</p>}
                    </div>

                    {/* Title */}
                    <div className="space-y-2">
                        <Label htmlFor="title">Title *</Label>
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
                        <Label htmlFor="body">Details *</Label>
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
                        <Label htmlFor="service">Related Service</Label>
                        <Input
                            id="service"
                            value={data.service || ''}
                            onChange={(e) => setData('service', e.target.value)}
                            placeholder="Which public service is this about? (Optional)"
                        />
                        {errors.service && <p className="text-sm text-red-600">{errors.service}</p>}
                    </div>

                    {/* Location */}
                    <div className="space-y-2">
                        <Label htmlFor="location">Location</Label>
                        <Input
                            id="location"
                            value={data.location || ''}
                            onChange={(e) => setData('location', e.target.value)}
                            placeholder="Where did this occur? (Optional)"
                        />
                        {errors.location && <p className="text-sm text-red-600">{errors.location}</p>}
                    </div>

                    {/* Privacy Setting */}
                    <div className="flex items-center space-x-3 p-4 rounded-lg border bg-gray-50/50">
                        <Checkbox
                            id="is_public"
                            checked={data.is_public}
                            onCheckedChange={(checked) => setData('is_public', !!checked)}
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
                                onClick={handleClose}
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
    );
}
