<div
    x-show="isChatOpen"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    style="background-color: var(--bg-card); border-color: var(--border-color); display: none;"
    class="fixed left-0 top-0 h-[100dvh] w-full sm:w-[420px] shadow-2xl z-50 flex flex-col border-r transition-all duration-300"
    x-data="{
        view: 'list',
        selectedContact: null,
        contacts: [],
        messages: [],
        newMessage: '',
        isLoading: false,
        currentEchoChannel: null,
        showEmojiPicker: false,

        init() {
            this.fetchContacts();

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
        },

        fetchContacts() {
            this.isLoading = true;
            axios.get('/mensajes/contactos/lista')
                .then(response => {
                    this.contacts = response.data;
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        selectContact(contact) {
            this.selectedContact = contact;
            contact.unreadCount = 0;
            this.view = 'chat';
            this.messages = [];
            this.fetchMessages();

            if (window.recalculateChatBadge) {
                window.recalculateChatBadge(this.contacts);
            }
        },

        fetchMessages() {
            this.isLoading = true;
            if (this.currentEchoChannel) window.Echo.leave(this.currentEchoChannel);

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
                                    this.messages.push({ id: e.id, body: e.body, is_mine: false, time: e.time });
                                    this.scrollToBottom();

                                    let contactIndex = this.contacts.findIndex(c => c.id === this.selectedContact.id);
                                    if(contactIndex !== -1) {
                                        let c = this.contacts[contactIndex];
                                        c.lastMessage = e.body;
                                        c.time = e.time;
                                        c.unreadCount = 0;
                                        this.contacts.splice(contactIndex, 1);
                                        this.contacts.unshift(c);
                                    }

                                    if (window.recalculateChatBadge) {
                                        window.recalculateChatBadge(this.contacts);
                                    }
                                }
                            });
                    }
                }).finally(() => { this.isLoading = false; });
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
                    if(contactIndex !== -1) {
                        let c = this.contacts[contactIndex];
                        c.lastMessage = text;
                        c.time = 'Ahora';
                        this.contacts.splice(contactIndex, 1);
                        this.contacts.unshift(c);
                    }
                });
        },

        insertEmoji(emoji) {
            this.newMessage += emoji;
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = document.getElementById('sidebar-messages-container');
                if (container) container.scrollTop = container.scrollHeight;
            });
        }
    }"
>
    {{-- ══════════════════ HEADER ══════════════════ --}}
    <header class="px-3 py-2.5 border-b flex items-center gap-2 shrink-0 shadow-sm"
        style="background-color: var(--bg-card); border-color: var(--border-color);">

        <template x-if="view === 'chat'">
            <button @click="view = 'list'"
                class="w-9 h-9 rounded-full flex items-center justify-center text-gray-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400 transition-colors flex-shrink-0"
                title="Volver">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
        </template>

        <div class="flex-1 min-w-0">
            <template x-if="view === 'chat' && selectedContact">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="relative w-9 h-9 flex-shrink-0">
                        <template x-if="selectedContact.profile_photo">
                            <div class="w-9 h-9 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 ring-2 ring-red-100 dark:ring-red-950/40">
                                <img :src="selectedContact.profile_photo" class="w-full h-full object-cover">
                            </div>
                        </template>
                        <template x-if="!selectedContact.profile_photo">
                            <div class="w-9 h-9 rounded-full bg-red-600 text-white flex items-center justify-center font-bold text-xs ring-2 ring-red-100 dark:ring-red-950/40"
                                x-text="selectedContact.avatar"></div>
                        </template>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="text-sm font-extrabold truncate leading-tight" style="color: var(--text-main);"
                            x-text="selectedContact.name"></h3>
                        <p class="text-[11px] text-gray-400 font-semibold leading-tight">Conversación privada</p>
                    </div>
                </div>
            </template>

            <template x-if="view === 'list'">
                <div>
                    <h3 class="text-lg font-black tracking-tight leading-tight" style="color: var(--text-main);">
                        Mensajes
                    </h3>
                    <p class="text-[11px] text-gray-400 font-medium leading-tight">Conversaciones recientes</p>
                </div>
            </template>
        </div>

        <button @click="isChatOpen = false"
            class="w-9 h-9 rounded-full flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/30 dark:hover:text-red-400 transition-colors flex-shrink-0"
            title="Cerrar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </header>

    {{-- ══════════════════ BODY ══════════════════ --}}
    <div class="flex-1 overflow-hidden" style="background-color: var(--bg-card);">

        {{-- VISTA LISTA --}}
        <div x-show="view === 'list'" class="h-full flex flex-col">
            <div class="px-3 py-3">
                <div class="relative">
                    <input type="text" placeholder="Buscar en mensajes..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-full border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400"
                        style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color); color: var(--text-main);">
                    <svg class="w-4 h-4 absolute left-3.5 top-3 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-2 pb-2 no-scrollbar">
                <div class="px-3 py-1.5 text-[10px] font-black text-gray-400 uppercase tracking-wider">Recientes</div>

                <template x-for="contact in contacts" :key="contact.id">
                    <button @click="selectContact(contact)"
                        class="w-full flex items-center gap-3 p-2.5 rounded-2xl cursor-pointer transition-all duration-150 text-left hover:bg-red-50/60 dark:hover:bg-red-950/20 group">

                        <div class="relative shrink-0">
                            <template x-if="contact.profile_photo">
                                <div class="w-12 h-12 rounded-full overflow-hidden ring-1 ring-gray-100 dark:ring-gray-700 shadow-sm group-hover:ring-red-200 dark:group-hover:ring-red-800 transition-all">
                                    <img :src="contact.profile_photo" class="w-full h-full object-cover">
                                </div>
                            </template>
                            <template x-if="!contact.profile_photo">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-red-500 to-red-700 text-white flex items-center justify-center font-black text-sm shadow-sm ring-1 ring-red-200/50 dark:ring-red-900/50 group-hover:scale-105 transition-transform"
                                    x-text="contact.avatar"></div>
                            </template>

                            <span x-show="contact.unreadCount > 0"
                                class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-red-600 rounded-full border-2 shadow-sm"
                                style="border-color: var(--bg-card);"></span>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-baseline justify-between gap-2 mb-0.5">
                                <h4 class="font-bold text-sm truncate" style="color: var(--text-main);"
                                    x-text="contact.name"></h4>
                                <span class="text-[10px] text-gray-400 font-semibold shrink-0" x-text="contact.time"></span>
                            </div>
                            <p class="text-xs truncate leading-snug"
                                :class="contact.unreadCount > 0
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
                        <p class="text-xs text-gray-400 mt-1">Aún no tienes mensajes</p>
                    </div>
                </template>
            </div>
        </div>

        {{-- VISTA CHAT --}}
        <div x-show="view === 'chat'" class="h-full flex flex-col"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-4">

            <div id="sidebar-messages-container"
                class="flex-1 px-3 py-4 flex flex-col gap-2.5 overflow-y-auto custom-scrollbar"
                style="background-color: rgba(0,0,0,0.015); scroll-behavior: smooth;">

                <div x-show="isLoading" class="flex items-center justify-center py-6">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full"
                        style="background-color: var(--bg-card);">
                        <div class="w-3 h-3 border-2 border-red-200 border-t-red-600 rounded-full animate-spin"></div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Cargando</span>
                    </div>
                </div>

                <template x-for="msg in messages" :key="msg.id">
                    <div>
                        {{-- Recibido --}}
                        <template x-if="!msg.is_mine">
                            <div class="flex items-start gap-2">
                                <template x-if="selectedContact && selectedContact.profile_photo">
                                    <div class="w-7 h-7 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-700 shrink-0 shadow-sm">
                                        <img :src="selectedContact.profile_photo" class="w-full h-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!selectedContact || !selectedContact.profile_photo">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-red-500 to-red-700 shrink-0 flex items-center justify-center text-[10px] font-black text-white shadow-sm"
                                        x-text="selectedContact ? selectedContact.avatar : ''"></div>
                                </template>

                                {{-- Columna: burbuja + hora --}}
                                <div class="flex flex-col items-start gap-1 max-w-[80%]">
                                    <div class="px-3.5 py-2 rounded-3xl rounded-bl-md text-[13.5px] leading-relaxed break-words"
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
                                <div class="max-w-[80%] px-3.5 py-2 rounded-3xl rounded-br-md text-[13.5px] leading-relaxed break-words bg-gradient-to-br from-red-500 to-red-700 text-white shadow-sm">
                                    <span x-text="msg.body"></span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-semibold mr-1" x-text="msg.time"></span>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            {{-- Input --}}
            <div class="p-3 border-t shrink-0" style="background-color: var(--bg-card); border-color: var(--border-color);">
                <div class="flex items-end gap-2">
                    <div class="flex-1 relative" @click.outside="showEmojiPicker = false">

                        {{-- Panel de emojis --}}
                        <div x-show="showEmojiPicker"
                            x-transition.opacity.duration.150ms
                            x-cloak
                            class="absolute bottom-full mb-2 right-0 w-[280px] rounded-2xl border shadow-xl p-2.5 z-50"
                            style="background-color: var(--bg-card); border-color: var(--border-color);">
                            <div class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5 px-1">Emojis frecuentes</div>
                            <div class="grid grid-cols-8 gap-0.5 max-h-48 overflow-y-auto custom-scrollbar">
                                <template x-for="emoji in ['😀','😁','😂','🤣','😃','😄','😅','😆','😉','😊','😋','😎','😍','😘','🥰','😗','🙂','🤗','🤩','🤔','🤨','😐','😑','😶','🙄','😏','😣','😥','😮','🤐','😯','😪','😫','🥱','😴','😌','😛','😜','🤪','😝','🤤','😒','😓','😔','😕','🙃','🤑','😲','🙁','😖','😞','😟','😤','😢','😭','😦','😧','😨','😩','🤯','😬','😰','😱','🥵','🥶','😳','😵','🥴','😠','😡','🤬','😷','🤒','🤕','🤢','🤮','🤧','😇','🥳','🥺','🤠','🤡','🤥','🤫','🤭','🧐','🤓','😈','👿','👹','👺','💀','👻','👽','🤖','💩','👍','👎','👏','🙌','🙏','💪','👌','✌️','🤞','❤️','🧡','💛','💚','💙','💜','🖤','💔','💯','🔥','✨','⭐','🌟','🎉','🎊','🎁','☀️','🌙','⚡','🌸','🌺','🌻','🍀']"
                                    :key="emoji">
                                    <button type="button"
                                        @click="insertEmoji(emoji)"
                                        class="w-7 h-7 flex items-center justify-center text-base rounded-lg hover:bg-red-50 dark:hover:bg-red-950/40 transition-colors active:scale-90"
                                        x-text="emoji"></button>
                                </template>
                            </div>
                        </div>

                        <input type="text" x-model="newMessage" @keydown.enter="sendMessage"
                            placeholder="Aa"
                            class="w-full rounded-3xl border py-2.5 pl-4 pr-10 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-red-500/30 focus:border-red-500 transition-all placeholder-gray-400"
                            style="background-color: rgba(0,0,0,0.03); border-color: var(--border-color); color: var(--text-main);">

                        <button type="button"
                            @click="showEmojiPicker = !showEmojiPicker"
                            class="absolute right-2.5 top-2 w-7 h-7 rounded-full flex items-center justify-center transition-colors"
                            :class="showEmojiPicker ? 'text-red-500 bg-red-50 dark:bg-red-950/30' : 'text-gray-400 hover:text-red-500'"
                            title="Emojis">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                    </div>

                    <button @click="sendMessage"
                        class="w-10 h-10 rounded-full flex items-center justify-center transition-all active:scale-95 flex-shrink-0"
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
    </div>
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