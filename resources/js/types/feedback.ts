export interface User {
    id: number;
    name: string;
    email: string;
    profile_photo_url?: string;
}

export interface Comment {
    id: number;
    content: string;
    user: User;
    created_at: string;
    updated_at: string;
    parent_id?: number;
    replies?: Comment[];
}

export interface Vote {
    id: number;
    user_id: number;
    feedback_id: number;
    vote: 'upvote' | 'downvote';
    created_at: string;
}

export interface FeedbackStatus {
    value: string;
    label: string;
    color: string;
    icon: string;
}

export interface Feedback {
    id: number;
    title: string;
    body: string;
    service?: string;
    location?: string;
    feedback_type: 'suggestion' | 'complaint' | 'question' | 'compliment';
    is_public: boolean;
    status: string;
    tracking_code: string;
    sentiment?: 'positive' | 'negative' | 'neutral';
    department_assigned?: string;
    user: User;
    comments?: Comment[];
    votes?: Vote[];
    created_at: string;
    updated_at: string;
    ai_analysis_details?: {
        summary?: string;
        suggested_tags?: string[];
    };
}

export interface PaginatedFeedback {
    data: Feedback[];
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface FeedbackIndexProps {
    feedbacks: PaginatedFeedback;
    auth: {
        user: User | null;
    };
    statuses: FeedbackStatus[];
    feedbackTypes: { value: string; label: string }[];
    urgencyLevels: { value: string; label: string }[];
}

export interface FeedbackShowProps {
    feedback: Feedback;
    auth: {
        user: User | null;
    };
    statuses: FeedbackStatus[];
    statusColors: Record<string, string>;
    statusIcons: Record<string, string>;
}
