<x-app-layout>
    <div class="fixed top-16 left-0 right-0 h-[calc(100dvh-4rem)] flex overflow-hidden transition-all duration-300"
        :class="sidebarOpen ? 'lg:left-56' : 'lg:left-16'"
        x-data="chatComponent"
        style="background-color: var(--bg-card);">

        {{-- COLUMNA IZQUIERDA: LISTA --}}
        <aside class="w-80 md:w-96 flex flex-col h-full shrink-0 border-r"
            style="background-color: var(--bg-card); border-color: var(--border-color);">

            <div class="px-5 pt-5 pb-3">
                <div class="flex items-center justify-between mb-4">
                    <h1 class="text-2xl font-black tracking-tight" style="color: var(--text-main);">
                        Chats
                    </h1>
                </div>

                <div class="relative">
                    <input type="text" placeholder="Buscar en chats..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-full border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400"
                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color); color: var(--text-main);">
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="px-5 flex gap-2 mb-3 overflow-x-auto no-scrollbar">
                <button @click="filter = 'todos'"
                    :class="filter === 'todos'
                        ? 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border-red-200 dark:border-red-800/60'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap border">
                    Todos
                </button>
                <button @click="filter = 'no_leidos'"
                    :class="filter === 'no_leidos'
                        ? 'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border-red-200 dark:border-red-800/60'
                        : 'border-transparent text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800'"
                    class="px-4 py-1.5 rounded-full text-xs font-bold transition whitespace-nowrap border">
                    No leídos
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-2 pb-3 custom-scrollbar">
                <template x-for="contact in contacts" :key="contact.id">
                    <button @click="selectContact(contact)"
                        class="w-full flex items-center gap-3 p-2.5 rounded-2xl cursor-pointer transition-all duration-150 text-left group"
                        :class="selectedContact && selectedContact.id === contact.id
                            ? 'bg-red-50 dark:bg-red-950/30'
                            : 'hover:bg-red-50/60 dark:hover:bg-red-950/20'">

                        <div class="relative shrink-0">
                            <template x-if="contact.profile_photo">
                                <div class="w-14 h-14 rounded-full overflow-hidden ring-2 shadow-sm transition-all"
                                    :class="selectedContact && selectedContact.id === contact.id
                                        ? 'ring-red-300 dark:ring-red-800'
                                        : 'ring-gray-100 dark:ring-gray-700 group-hover:ring-red-200 dark:group-hover:ring-red-800'">
                                    <img :src="contact.profile_photo" class="w-full h-full object-cover">
                                </div>
                            </template>
                            <template x-if="!contact.profile_photo">
                                <div class="w-14 h-14 rounded-full bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-base shadow-sm ring-2 transition-all group-hover:scale-105"
                                    :class="selectedContact && selectedContact.id === contact.id
                                        ? 'ring-red-300 dark:ring-red-800'
                                        : 'ring-red-200/40 dark:ring-red-900/40'"
                                    x-text="contact.avatar"></div>
                            </template>

                            <span x-show="contact.unreadCount > 0"
                                class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-red-600 rounded-full border-2 shadow-sm"
                                style="border-color: var(--bg-card);"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between gap-2 mb-0.5">
                                <h4 class="font-bold text-[15px] truncate" style="color: var(--text-main);"
                                    x-text="contact.name"></h4>
                                <span class="text-[11px] text-gray-400 font-semibold shrink-0" x-text="contact.time"></span>
                            </div>
                            <p class="text-[13px] truncate leading-snug"
                                :class="(contact.unread || contact.unreadCount > 0)
                                    ? 'font-bold text-gray-900 dark:text-gray-100'
                                    : 'font-medium text-gray-500 dark:text-gray-400'"
                                x-text="contact.lastMessage"></p>
                        </div>
                    </button>
                </template>

                <template x-if="contacts.length === 0 && !isLoading">
                    <div class="flex flex-col items-center justify-center py-16 px-6 text-center">
                        <div class="w-16 h-16 rounded-full bg-red-50 dark:bg-red-950/30 text-red-500 flex items-center justify-center mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <p class="text-sm font-bold" style="color: var(--text-main);">Sin conversaciones</p>
                    </div>
                </template>
            </div>
        </aside>

        {{-- COLUMNA DERECHA: CHAT --}}
        <main class="flex-1 flex flex-col overflow-hidden relative"
            style="background-color: rgba(0,0,0,0.015);">

            <template x-if="selectedContact">
                <div class="flex-1 flex flex-col h-full">

                    <header class="h-16 px-5 border-b flex items-center justify-between shrink-0 backdrop-blur-sm z-10"
                        style="background-color: var(--bg-card); border-color: var(--border-color);">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative w-10 h-10 shrink-0">
                                <template x-if="selectedContact.profile_photo">
                                    <div class="w-10 h-10 rounded-full overflow-hidden ring-2 ring-red-100 dark:ring-red-950/40">
                                        <img :src="selectedContact.profile_photo" class="w-full h-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!selectedContact.profile_photo">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-sm ring-2 ring-red-100 dark:ring-red-950/40"
                                        x-text="selectedContact.avatar"></div>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <h2 class="font-extrabold text-[15px] leading-tight truncate"
                                    style="color: var(--text-main);" x-text="selectedContact.name"></h2>
                                <p class="text-[11px] text-gray-400 font-semibold leading-tight">Conversación privada</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400 transition-colors"
                                title="Buscar en conversación">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </button>
                            <button class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400 transition-colors"
                                title="Más opciones">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 8c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm0 2c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm0 6c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z" />
                                </svg>
                            </button>
                        </div>
                    </header>

                    <div class="flex-1 overflow-y-auto px-4 md:px-8 py-6 no-scrollbar" id="messages-container">

                        <div class="flex flex-col items-center py-8 mb-4">
                            <template x-if="selectedContact.profile_photo">
                                <div class="w-20 h-20 rounded-full overflow-hidden bg-red-100 dark:bg-red-950/30 shadow-xl ring-4 ring-red-50 dark:ring-red-950/20">
                                    <img :src="selectedContact.profile_photo" class="w-full h-full object-cover">
                                </div>
                            </template>
                            <template x-if="!selectedContact.profile_photo">
                                <div class="w-20 h-20 rounded-full bg-gradient-to-br from-red-500 to-red-700 flex items-center justify-center text-white text-2xl font-black mb-3 shadow-xl ring-4 ring-red-50 dark:ring-red-950/20"
                                    x-text="selectedContact.avatar"></div>
                            </template>
                            <h3 class="text-xl font-black mt-3" style="color: var(--text-main);"
                                x-text="selectedContact.name"></h3>
                            <p class="text-[13px] text-gray-500 dark:text-gray-400 mt-1 text-center max-w-xs">
                                Has iniciado una conversación privada. Los mensajes son privados y seguros.
                            </p>
                        </div>

                        <div x-show="isLoading" class="flex items-center justify-center py-4">
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full"
                                style="background-color: var(--bg-card);">
                                <div class="w-3 h-3 border-2 border-red-200 border-t-red-600 rounded-full animate-spin"></div>
                                <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Cargando</span>
                            </div>
                        </div>

                        <div class="space-y-2.5 max-w-4xl mx-auto">
                            <template x-for="msg in messages" :key="msg.id">
                                <div>
                                    {{-- Recibido --}}
                                    <template x-if="!msg.is_mine">
                                        <div class="flex items-start gap-2">
                                            <template x-if="selectedContact.profile_photo">
                                                <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 shrink-0 shadow-sm">
                                                    <img :src="selectedContact.profile_photo" class="w-full h-full object-cover">
                                                </div>
                                            </template>
                                            <template x-if="!selectedContact.profile_photo">
                                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-red-500 to-red-700 shrink-0 flex items-center justify-center text-[10px] font-black text-white shadow-sm"
                                                    x-text="selectedContact.avatar"></div>
                                            </template>

                                            <div class="flex flex-col items-start gap-1 max-w-[70%]">
                                                <div class="px-4 py-2.5 rounded-3xl rounded-bl-md text-[15px] leading-relaxed break-words"
                                                    style="background-color: var(--bg-card); border: 1px solid var(--border-color); color: var(--text-main);">
                                                    <span x-text="msg.body"></span>
                                                </div>
                                                <span class="text-[10px] text-gray-400 font-semibold ml-1" x-text="msg.time"></span>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Propio --}}
                                    <template x-if="msg.is_mine">
                                        <div class="flex flex-col items-end gap-1">
                                            <div class="max-w-[70%] px-4 py-2.5 rounded-3xl rounded-br-md text-[15px] leading-relaxed break-words bg-gradient-to-br from-red-500 to-red-700 text-white shadow-md shadow-red-500/20">
                                                <span x-text="msg.body"></span>
                                            </div>
                                            <span class="text-[10px] text-gray-400 font-semibold mr-1" x-text="msg.time"></span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Input --}}
                    <div class="px-4 md:px-8 py-4 border-t shrink-0"
                        style="background-color: var(--bg-card); border-color: var(--border-color);">
                        <div class="flex items-end gap-2 max-w-4xl mx-auto">
                            <button type="button"
                                class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors shrink-0"
                                title="Adjuntar">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
                                </svg>
                            </button>

                            <div class="flex-1 relative" @click.outside="showEmojiPicker = false">

                                <div x-show="showEmojiPicker"
                                    x-transition.opacity.duration.150ms
                                    x-cloak
                                    class="absolute bottom-full mb-2 right-0 w-[300px] rounded-2xl border shadow-xl p-3 z-50"
                                    style="background-color: var(--bg-card); border-color: var(--border-color);">
                                    <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2 px-1">Emojis frecuentes</div>
                                    <div class="grid grid-cols-8 gap-0.5 max-h-52 overflow-y-auto custom-scrollbar">
                                        <template x-for="emoji in ['😀','😁','😂','🤣','😃','😄','😅','😆','😉','😊','😋','😎','😍','😘','🥰','😗','🙂','🤗','🤩','🤔','🤨','😐','😑','😶','🙄','😏','😣','😥','😮','🤐','😯','😪','😫','🥱','😴','😌','😛','😜','🤪','😝','🤤','😒','😓','😔','😕','🙃','🤑','😲','🙁','😖','😞','😟','😤','😢','😭','😦','😧','😨','😩','🤯','😬','😰','😱','🥵','🥶','😳','😵','🥴','😠','😡','🤬','😷','🤒','🤕','🤢','🤮','🤧','😇','🥳','🥺','🤠','🤡','🤥','🤫','🤭','🧐','🤓','😈','👿','👹','👺','💀','👻','👽','🤖','💩','👍','👎','👏','🙌','🙏','💪','👌','✌️','🤞','❤️','🧡','💛','💚','💙','💜','🖤','💔','💯','🔥','✨','⭐','🌟','🎉','🎊','🎁','☀️','🌙','⚡','💧','🌸','🌺','🌻','🍀']"
                                            :key="emoji">
                                            <button type="button"
                                                @click="insertEmoji(emoji)"
                                                class="w-8 h-8 flex items-center justify-center text-lg rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors active:scale-90"
                                                x-text="emoji"></button>
                                        </template>
                                    </div>
                                </div>

                                <input type="text" x-model="newMessage" @keydown.enter="sendMessage"
                                    placeholder="Aa"
                                    class="w-full rounded-3xl border py-3 pl-4 pr-12 text-[15px] font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400"
                                    style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color); color: var(--text-main);">

                                <button type="button"
                                    @click="showEmojiPicker = !showEmojiPicker"
                                    class="absolute right-3 top-2.5 w-8 h-8 rounded-full flex items-center justify-center transition-colors"
                                    :class="showEmojiPicker ? 'text-red-500 bg-red-50 dark:bg-red-950/30' : 'text-gray-400 hover:text-red-500'"
                                    title="Emojis">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </button>
                            </div>

                            <button @click="sendMessage"
                                class="w-11 h-11 rounded-full flex items-center justify-center transition-all active:scale-95 shrink-0"
                                :class="newMessage.trim() === ''
                                    ? 'bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed'
                                    : 'bg-gradient-to-br from-red-500 to-red-700 text-white shadow-md shadow-red-500/30 hover:shadow-lg'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!selectedContact">
                <div class="flex-1 flex flex-col items-center justify-center text-center p-6"
                    style="background-color: rgba(0,0,0,0.015);">

                    <div class="w-24 h-24 rounded-full flex items-center justify-center mb-6 bg-gradient-to-br from-red-50 to-red-100 dark:from-red-950/40 dark:to-red-900/20 text-red-500 shadow-lg ring-4 ring-red-100/50 dark:ring-red-950/20">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>

                    <h2 class="text-2xl font-black tracking-tight mb-2" style="color: var(--text-main);">
                        Tus mensajes
                    </h2>
                    <p class="text-sm max-w-md text-gray-500 dark:text-gray-400 leading-relaxed">
                        Selecciona una conversación de la lista para chatear. Todos tus mensajes son privados y seguros.
                    </p>

                    <div class="mt-8 flex items-center gap-6 text-xs text-gray-400 font-semibold">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Cifrado seguro
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Solo tú y tu contacto
                        </div>
                    </div>
                </div>
            </template>
        </main>
    </div>

    <style>
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
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
        .dark .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6b7280; }
        .no-scrollbar::-webkit-scrollbar { display: none !important; }
        .no-scrollbar { -ms-overflow-style: none !important; scrollbar-width: none !important; }
        [x-cloak] { display: none !important; }
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
            showEmojiPicker: false,

            init() {
                if (window.Echo) {
                    window.Echo.private('App.Models.Usuario.' + {{ auth()->id() ?? 'null' }})
                        .listen('.MessageSent', (e) => {
                            if (!this.selectedContact || this.selectedContact.id != e.sender_id) {
                                let contactIndex = this.contacts.findIndex(c => c.id == e.sender_id);
                                if (contactIndex !== -1) {
                                    let contact = this.contacts[contactIndex];
                                    contact.lastMessage = e.body;
                                    contact.time = e.time;
                                    contact.unreadCount += 1;

                                    this.contacts.splice(contactIndex, 1);
                                    this.contacts.unshift(contact);
                                }
                            }
                            if (window.recalculateChatBadge) {
                                window.recalculateChatBadge(this.contacts);
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

                if (window.recalculateChatBadge) {
                    window.recalculateChatBadge(this.contacts);
                }
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

                                        let contactIndex = this.contacts.findIndex(c => c.id === this.selectedContact.id);
                                        if (contactIndex !== -1) {
                                            let contact = this.contacts[contactIndex];
                                            contact.lastMessage = e.body;
                                            contact.time = e.time;
                                            contact.unread = true;

                                            this.contacts.splice(contactIndex, 1);
                                            this.contacts.unshift(contact);
                                        }

                                        if (window.recalculateChatBadge) {
                                            window.recalculateChatBadge(this.contacts);
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

                axios.post(`/mensajes/${this.selectedContact.id}`, { body: text })
                    .then(response => {
                        this.messages.push(response.data);
                        this.messages = [...this.messages];
                        this.scrollToBottom();

                        let contactIndex = this.contacts.findIndex(c => c.id === this.selectedContact.id);
                        if (contactIndex !== -1) {
                            let contact = this.contacts[contactIndex];
                            contact.lastMessage = text;
                            contact.time = 'Ahora';

                            this.contacts.splice(contactIndex, 1);
                            this.contacts.unshift(contact);
                        }
                    });
            },

            insertEmoji(emoji) {
                this.newMessage += emoji;
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