<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enum\FeedbackSentiment;
use App\Enum\FeedbackStatus;
use App\Enum\FeedbackType;
use App\Enum\UrgencyLevel;
use App\Enum\VoteType;
use App\Models\Feedback;
use App\Models\FeedbackComment;
use App\Models\FeedbackVote;
use App\Models\User;
use Illuminate\Database\Seeder;

final class FeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create additional users for seeding
        $users = User::factory()->count(30)->create();

        // Check if admin user exists, if not create one
        $adminUser = User::where('email', 'admin@pulsegov.com')->first();
        if (! $adminUser) {
            $adminUser = User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@pulsegov.com',
            ]);
        }

        $allUsers = $users->concat([$adminUser]);

        // Realistic feedback data
        $feedbackData = [
            [
                'title' => 'Broken streetlights on Main Street creating safety hazard',
                'body' => 'Several streetlights between 5th and 8th Avenue on Main Street have been out for over two weeks now. This is creating a dangerous situation for pedestrians and drivers, especially during evening hours. I\'ve personally witnessed two near-miss accidents due to poor visibility. The community desperately needs these lights fixed urgently.',
                'location' => 'Main Street, between 5th and 8th Avenue',
                'service' => 'Street Lighting',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::HIGH,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Public Works',
                'intent' => 'infrastructure repair',
                'topic_cluster' => 'public safety',
                'is_public' => true,
            ],
            [
                'title' => 'Pothole damage on Elm Street needs immediate attention',
                'body' => 'There\'s a massive pothole on Elm Street near the school zone that has been growing larger every day. Multiple cars have suffered tire damage, and it\'s only a matter of time before someone gets seriously hurt. The city needs to prioritize road maintenance in residential areas, especially near schools where children walk daily.',
                'location' => 'Elm Street, near Washington Elementary',
                'service' => 'Road Maintenance',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::CRITICAL,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Public Works',
                'intent' => 'infrastructure repair',
                'topic_cluster' => 'transportation',
                'is_public' => true,
            ],
            [
                'title' => 'Excellent new bike lane implementation on Park Avenue',
                'body' => 'I want to commend the city for the fantastic new bike lane on Park Avenue. The separated lanes make cycling much safer, and the clear markings are excellent. This has encouraged more people in our neighborhood to cycle to work. Please continue expanding the bike lane network throughout the city!',
                'location' => 'Park Avenue',
                'service' => 'Transportation Planning',
                'feedback_type' => FeedbackType::PRAISE,
                'urgency_level' => UrgencyLevel::LOW,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'status' => FeedbackStatus::IMPLEMENTED,
                'department_assigned' => 'Transportation',
                'intent' => 'positive feedback',
                'topic_cluster' => 'transportation',
                'is_public' => true,
            ],
            [
                'title' => 'Lack of public restrooms in downtown area',
                'body' => 'The downtown area desperately needs more public restrooms. During events and busy shopping days, finding a clean, accessible restroom becomes a real challenge. This affects not only tourists but also elderly residents and families with young children. Consider installing modern, self-cleaning public restroom facilities.',
                'location' => 'Downtown District',
                'service' => 'Public Facilities',
                'feedback_type' => FeedbackType::SUGGESTION,
                'urgency_level' => UrgencyLevel::MEDIUM,
                'sentiment' => FeedbackSentiment::NEUTRAL,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Parks and Recreation',
                'intent' => 'infrastructure improvement',
                'topic_cluster' => 'public amenities',
                'is_public' => true,
            ],
            [
                'title' => 'Bus stop benches missing or broken throughout the city',
                'body' => 'Many bus stops across the city either lack benches entirely or have broken ones that haven\'t been repaired in months. This is particularly problematic for elderly citizens and people with disabilities who need to rest while waiting for public transport. The city should prioritize maintaining and installing proper seating at all bus stops.',
                'location' => 'Various bus stops citywide',
                'service' => 'Public Transit',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::MEDIUM,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Transportation',
                'intent' => 'accessibility improvement',
                'topic_cluster' => 'public transit',
                'is_public' => true,
            ],
            [
                'title' => 'Suggest implementing a city-wide composting program',
                'body' => 'Our city should consider implementing a comprehensive composting program to reduce organic waste going to landfills. Many residents want to compost but lack the space or knowledge. A city-run program could provide bins, pickup services, and educational resources. This would significantly reduce our environmental impact and could create jobs in the green sector.',
                'location' => 'Citywide',
                'service' => 'Waste Management',
                'feedback_type' => FeedbackType::SUGGESTION,
                'urgency_level' => UrgencyLevel::LOW,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Environmental Services',
                'intent' => 'environmental initiative',
                'topic_cluster' => 'sustainability',
                'is_public' => true,
            ],
            [
                'title' => 'Noise pollution from construction sites exceeding legal hours',
                'body' => 'Construction work on the new apartment complex on Cedar Street regularly continues past 6 PM and starts before 7 AM, violating city noise ordinances. This has been ongoing for weeks, disrupting sleep and family time for dozens of residents. The city needs to better enforce construction hour regulations and impose meaningful penalties for violations.',
                'location' => 'Cedar Street construction site',
                'service' => 'Code Enforcement',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::HIGH,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Code Enforcement',
                'intent' => 'noise complaint',
                'topic_cluster' => 'quality of life',
                'is_public' => true,
            ],
            [
                'title' => 'Outstanding snow removal service during last winter storm',
                'body' => 'I wanted to praise the snow removal crews for their exceptional work during the February blizzard. Roads were cleared quickly and efficiently, and the crews worked around the clock to keep our city moving. The coordination between different departments was impressive. Thank you for keeping our community safe during challenging weather conditions.',
                'location' => 'Citywide',
                'service' => 'Snow Removal',
                'feedback_type' => FeedbackType::PRAISE,
                'urgency_level' => UrgencyLevel::LOW,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'status' => FeedbackStatus::RESOLVED,
                'department_assigned' => 'Public Works',
                'intent' => 'positive feedback',
                'topic_cluster' => 'emergency services',
                'is_public' => true,
            ],
            [
                'title' => 'Inadequate parking enforcement in residential areas',
                'body' => 'Residential parking restrictions are not being enforced properly in the Riverside neighborhood. Non-residents park all day in 2-hour zones, making it impossible for residents to find parking near their homes. This issue has gotten worse since the new shopping center opened. We need more frequent patrol and consistent enforcement of parking regulations.',
                'location' => 'Riverside neighborhood',
                'service' => 'Parking Enforcement',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::MEDIUM,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Traffic Control',
                'intent' => 'enforcement request',
                'topic_cluster' => 'parking',
                'is_public' => true,
            ],
            [
                'title' => 'Install more playground equipment at Riverside Park',
                'body' => 'Riverside Park could benefit from additional playground equipment, especially for children ages 8-12. Currently, most equipment is designed for younger kids. Adding climbing structures, swings for older children, and maybe a basketball court would make this park more inclusive and engaging for families with children of various ages.',
                'location' => 'Riverside Park',
                'service' => 'Parks and Recreation',
                'feedback_type' => FeedbackType::SUGGESTION,
                'urgency_level' => UrgencyLevel::LOW,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Parks and Recreation',
                'intent' => 'facility improvement',
                'topic_cluster' => 'recreation',
                'is_public' => true,
            ],
            [
                'title' => 'Dangerous intersection needs traffic signal at Oak and 3rd',
                'body' => 'The intersection of Oak Street and 3rd Avenue has become increasingly dangerous with growing traffic volume. There have been multiple accidents this year, including one serious injury. A four-way stop sign is no longer adequate for this intersection. Please conduct a traffic study and consider installing a traffic signal to improve safety for both drivers and pedestrians.',
                'location' => 'Oak Street and 3rd Avenue intersection',
                'service' => 'Traffic Control',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::HIGH,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Transportation',
                'intent' => 'safety improvement',
                'topic_cluster' => 'traffic safety',
                'is_public' => true,
            ],
            [
                'title' => 'Excellent response from fire department during apartment fire',
                'body' => 'I want to commend the fire department for their incredible response during the apartment fire on Maple Street last week. They arrived within minutes, evacuated residents safely, and contained the fire quickly. The professionalism and bravery shown by the entire crew was outstanding. Our community is lucky to have such dedicated emergency responders.',
                'location' => 'Maple Street apartment complex',
                'service' => 'Fire Department',
                'feedback_type' => FeedbackType::PRAISE,
                'urgency_level' => UrgencyLevel::LOW,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'status' => FeedbackStatus::RESOLVED,
                'department_assigned' => 'Fire Department',
                'intent' => 'positive feedback',
                'topic_cluster' => 'emergency services',
                'is_public' => true,
            ],
            [
                'title' => 'Stray dog problem in Lincoln Park area needs addressing',
                'body' => 'There\'s been a growing problem with stray dogs in the Lincoln Park area. Several residents have reported aggressive behavior from a pack of dogs that seems to have made the park their territory. This is making the park unsafe for families with children and other pet owners. Animal control needs to address this situation before someone gets hurt.',
                'location' => 'Lincoln Park',
                'service' => 'Animal Control',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::HIGH,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Animal Control',
                'intent' => 'safety concern',
                'topic_cluster' => 'public safety',
                'is_public' => true,
            ],
            [
                'title' => 'Propose mobile library service for underserved neighborhoods',
                'body' => 'Many residents in the Eastside neighborhood lack easy access to library services due to distance and transportation issues. A mobile library service could bring books, internet access, and educational programs directly to these communities. This would be especially beneficial for children, elderly residents, and families without reliable transportation.',
                'location' => 'Eastside neighborhood',
                'service' => 'Library Services',
                'feedback_type' => FeedbackType::SUGGESTION,
                'urgency_level' => UrgencyLevel::MEDIUM,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Library Services',
                'intent' => 'service expansion',
                'topic_cluster' => 'education',
                'is_public' => true,
            ],
            [
                'title' => 'Broken sidewalks creating accessibility barriers',
                'body' => 'Multiple sidewalks in the downtown area have large cracks, raised sections, and missing pieces that make them dangerous for wheelchair users and people with mobility aids. The city needs to prioritize sidewalk repairs to ensure equal access for all residents. This is not just a convenience issue - it\'s a civil rights issue.',
                'location' => 'Downtown sidewalks',
                'service' => 'Public Works',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::HIGH,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Public Works',
                'intent' => 'accessibility improvement',
                'topic_cluster' => 'accessibility',
                'is_public' => true,
            ],
            [
                'title' => 'Water pressure issues in Highland district need investigation',
                'body' => 'Residents in the Highland district have been experiencing low water pressure for the past month. This affects daily activities like showering, washing dishes, and maintaining gardens. The problem seems to be getting worse during peak usage hours. The water department should investigate the cause and implement a solution quickly.',
                'location' => 'Highland district',
                'service' => 'Water Services',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::MEDIUM,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Water Department',
                'intent' => 'service issue',
                'topic_cluster' => 'utilities',
                'is_public' => true,
            ],
            [
                'title' => 'Suggest expanding recycling program to include electronics',
                'body' => 'The city\'s recycling program is good, but it should be expanded to include electronics and batteries. Many residents don\'t know how to properly dispose of old phones, computers, and batteries, leading to environmental hazards. A monthly electronics recycling drive or permanent drop-off locations would help residents dispose of these items responsibly.',
                'location' => 'Citywide',
                'service' => 'Waste Management',
                'feedback_type' => FeedbackType::SUGGESTION,
                'urgency_level' => UrgencyLevel::LOW,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Environmental Services',
                'intent' => 'environmental initiative',
                'topic_cluster' => 'sustainability',
                'is_public' => true,
            ],
            [
                'title' => 'Excessive wait times at city permit office',
                'body' => 'The wait times at the city permit office have become unreasonable. I waited over 3 hours just to renew a business license, and this seems to be a common experience. The city should either hire more staff, implement an appointment system, or offer more online services to reduce wait times. This inefficiency is hurting local businesses.',
                'location' => 'City Hall permit office',
                'service' => 'Permit Services',
                'feedback_type' => FeedbackType::PROBLEM,
                'urgency_level' => UrgencyLevel::MEDIUM,
                'sentiment' => FeedbackSentiment::NEGATIVE,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Administrative Services',
                'intent' => 'service improvement',
                'topic_cluster' => 'government services',
                'is_public' => true,
            ],
            [
                'title' => 'Fantastic new community garden program',
                'body' => 'The new community garden program has been a huge success! The gardens are bringing neighbors together, teaching kids about growing food, and beautifying our neighborhoods. The staff has been incredibly helpful with guidance and resources. Please continue expanding this program to more neighborhoods across the city.',
                'location' => 'Community gardens citywide',
                'service' => 'Parks and Recreation',
                'feedback_type' => FeedbackType::PRAISE,
                'urgency_level' => UrgencyLevel::LOW,
                'sentiment' => FeedbackSentiment::POSITIVE,
                'status' => FeedbackStatus::IMPLEMENTED,
                'department_assigned' => 'Parks and Recreation',
                'intent' => 'positive feedback',
                'topic_cluster' => 'community programs',
                'is_public' => true,
            ],
            [
                'title' => 'Implement smart traffic lights to reduce congestion',
                'body' => 'The city should consider upgrading to smart traffic lights that can adapt to real-time traffic conditions. Current traffic lights cause unnecessary delays during off-peak hours and don\'t optimize flow during rush hour. Smart lights could reduce commute times, save fuel, and reduce emissions. This technology is already successful in many other cities.',
                'location' => 'Major intersections citywide',
                'service' => 'Traffic Control',
                'feedback_type' => FeedbackType::SUGGESTION,
                'urgency_level' => UrgencyLevel::LOW,
                'sentiment' => FeedbackSentiment::NEUTRAL,
                'status' => FeedbackStatus::UNDER_REVIEW,
                'department_assigned' => 'Transportation',
                'intent' => 'technology improvement',
                'topic_cluster' => 'traffic management',
                'is_public' => true,
            ],
        ];

        // Create feedback entries
        foreach ($feedbackData as $index => $data) {
            $user = $allUsers->random();

            $feedback = Feedback::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'body' => $data['body'],
                'location' => $data['location'],
                'service' => $data['service'],
                'is_public' => $data['is_public'],
                'sentiment' => $data['sentiment']->value,
                'status' => $data['status']->value,
                'feedback_type' => $data['feedback_type']->value,
                'urgency_level' => $data['urgency_level']->value,
                'intent' => $data['intent'],
                'topic_cluster' => $data['topic_cluster'],
                'department_assigned' => $data['department_assigned'],
                'tracking_code' => 'PG-' . mb_str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(0, 5)),
            ]);

            // Add votes for each feedback
            $this->addVotesToFeedback($feedback, $allUsers);

            // Add comments to each feedback
            $this->addCommentsToFeedback($feedback, $allUsers, $adminUser);
        }
    }

    private function addVotesToFeedback(Feedback $feedback, $users): void
    {
        // Get all users except the feedback author
        $availableVoters = $users->where('id', '!=', $feedback->user_id);

        // If no available voters, skip voting
        if ($availableVoters->count() === 0) {
            return;
        }

        // Randomly select users to vote (between 3 and available voters count)
        $maxVoters = $availableVoters->count();
        $votersCount = rand(3, max(3, min($maxVoters, 15))); // Cap at 15 voters max

        // E        nsure we don't try to select more users than available
        $votersCount = min($votersCount, $availableVoters->count());

        $voters = $availableVoters->random($votersCount);

        foreach ($voters as $voter) {
            // 70% chance of upvote, 30% chance of downvote
            $voteType = rand(1, 100) <= 70 ? VoteType::UPVOTE : VoteType::DOWNVOTE;

            FeedbackVote::create([
                'feedback_id' => $feedback->id,
                'user_id' => $voter->id,
                'vote' => $voteType->value,
                'created_at' => now()->subDays(rand(0, 20)),
            ]);
        }
    }

    private function addCommentsToFeedback(Feedback $feedback, $users, $adminUser): void
    {
        // Create realistic comments based on feedback type
        $comments = $this->getCommentsForFeedback($feedback);

        foreach ($comments as $commentData) {
            // Choose appropriate user (admin for official responses, regular users for community comments)
            $commenter = $commentData['is_official'] ? $adminUser : $users->random();

            // Don't let users comment on their own feedback for the first comment
            if ($commenter->id === $feedback->user_id && count($comments) === 1) {
                $availableCommenters = $users->where('id', '!=', $feedback->user_id);
                if ($availableCommenters->count() > 0) {
                    $commenter = $availableCommenters->random();
                } else {
                    // If no other users available, use admin
                    $commenter = $adminUser;
                }
            }

            FeedbackComment::create([
                'feedback_id' => $feedback->id,
                'user_id' => $commenter->id,
                'content' => $commentData['content'],
                'is_pinned' => $commentData['is_pinned'] ?? false,
                'created_at' => now()->subDays(rand(0, 15)),
            ]);
        }
    }

    private function getCommentsForFeedback(Feedback $feedback): array
    {
        $comments = [];

        // Add community comments
        $communityComments = [
            'I\'ve experienced the same issue in my neighborhood. Thanks for bringing this to attention!',
            'This has been a problem for months. Glad someone finally reported it.',
            'I support this suggestion. It would benefit many residents.',
            'Has anyone contacted the relevant department about this?',
            'I witnessed this problem myself. Something needs to be done.',
            'Great idea! I hope the city considers implementing this.',
            'This affects my daily commute. Please prioritize this issue.',
            'I\'m willing to volunteer if community help is needed.',
            'Similar issues exist in other parts of the city too.',
            'Thank you for your service and dedication to our community.',
        ];

        // Add 1-3 community comments
        for ($i = 0; $i < rand(1, 3); $i++) {
            $comments[] = [
                'content' => $communityComments[array_rand($communityComments)],
                'is_official' => false,
                'is_pinned' => false,
            ];
        }

        // Add official response based on feedback type and status
        if ($feedback->status === FeedbackStatus::RESOLVED->value || $feedback->status === FeedbackStatus::IMPLEMENTED->value) {
            $comments[] = [
                'content' => $this->getOfficialResolvedResponse($feedback),
                'is_official' => true,
                'is_pinned' => true,
            ];
        } else {
            $comments[] = [
                'content' => $this->getOfficialUnderReviewResponse($feedback),
                'is_official' => true,
                'is_pinned' => true,
            ];
        }

        return $comments;
    }

    private function getOfficialResolvedResponse(Feedback $feedback): string
    {
        $responses = [
            'Thank you for your feedback. This issue has been resolved by our team. We appreciate your patience.',
            'Update: The necessary repairs have been completed. Thank you for bringing this to our attention.',
            'This matter has been successfully addressed. We will continue monitoring the situation.',
            'Resolution complete. Thank you for your civic engagement and for helping us improve our community.',
            'The issue has been resolved. We appreciate your detailed report which helped us address this quickly.',
        ];

        return $responses[array_rand($responses)];
    }

    private function getOfficialUnderReviewResponse(Feedback $feedback): string
    {
        $responses = [
            'Thank you for your feedback. We have forwarded this to the appropriate department for review and will provide updates as they become available.',
            'Your concern has been logged and assigned to our team. We will investigate and respond with our findings.',
            'We appreciate you bringing this to our attention. This matter is currently under review by the relevant department.',
            'Thank you for your report. We are evaluating this issue and will provide an update once we have more information.',
            'Your feedback has been received and is being reviewed. We will keep you informed of our progress.',
        ];

        return $responses[array_rand($responses)];
    }
}
