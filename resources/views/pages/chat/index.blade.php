@extends('layouts.app')
@section('title', 'Live Chat')

@section('content')
<div class="flex h-[calc(100vh-120px)] bg-white rounded-xl overflow-hidden shadow-sm border border-outline-variant/30">
    <!-- Left Sidebar: Conversations -->
    <div class="w-1/3 border-r border-outline-variant/50 bg-surface-gray flex flex-col">
        <div class="p-4 border-b border-outline-variant/50 bg-white flex justify-between items-center">
            <h2 class="text-base font-bold text-primary flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">forum</span> Messages
            </h2>
        </div>
        <div class="flex-1 overflow-y-auto" id="conversations-list">
            <!-- Loaded via JS -->
            <div class="p-4 text-center text-xs text-on-surface-variant">Loading conversations...</div>
        </div>
    </div>

    <!-- Right Panel: Active Chat -->
    <div class="w-2/3 flex flex-col bg-surface-gray" id="chat-panel" style="display: none;">
        <!-- Header -->
        <div class="p-3 border-b border-outline-variant/50 bg-white flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary font-bold border border-primary/20">
                <span id="active-chat-initial" class="text-sm">C</span>
            </div>
            <div>
                <h3 class="font-bold text-sm text-on-surface" id="active-chat-name">Customer Name</h3>
                <p class="text-[10px] text-on-surface-variant" id="active-chat-subject">Subject</p>
            </div>
        </div>

        <!-- Messages -->
        <div class="flex-1 overflow-y-auto p-4 flex flex-col gap-3" id="messages-list">
            <!-- Loaded via JS -->
        </div>

        <!-- Input -->
        <div class="p-3 border-t border-outline-variant/50 bg-white">
            <form id="chat-form" class="flex gap-2">
                <input type="text" id="chat-input" class="flex-1 bg-surface-container rounded-full px-4 py-2 text-xs focus:outline-none focus:ring-1 focus:ring-primary border border-outline-variant/50" placeholder="Type a message..." required autocomplete="off">
                <button type="submit" class="w-9 h-9 rounded-full bg-primary text-on-primary flex items-center justify-center hover:bg-primary/90 transition-colors shadow-sm">
                    <span class="material-symbols-outlined text-[18px] ml-1">send</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Empty State -->
    <div class="w-2/3 flex flex-col items-center justify-center bg-surface-gray text-on-surface-variant" id="empty-state">
        <div class="w-16 h-16 rounded-full bg-surface-container-high flex items-center justify-center mb-3">
            <span class="material-symbols-outlined text-3xl opacity-50">forum</span>
        </div>
        <p class="text-sm font-medium">Your Messages</p>
        <p class="text-xs mt-1">Select a conversation to start chatting.</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.js"></script>
<script>
    if (typeof window.Echo === 'function') {
        try {
            window.Echo = new window.Echo({
                broadcaster: 'pusher',
                key: '{{ env('PUSHER_APP_KEY') }}',
                cluster: '{{ env('PUSHER_APP_CLUSTER') }}',
                forceTLS: true
            });
        } catch (e) {
            console.error('Echo init failed:', e);
        }
    }
    let activeConversationId = null;
    
    function loadConversations() {
        axios.get('{{ route('chat.conversations') }}')
            .then(res => {
                const list = document.getElementById('conversations-list');
                list.innerHTML = '';
                
                if(res.data.length === 0) {
                    list.innerHTML = '<div class="p-4 text-center text-xs text-on-surface-variant">No conversations yet.</div>';
                    return;
                }

                res.data.forEach(conv => {
                    const name = conv.customer ? conv.customer.name : 'Unknown Customer';
                    const lastMsg = conv.latest_message ? conv.latest_message.text : 'No messages yet';
                    const time = conv.latest_message ? new Date(conv.latest_message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '';
                    const unreadBadge = conv.unread_count > 0 
                        ? `<span class="bg-error text-white text-[9px] font-bold w-4 h-4 flex items-center justify-center rounded-full ml-2 shadow-sm">${conv.unread_count}</span>` 
                        : '';
                    
                    const div = document.createElement('div');
                    div.className = `p-3 border-b border-outline-variant/30 cursor-pointer hover:bg-surface-container transition-colors ${activeConversationId == conv.id ? 'bg-white border-l-4 border-l-primary' : 'bg-surface-gray border-l-4 border-l-transparent'}`;
                    div.onclick = () => openChat(conv.id, name, conv.subject);
                    div.innerHTML = `
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-bold text-xs flex items-center ${conv.unread_count > 0 ? 'text-primary' : 'text-on-surface'}">${name} ${unreadBadge}</h4>
                            <span class="text-[9px] ${conv.unread_count > 0 ? 'text-primary font-bold' : 'text-on-surface-variant'}">${time}</span>
                        </div>
                        <p class="text-[10px] truncate w-[90%] ${conv.unread_count > 0 ? 'font-bold text-on-surface' : 'text-on-surface-variant'}">${lastMsg}</p>
                    `;
                    list.appendChild(div);
                });
            });
    }

    function openChat(id, name, subject) {
        activeConversationId = id;
        document.getElementById('empty-state').style.display = 'none';
        document.getElementById('chat-panel').style.display = 'flex';
        document.getElementById('active-chat-name').innerText = name;
        document.getElementById('active-chat-subject').innerText = subject || 'Customers';
        document.getElementById('active-chat-initial').innerText = name.charAt(0).toUpperCase();

        loadConversations(); // Re-render to highlight active
        loadMessages(id);
    }

    function loadMessages(id) {
        const list = document.getElementById('messages-list');
        list.innerHTML = '<div class="text-center text-xs text-on-surface-variant py-4">Loading messages...</div>';
        
        axios.get(`/chat/${id}/messages`)
            .then(res => {
                list.innerHTML = '';
                if(res.data.length === 0) {
                    list.innerHTML = '<div class="text-center text-xs text-on-surface-variant py-4">This is the start of your conversation.</div>';
                }
                res.data.forEach(msg => appendMessage(msg));
                scrollToBottom();
            });
    }

    function appendMessage(msg) {
        if (document.getElementById('msg-' + msg.id)) return; // Prevent duplicate appends

        const list = document.getElementById('messages-list');
        // Remove empty state message if exists
        if(list.innerHTML.includes('start of your conversation')) list.innerHTML = '';

        const isMe = msg.sender_type === 'agent';
        
        const div = document.createElement('div');
        div.id = 'msg-' + msg.id;
        div.className = `flex flex-col max-w-[80%] ${isMe ? 'self-end items-end' : 'self-start items-start'}`;
        
        const bubble = document.createElement('div');
        bubble.className = `px-3 py-2 text-xs shadow-sm ${isMe ? 'bg-primary text-on-primary rounded-2xl rounded-br-sm' : 'bg-white text-on-surface rounded-2xl rounded-bl-sm border border-outline-variant/30'}`;
        bubble.innerText = msg.text;
        
        const time = document.createElement('span');
        time.className = 'text-[9px] text-on-surface-variant mt-1 px-1';
        time.innerText = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        div.appendChild(bubble);
        div.appendChild(time);
        list.appendChild(div);
        
        scrollToBottom();
    }

    function scrollToBottom() {
        const list = document.getElementById('messages-list');
        list.scrollTop = list.scrollHeight;
    }

    document.getElementById('chat-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const text = input.value.trim();
        if (!text || !activeConversationId) return;

        input.value = '';
        
        axios.post(`/chat/${activeConversationId}/messages`, { text: text })
            .then(res => {
                appendMessage(res.data);
                loadConversations();
            })
            .catch(err => {
                console.error("Failed to send", err);
                alert("Failed to send message");
            });
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadConversations();

        // Global listener for ANY incoming customer message to update sidebar and active chat
        if (typeof window.Echo !== 'undefined') {
            window.Echo.channel('admin.chat')
                .listen('.message.sent', (e) => {
                    loadConversations();
                    // If the incoming message belongs to the currently open chat, append it!
                    if (activeConversationId == e.conversation_id) {
                        appendMessage(e);
                        scrollToBottom();
                        
                        // Mark as read silently
                        axios.get(`/chat/${activeConversationId}/messages`).catch(()=>{});
                    }
                });
        }
    });
</script>
@endpush
