import { Head } from '@inertiajs/react';
import { MapContainer, TileLayer, Marker, Popup, useMap } from 'react-leaflet';
import { useState, useEffect } from 'react';
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
    image_url: string;
    created_at: string;
    user: {
        name: string;
    };
    analysis: {
        summary: string;
        keywords: string[];
    };
}

interface Props {
    feedbacks: FeedbackLocation[];
}

// Custom hook for marker icons
const useMarkerIcon = (urgencyLevel: string) => {
    const colors: Record<string, string> = {
        LOW: 'green',
        MEDIUM: 'yellow',
        HIGH: 'orange',
        CRITICAL: 'red',
        DEFAULT: 'default'
    };

    const color = colors[urgencyLevel] || colors.DEFAULT;

    return new Icon({
        iconUrl: `/images/markers/${color}-marker.png`,
        shadowUrl: '/images/markers/marker-shadow.png',
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        popupAnchor: [1, -34],
        shadowSize: [41, 41]
    });
};

// Map center component
const MapCenter = ({ lat, lng }: { lat: number; lng: number }) => {
    const map = useMap();
    useEffect(() => {
        map.setView([lat, lng], 13);
    }, [lat, lng, map]);
    return null;
};

export default function Index({ feedbacks }: Props) {
    const [selectedFeedback, setSelectedFeedback] = useState<FeedbackLocation | null>(null);
    const [mapCenter, setMapCenter] = useState([42.3833, 20.4333]); // Default to Gjakova

    const handleMarkerClick = (feedback: FeedbackLocation) => {
        setSelectedFeedback(feedback);
        setMapCenter([feedback.latitude, feedback.longitude]);
    };

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900">
            <Head title="Feedback Map" />

            <div className="flex flex-col h-screen">
                {/* Header */}
                <div className="bg-white dark:bg-gray-800 shadow px-4 py-3">
                    <h1 className="text-xl font-semibold text-gray-900 dark:text-white">
                        Citizen Feedback Map
                    </h1>
                </div>

                {/* Main Content */}
                <div className="flex flex-1 overflow-hidden">
                    {/* Map Container */}
                    <div className="flex-1 relative">
                        <MapContainer
                            center={mapCenter as [number, number]}
                            zoom={13}
                            className="w-full h-full"
                        >
                            <TileLayer
                                url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                                attribution='&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                            />
                            <MapCenter lat={mapCenter[0]} lng={mapCenter[1]} />
                            
                            {feedbacks.map((feedback) => (
                                <Marker
                                    key={feedback.id}
                                    position={[feedback.latitude, feedback.longitude]}
                                    icon={useMarkerIcon(feedback.urgency_level)}
                                    eventHandlers={{
                                        click: () => handleMarkerClick(feedback),
                                    }}
                                >
                                    <Popup>
                                        <div className="text-sm">
                                            <h3 className="font-semibold">{feedback.title}</h3>
                                            <p className="text-gray-600">{feedback.location}</p>
                                        </div>
                                    </Popup>
                                </Marker>
                            ))}
                        </MapContainer>
                    </div>

                    {/* Sidebar */}
                    {selectedFeedback && (
                        <div className="w-96 bg-white dark:bg-gray-800 shadow-lg overflow-y-auto">
                            <div className="p-4">
                                {/* Close button */}
                                <div className="flex justify-between items-center mb-4">
                                    <h2 className="text-lg font-semibold text-gray-900 dark:text-white">
                                        Feedback Details
                                    </h2>
                                    <button
                                        onClick={() => setSelectedFeedback(null)}
                                        className="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>

                                {/* Feedback Image */}
                                <div className="mb-4">
                                    <img
                                        src={selectedFeedback.image_url}
                                        alt="Feedback"
                                        className="w-full h-48 object-cover rounded-lg"
                                    />
                                </div>

                                {/* Title and Description */}
                                <div className="mb-4">
                                    <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                                        {selectedFeedback.title}
                                    </h3>
                                    <p className="text-gray-600 dark:text-gray-400">
                                        {selectedFeedback.body}
                                    </p>
                                </div>

                                {/* Metadata Grid */}
                                <div className="grid grid-cols-2 gap-4 mb-4">
                                    <div className="col-span-2">
                                        <span className="text-gray-500 dark:text-gray-400">Location</span>
                                        <p className="font-medium text-gray-900 dark:text-white">
                                            {selectedFeedback.location}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-500 dark:text-gray-400">Status</span>
                                        <p className="font-medium text-gray-900 dark:text-white">
                                            {selectedFeedback.status}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-500 dark:text-gray-400">Urgency</span>
                                        <p className="font-medium text-gray-900 dark:text-white">
                                            {selectedFeedback.urgency_level}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-500 dark:text-gray-400">Department</span>
                                        <p className="font-medium text-gray-900 dark:text-white">
                                            {selectedFeedback.department_assigned}
                                        </p>
                                    </div>
                                    <div>
                                        <span className="text-gray-500 dark:text-gray-400">Submitted by</span>
                                        <p className="font-medium text-gray-900 dark:text-white">
                                            {selectedFeedback.user.name}
                                        </p>
                                    </div>
                                    <div className="col-span-2">
                                        <span className="text-gray-500 dark:text-gray-400">Date Submitted</span>
                                        <p className="font-medium text-gray-900 dark:text-white">
                                            {selectedFeedback.created_at}
                                        </p>
                                    </div>
                                </div>

                                {/* AI Analysis */}
                                {selectedFeedback.analysis.summary && (
                                    <div className="border-t dark:border-gray-700 pt-4">
                                        <h4 className="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                            AI Analysis
                                        </h4>
                                        <p className="text-gray-600 dark:text-gray-400 mb-3">
                                            {selectedFeedback.analysis.summary}
                                        </p>
                                        {selectedFeedback.analysis.keywords && (
                                            <div className="flex flex-wrap gap-2">
                                                {selectedFeedback.analysis.keywords.map((keyword, index) => (
                                                    <span
                                                        key={index}
                                                        className="px-2 py-1 text-sm bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-full"
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
            </div>
        </div>
    );
}