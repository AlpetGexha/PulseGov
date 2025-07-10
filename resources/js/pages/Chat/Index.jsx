import React, { useState, useEffect, useRef } from 'react';
import { Head } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Separator } from '@/components/ui/separator';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Bot, User, Send, Plus, Trash2 } from 'lucide-react';

const breadcrumbs = [
    {
        title: 'AI Chat',
        href: '/chat',
    },
];

export default function ChatIndex({ conversations: initialConversations = [] }) {
    const [conversations, setConversations] = useState(initialConversations);
    const [currentConversation, setCurrentConversation] = useState(null);
    const [messages, setMessages] = useState([]);
    const [newMessage, setNewMessage] = useState('');
    const [loading, setLoading] = useState(false);
    const [sending, setSending] = useState(false);
    const messagesEndRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages]);

    const createNewConversation = async () => {
        try {
            const response = await fetch('/chat/conversations', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                const data = await response.json();
                setConversations(prev => [data.conversation, ...prev]);
                setCurrentConversation(data.conversation);
                setMessages([]);
            }
        } catch (error) {
            console.error('Failed to create conversation:', error);
        }
    };

    const loadConversation = async (conversationId) => {
        if (currentConversation?.id === conversationId) return;

        setLoading(true);
        try {
            const response = await fetch(`/chat/conversations/${conversationId}`);
            if (response.ok) {
                const data = await response.json();
                setCurrentConversation(data.conversation);
                setMessages(data.conversation.messages || []);
            }
        } catch (error) {
            console.error('Failed to load conversation:', error);
        } finally {
            setLoading(false);
        }
    };

    const sendMessage = async (e) => {
        e.preventDefault();
        if (!newMessage.trim() || !currentConversation || sending) return;

        setSending(true);
        const messageText = newMessage.trim();
        setNewMessage('');

        // Add user message to UI immediately
        const userMessage = {
            id: Date.now(),
            role: 'user',
            content: messageText,
            created_at: new Date().toISOString()
        };
        setMessages(prev => [...prev, userMessage]);

        try {
            const response = await fetch(`/chat/conversations/${currentConversation.id}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ message: messageText })
            });

            if (response.ok) {
                const data = await response.json();

                // Replace the temporary user message with the actual one
                setMessages(prev => {
                    const filtered = prev.filter(msg => msg.id !== userMessage.id);
                    return [...filtered, data.user_message, data.assistant_message];
                });

                // Update conversation in sidebar
                setConversations(prev =>
                    prev.map(conv =>
                        conv.id === currentConversation.id
                            ? { ...conv, last_activity_at: new Date().toISOString() }
                            : conv
                    )
                );
            } else {
                // Remove the temporary message on error
                setMessages(prev => prev.filter(msg => msg.id !== userMessage.id));
                alert('Failed to send message. Please try again.');
            }
        } catch (error) {
            console.error('Failed to send message:', error);
            setMessages(prev => prev.filter(msg => msg.id !== userMessage.id));
            alert('Failed to send message. Please try again.');
        } finally {
            setSending(false);
        }
    };

    const deleteConversation = async (conversationId, e) => {
        e.stopPropagation();
        if (!confirm('Are you sure you want to delete this conversation?')) return;

        try {
            const response = await fetch(`/chat/conversations/${conversationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (response.ok) {
                setConversations(prev => prev.filter(conv => conv.id !== conversationId));
                if (currentConversation?.id === conversationId) {
                    setCurrentConversation(null);
                    setMessages([]);
                }
            }
        } catch (error) {
            console.error('Failed to delete conversation:', error);
        }
    };

    const formatDate = (dateString) => {
        return new Date(dateString).toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    const formatMessageTime = (dateString) => {
        return new Date(dateString).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="AI Chat - PulseGov" />

            <div className="flex h-[calc(100vh-200px)] bg-white rounded-lg shadow-sm border">
                {/* Sidebar */}
                <div className="w-1/4 border-r flex flex-col">
                    <div className="p-4 border-b">
                        <div className="flex items-center gap-2 mb-4">
                            <Bot className="h-6 w-6 text-blue-600" />
                            <h2 className="text-lg font-semibold">PulseGov AI</h2>
                        </div>
                        <Button
                            onClick={createNewConversation}
                            className="w-full"
                            variant="outline"
                        >
                            <Plus className="h-4 w-4 mr-2" />
                            New Conversation
                        </Button>
                    </div>

                    <div className="flex-1 overflow-y-auto">
                        <div className="p-4">
                            <h3 className="text-sm font-medium text-muted-foreground mb-3">
                                Conversations
                            </h3>
                            <div className="space-y-2">
                                {conversations.map((conversation) => (
                                    <Card
                                        key={conversation.id}
                                        className={`cursor-pointer transition-colors hover:bg-muted/50 ${
                                            currentConversation?.id === conversation.id
                                                ? 'bg-muted border-primary'
                                                : 'border-muted'
                                        }`}
                                        onClick={() => loadConversation(conversation.id)}
                                    >
                                        <CardContent className="p-3">
                                            <div className="flex items-start justify-between">
                                                <div className="flex-1 min-w-0">
                                                    <p className="text-sm font-medium truncate">
                                                        {conversation.title}
                                                    </p>
                                                    <p className="text-xs text-muted-foreground mt-1">
                                                        {formatDate(conversation.last_activity_at)}
                                                    </p>
                                                </div>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    className="h-6 w-6 p-0 opacity-0 group-hover:opacity-100"
                                                    onClick={(e) => deleteConversation(conversation.id, e)}
                                                >
                                                    <Trash2 className="h-3 w-3" />
                                                </Button>
                                            </div>
                                            <div className="mt-2 flex items-center gap-2">
                                                <Badge variant="secondary" className="text-xs">
                                                    {conversation.token_usage || 0} tokens
                                                </Badge>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="p-4 border-t">
                        <p className="text-xs text-muted-foreground text-center">
                            Your conversations are saved automatically
                        </p>
                    </div>
                </div>

                {/* Chat Area */}
                <div className="flex-1 flex flex-col">
                    {currentConversation ? (
                        <>
                            {/* Chat Header */}
                            <div className="p-4 border-b">
                                <h3 className="text-lg font-semibold">
                                    {currentConversation.title}
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                    {formatDate(currentConversation.last_activity_at)}
                                </p>
                            </div>

                            {/* Messages */}
                            <div className="flex-1 overflow-y-auto p-4 space-y-4">
                                {loading ? (
                                    <div className="flex justify-center items-center h-32">
                                        <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                                    </div>
                                ) : (
                                    messages.map((message) => (
                                        <div
                                            key={message.id}
                                            className={`flex ${
                                                message.role === 'user' ? 'justify-end' : 'justify-start'
                                            }`}
                                        >
                                            <div className="flex items-start space-x-3 max-w-3xl">
                                                {message.role === 'assistant' && (
                                                    <Avatar className="h-8 w-8">
                                                        <AvatarFallback className="bg-primary text-primary-foreground">
                                                            <Bot className="h-4 w-4" />
                                                        </AvatarFallback>
                                                    </Avatar>
                                                )}
                                                <div
                                                    className={`px-4 py-2 rounded-lg max-w-lg ${
                                                        message.role === 'user'
                                                            ? 'bg-primary text-primary-foreground'
                                                            : 'bg-muted'
                                                    }`}
                                                >
                                                    <div className="whitespace-pre-wrap text-sm">
                                                        {message.content}
                                                    </div>
                                                    <div className="text-xs opacity-70 mt-1">
                                                        {formatMessageTime(message.created_at)}
                                                    </div>
                                                </div>
                                                {message.role === 'user' && (
                                                    <Avatar className="h-8 w-8">
                                                        <AvatarFallback>
                                                            <User className="h-4 w-4" />
                                                        </AvatarFallback>
                                                    </Avatar>
                                                )}
                                            </div>
                                        </div>
                                    ))
                                )}
                                <div ref={messagesEndRef} />
                            </div>

                            {/* Message Input */}
                            <div className="p-4 border-t">
                                <form onSubmit={sendMessage} className="flex space-x-2">
                                    <Input
                                        type="text"
                                        value={newMessage}
                                        onChange={(e) => setNewMessage(e.target.value)}
                                        placeholder="Send a message..."
                                        className="flex-1"
                                        disabled={sending}
                                    />
                                    <Button
                                        type="submit"
                                        disabled={!newMessage.trim() || sending}
                                        className="px-4"
                                    >
                                        {sending ? (
                                            <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-foreground"></div>
                                        ) : (
                                            <Send className="h-4 w-4" />
                                        )}
                                    </Button>
                                </form>
                            </div>
                        </>
                    ) : (
                        <div className="flex-1 flex items-center justify-center">
                            <div className="text-center">
                                <div className="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <Bot className="h-8 w-8 text-primary" />
                                </div>
                                <h3 className="text-lg font-semibold mb-2">
                                    Welcome to PulseGov AI Assistant
                                </h3>
                                <p className="text-muted-foreground mb-4">
                                    Start a new conversation to analyze citizen feedback data
                                </p>
                                <Button onClick={createNewConversation}>
                                    <Plus className="h-4 w-4 mr-2" />
                                    Start New Conversation
                                </Button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
