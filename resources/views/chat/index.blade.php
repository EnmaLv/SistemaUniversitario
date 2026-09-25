<x-app-layout>
    <div class="fixed top-16 left-0 right-0 h-[calc(100dvh-4rem)] bg-gray-100 dark:bg-gray-900 flex overflow-hidden transition-all duration-300"
        :class="sidebarOpen ? 'lg:left-56' : 'lg:left-16'" x-data="chatComponent">
        <div
            class="w-80 md:w-96 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col h-full shrink-0">
            <div class="p-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white leading-none">Chats</h1>
            </div>
            <div class="px-4 pb-4">
                <div class="relative group">
                    <input type="text" placeholder="Buscar en Messenger"
                        class="w-full bg-gray-100 dark:bg-gray-700 border-none rounded-full py-2.5 pl-11 pr-4 text-sm focus:ring-0 focus:bg-gray-100 dark:focus:bg-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white transition-all">
                    <svg class="w-5 h-5 absolute left-3.5 top-2.5 text-gray-400 dark:text-gray-500 group-focus-within:text-blue-500 transition-colors"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="px-4 flex gap-2 mb-2 overflow-x-auto no-scrollbar">
                <button @click="filter = 'todos'"
                    :class="filter === 'todos' ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400' :
                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap">Todos</button>
                <button @click="filter = 'no_leidos'"
                    :class="filter === 'no_leidos' ? 'bg-blue-50 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400' :
                        'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-600 dark:text-gray-400'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap line-through decoration-gray-400">No
                    leídos</button>
            </div>

            <div class="flex-1 overflow-y-auto px-2 space-y-0.5 no-scrollbar">
                <template x-for="contact in contacts" :key="contact.id">
                    <div @click="selectContact(contact)"
                        class="flex items-center gap-3 p-3 rounded-xl cursor-pointer transition select-none"
                        :class="selectedContact && selectedContact.id === contact.id ? 'bg-blue-50/70 dark:bg-blue-900/30' :
                            'hover:bg-gray-100/70 dark:hover:bg-gray-700/50'">
                        <div class="relative shrink-0">
                            <template x-if="contact.profile_photo">
                                <div
                                    class="w-14 h-14 rounded-full overflow-hidden bg-gradient-to-tr from-blue-600 to-indigo-700 shadow-md group-hover:scale-105 transition transform">
                                    <img :src="contact.profile_photo" alt="Foto de perfil"
                                        class="w-full h-full object-cover">
                                </div>
                            </template>
                            <template x-if="!contact.profile_photo">
                                <div class="w-14 h-14 rounded-full bg-gradient-to-tr from-blue-600 to-indigo-700 flex items-center justify-center text-white font-bold text-lg shadow-sm group-hover:scale-105 transition transform"
                                    x-text="contact.avatar"></div>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h4 class="font-bold text-gray-900 dark:text-white truncate text-[15px]"
                                    x-text="contact.name"></h4>
                                <div x-show="contact.unreadCount > 0"
                                    class="w-2.5 h-2.5 bg-blue-600 rounded-full shrink-0"></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-[13px] truncate"
                                    :class="contact.unread || contact.unreadCount > 0 ?
                                        'font-bold text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'"
                                    x-text="contact.lastMessage"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
        <div class="flex-1 flex flex-col bg-white dark:bg-gray-800 h-full overflow-hidden relative">

            <template x-if="selectedContact">
                <div class="flex-1 flex flex-col h-full">
                    <div
                        class="h-16 px-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between shrink-0 shadow-sm z-10 bg-white/95 dark:bg-gray-800/95 backdrop-blur-sm">
                        <div class="flex items-center gap-3">
                            <div class="relative group cursor-pointer">
                                <template x-if="selectedContact.profile_photo">
                                    <div
                                        class="w-10 h-10 rounded-full overflow-hidden bg-blue-100 dark:bg-blue-900/50 shadow-md">
                                        <img :src="selectedContact.profile_photo" alt="Foto de perfil"
                                            class="w-full h-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!selectedContact.profile_photo">
                                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-700 dark:text-blue-400 font-bold text-sm"
                                        x-text="selectedContact.avatar"></div>
                                </template>
                            </div>
                            <div>
                                <h2 class="font-bold text-gray-900 dark:text-white leading-tight"
                                    x-text="selectedContact.name"></h2>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4 md:p-8 space-y-4 no-scrollbar bg-white dark:bg-gray-800"
                        id="messages-container">

                        <div class="flex flex-col items-center py-10">
                            <template x-if="selectedContact.profile_photo">
                                <div class="w-20 h-20 rounded-full overflow-hidden bg-blue-600 shadow-xl">
                                    <img :src="selectedContact.profile_photo" alt="Foto de perfil"
                                        class="w-full h-full object-cover">
                                </div>
                            </template>
                            <template x-if="!selectedContact.profile_photo">
                                <div class="w-20 h-20 rounded-full bg-blue-600 flex items-center justify-center text-white text-2xl font-black mb-4 shadow-xl"
                                    x-text="selectedContact.avatar"></div>
                            </template>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="selectedContact.name">
                            </h3>
                            <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-1">Has iniciado una conversación.
                                Todos los mensajes son privados.</p>
                        </div>

                        <div x-show="isLoading" class="text-center text-gray-400 dark:text-gray-500 text-sm py-4">
                            Cargando mensajes...</div>
                        <template x-for="msg in messages" :key="msg.id">
                            <div>
                                <template x-if="msg.is_mine">
                                    <div class="flex flex-col items-end gap-1">
                                        <div class="max-w-[70%]">
                                            <div
                                                class="p-3.5 bg-blue-600 text-white rounded-2xl rounded-br-none text-[15px] leading-relaxed shadow-md shadow-blue-100 dark:shadow-blue-900/30 flex flex-col">
                                                <span x-text="msg.body"></span>
                                                <span class="text-[9px] text-blue-200 self-end mt-1"
                                                    x-text="msg.time"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!msg.is_mine">
                                    <div class="flex items-end gap-2 group">
                                        <template x-if="selectedContact.profile_photo">
                                            <div
                                                class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 shrink-0">
                                                <img :src="selectedContact.profile_photo" alt="Foto de perfil"
                                                    class="w-full h-full object-cover">
                                            </div>
                                        </template>
                                        <template x-if="!selectedContact.profile_photo">
                                            <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 shrink-0 text-[10px] flex items-center justify-center font-bold text-gray-600 dark:text-gray-400"
                                                x-text="selectedContact.avatar"></div>
                                        </template>
                                        <div class="max-w-[70%]">
                                            <div
                                                class="p-3.5 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-2xl rounded-bl-none text-[15px] leading-relaxed shadow-sm flex flex-col">
                                                <span x-text="msg.body"></span>
                                                <span
                                                    class="text-[9px] text-gray-400 dark:text-gray-500 self-start mt-1"
                                                    x-text="msg.time"></span>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>

                    <div class="p-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 shrink-0">
                        <div class="flex items-center gap-2 max-w-5xl mx-auto">
                            <div class="flex-1 relative">
                                <input type="text" x-model="newMessage" @keydown.enter="sendMessage"
                                    placeholder="Escribe un mensaje..."
                                    class="w-full bg-gray-100 dark:bg-gray-700 border-none rounded-full py-2.5 px-5 text-[15px] focus:ring-0 focus:bg-gray-200 dark:focus:bg-gray-600 transition-all placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white">
                            </div>

                            <button @click="sendMessage"
                                class="p-2 text-blue-600 dark:text-blue-400 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full transition"
                                :class="newMessage.trim() === '' ? 'opacity-50 cursor-not-allowed' : ''">
                                <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
            <template x-if="!selectedContact">
                <div
                    class="flex-1 flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 h-full text-center p-6">
                    <div
                        class="w-24 h-24 bg-blue-100 dark:bg-blue-900/30 text-blue-500 dark:text-blue-400 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                            </path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Tus Mensajes</h2>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md">Selecciona un chat de la lista izquierda para
                        iniciar o continuar una conversación privada y segura.</p>
                </div>
            </template>
        </div>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 5px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #e1e1e1;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #d0d0d0;
            background-clip: content-box;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #4b5563;
        }

        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none !important;
        }

        .no-scrollbar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
    </style>
</x-app-layout>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('chatComponent', () => ({
            selectedContact: null,
            contacts: @json($contactsData),
            filter: 'todos',
            messages: [],
            newMessage: '',
            isLoading: false,
            currentEchoChannel: null,
            init() {
                if (window.Echo) {
                    window.Echo.private('App.Models.Usuario.' + {{ auth()->id() ?? 'null' }})
                        .listen('.MessageSent', (e) => {
                            if (!this.selectedContact || this.selectedContact.id != e
                                .sender_id) {
                                let contactIndex = this.contacts.findIndex(c => c.id == e
                                    .sender_id);
                                if (contactIndex !== -1) {
                                    let contact = this.contacts[contactIndex];
                                    contact.lastMessage = e.body;
                                    contact.time = e.time;
                                    contact.unreadCount += 1;

                                    this.contacts.splice(contactIndex, 1);
                                    this.contacts.unshift(contact);
                                }
                            }
                        });
                }

                setInterval(() => {
                    if (this.selectedContact) {
                        axios.post('/mensajes/ping', {
                            chat_activo_user_id: this.selectedContact.id
                        }).catch(() => {});
                    }
                }, 30000);
            },

            selectContact(contact) {
                this.selectedContact = contact;
                contact.unreadCount = 0;
                this.messages = [];
                this.fetchMessages();
            },

            fetchMessages() {
                this.isLoading = true;

                if (this.currentEchoChannel) {
                    window.Echo.leave(this.currentEchoChannel);
                }
                axios.post('/mensajes/ping', {
                    chat_activo_user_id: this.selectedContact.id
                }).catch(() => {});
                axios.get(`/mensajes/${this.selectedContact.id}`)
                    .then(response => {
                        this.messages = response.data.messages;
                        const convId = response.data.conversation_id;
                        this.scrollToBottom();
                        this.currentEchoChannel = 'chat.' + convId;
                        if (window.Echo) {
                            window.Echo.private(this.currentEchoChannel)
                                .listen('.MessageSent', (e) => {
                                    if (e.sender_id != {{ auth()->id() ?? 'null' }}) {
                                        this.messages.push({
                                            id: e.id,
                                            body: e.body,
                                            is_mine: false,
                                            time: e.time
                                        });
                                        this.scrollToBottom();

                                        let contactIndex = this.contacts.findIndex(c => c
                                            .id === this.selectedContact.id);
                                        if (contactIndex !== -1) {
                                            let contact = this.contacts[contactIndex];
                                            contact.lastMessage = e.body;
                                            contact.time = e.time;
                                            contact.unread = true;

                                            this.contacts.splice(contactIndex, 1);
                                            this.contacts.unshift(contact);
                                        }
                                    }
                                });
                        }
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            },

            sendMessage() {
                if (!this.newMessage.trim() || !this.selectedContact) return;

                let text = this.newMessage;
                this.newMessage = '';

                axios.post(`/mensajes/${this.selectedContact.id}`, {
                        body: text
                    })
                    .then(response => {
                        this.messages.push(response.data);
                        this.messages = [...this.messages];
                        this.scrollToBottom();

                        let contactIndex = this.contacts.findIndex(c => c.id === this
                            .selectedContact.id);
                        if (contactIndex !== -1) {
                            let contact = this.contacts[contactIndex];
                            contact.lastMessage = text;
                            contact.time = 'Ahora';

                            this.contacts.splice(contactIndex, 1);
                            this.contacts.unshift(contact);
                        }
                    });
            },

            scrollToBottom() {
                this.$nextTick(() => {
                    const container = document.getElementById('messages-container');
                    if (container) {
                        container.scrollTop = container.scrollHeight;
                    }
                });
            }
        }));
    });
</script>
