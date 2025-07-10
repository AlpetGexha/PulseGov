import React, { useEffect, useState } from 'react';
import axios from 'axios';

interface ProgressData {
    progress: number;
    message: string;
    status: 'pending' | 'processing' | 'completed' | 'failed';
}

interface Props {
    progressKey: string;
    onComplete?: () => void;
}

export default function ProgressTracker({ progressKey, onComplete }: Props) {
    const [progress, setProgress] = useState<ProgressData>({
        progress: 0,
        message: 'Starting...',
        status: 'pending'
    });
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        let intervalId: NodeJS.Timeout;

        const checkProgress = async () => {
            try {
                const response = await axios.get<ProgressData>(`/job-progress/${progressKey}`);
                setProgress(response.data);

                if (response.data.status === 'completed') {
                    clearInterval(intervalId);
                    onComplete?.();
                } else if (response.data.status === 'failed') {
                    clearInterval(intervalId);
                    setError(response.data.message);
                }
            } catch (err) {
                clearInterval(intervalId);
                setError('Failed to fetch progress');
            }
        };

        // Start polling
        intervalId = setInterval(checkProgress, 1000);
        checkProgress(); // Initial check

        // Cleanup
        return () => clearInterval(intervalId);
    }, [progressKey, onComplete]);

    return (
        <div className="space-y-4">
            {/* Progress Bar */}
            <div className="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div 
                    className={`h-2.5 rounded-full transition-all duration-500 ${
                        progress.status === 'failed' 
                            ? 'bg-red-600' 
                            : progress.status === 'completed'
                                ? 'bg-green-600'
                                : 'bg-blue-600'
                    }`}
                    style={{ width: `${progress.progress}%` }}
                />
            </div>

            {/* Progress Details */}
            <div className="flex justify-between items-center text-sm">
                <span className="text-gray-700 dark:text-gray-300">
                    {progress.message}
                </span>
                <span className="font-semibold">
                    {progress.progress}%
                </span>
            </div>

            {/* Error Message */}
            {error && (
                <div className="text-red-600 dark:text-red-400 text-sm mt-2">
                    {error}
                </div>
            )}
        </div>
    );
}
