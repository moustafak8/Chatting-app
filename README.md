# Chatting-app
A lightweight real-time messaging system supporting user-to-user conversations, message delivery/read tracking, and an AI-powered catch-up summary whenever a user has 3+ unread messages.

Features:

User Authentication (Login / Register)

1:1 Conversations with automatic conversation creation

Real-Time Messaging Flow

Messages saved to messages table

Delivery & read receipts stored in message_status

Message Sync: Fetch all messages for a conversation in correct order

AI Catch-Up Summary (OpenAI)
Automatically generates a short summary when a user has missed 3+ messages.

Clean UI Template for chat layout (HTML/CSS/JS)

 Tech Stack

Backend: PHP (Core), MySQL

Frontend: HTML, CSS, JavaScript

AI: OpenAI gpt-4o-mini

Database: MySQL with foreign keys & message status tracking
