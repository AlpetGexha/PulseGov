import { Head } from '@inertiajs/react';
import { MapContainer, TileLayer, Marker, Popup } from 'react-leaflet';
import { useState } from 'react';
import { Icon } from 'leaflet';
import 'leaflet/dist/leaflet.css';

interface FeedbackLocation {
    id: number;
    title: string;
    body: string;
    location: string;
    latitude: number;
    longitude: number;
    sentiment: string;
    urgency_level: string;
    department_assigned: string;
    status: string;
    category: string;
    image_url: string;
    created_at: string;
    user: {
        name: string;
        avatar: string;
    };
    analysis: {
        summary: string;
        keywords: string[];
    };
}

interface Props {
    feedbacks: FeedbackLocation[];
}

export default function Index({ feedbacks }: Props) {
    const [selectedFeedback, setSelectedFeedback] = useState<FeedbackLocation | null>(null);

    const getMarkerIcon = (urgencyLevel: string) => {
        const colors: Record<string, string> = {
            LOW: 'green',
            MEDIUM: 'yellow',
            HIGH: 'orange',
            CRITICAL: 'red'
        };

        return new Icon({
            iconUrl: `/images/markers/${colors[urgencyLevel] || 'blue'}-marker.png`,
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34]
        });
    };

    return (
        <>
            <Head title="Feedback Map" />
            
            <div className="flex h-screen">
                <MapContainer
                    center={[42.3833, 20.4333]} // Gjakova coordinates
                    zoom={13}
                    className="w-3/4 h-full"
                >
                    <TileLayer
                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                        attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    />
                    
                    {feedbacks.map((feedback) => (
                        <Marker
                            key={feedback.id}
                            position={[feedback.latitude, feedback.longitude]}
                            icon={getMarkerIcon(feedback.urgency_level)}
                            eventHandlers={{
                                click: () => setSelectedFeedback(feedback),
                            }}
                        >
                            <Popup>
                                <h3 className="font-bold">{feedback.title}</h3>
                                <p className="text-sm">{feedback.location}</p>
                            </Popup>
                        </Marker>
                    ))}
                </MapContainer>

                {selectedFeedback && (
                    <div className="w-1/4 h-full overflow-y-auto p-4 bg-white dark:bg-gray-800 border-l">
                        <div className="flex justify-between items-start mb-4">
                            <h2 className="text-xl font-bold">{selectedFeedback.title}</h2>
                            <button
                                onClick={() => setSelectedFeedback(null)}
                                className="text-gray-500 hover:text-gray-700"
                            >
                                ×
                            </button>
                        </div>

                        <div className="space-y-4">
                            <img
                                src={selectedFeedback.image_url}
                                alt="Feedback"
                                className="w-full h-48 object-cover rounded"
                            />

                            <div className="flex items-center space-x-2">
                                <img
                                    src={selectedFeedback.user.avatar}
                                    alt={selectedFeedback.user.name}
                                    className="w-8 h-8 rounded-full"
                                />
                                <span className="text-sm">{selectedFeedback.user.name}</span>
                            </div>

                            <div className="grid grid-cols-2 gap-2 text-sm">
                                <div className="col-span-2">
                                    <span className="font-semibold">Location:</span> {selectedFeedback.location}
                                </div>
                                <div>
                                    <span className="font-semibold">Category:</span> {selectedFeedback.category}
                                </div>
                                <div>
                                    <span className="font-semibold">Status:</span> {selectedFeedback.status}
                                </div>
                                <div>
                                    <span className="font-semibold">Urgency:</span> {selectedFeedback.urgency_level}
                                </div>
                                <div>
                                    <span className="font-semibold">Department:</span> {selectedFeedback.department_assigned}
                                </div>
                                <div className="col-span-2">
                                    <span className="font-semibold">Date:</span> {selectedFeedback.created_at}
                                </div>
                            </div>

                            <div>
                                <h3 className="font-semibold mb-2">Description</h3>
                                <p className="text-sm">{selectedFeedback.body}</p>
                            </div>

                            {selectedFeedback.analysis.summary && (
                                <div>
                                    <h3 className="font-semibold mb-2">AI Analysis</h3>
                                    <p className="text-sm">{selectedFeedback.analysis.summary}</p>
                                    {selectedFeedback.analysis.keywords && (
                                        <div className="mt-2 flex flex-wrap gap-1">
                                            {selectedFeedback.analysis.keywords.map((keyword, index) => (
                                                <span
                                                    key={index}
                                                    className="px-2 py-1 text-xs bg-gray-100 dark:bg-gray-700 rounded"
                                                >
                                                    {keyword}
                                                </span>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </>
    );
}