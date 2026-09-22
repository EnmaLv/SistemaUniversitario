<div
    x-show="isChatOpen"
    x-transition:enter="transition ease-out duration-300 transform"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200 transform"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    style="background-color: var(--bg-card); border-color: var(--border-color); display: none;"
    class="fixed left-0 top-0 h-[100dvh] w-full sm:w-[400px] shadow-2xl z-50 flex flex-col border-r transition-all duration-300"
    x-data="{
        view: 'list',
        selectedContact: null,
        contacts: [],
        messages: [],
        newMessage: '',
        isLoading: false,
        currentEchoChannel: null,

        init() {
            this.fetchContacts();

            if (window.Echo) {
                window.Echo.private('App.Models.Usuario.' + {{ auth()->id() ?? 'null' }})
                    .listen('MessageSent', (e) => {
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
                            .listen('MessageSent', (e) => {
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

        scrollToBottom() {
            this.$nextTick(() => {
                const container = document.getElementById('sidebar-messages-container');
                if (container) container.scrollTop = container.scrollHeight;
            });
        }
    }"
>
    <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="px-5 py-4 border-b flex items-center justify-between sticky top-0 z-10 shadow-sm">
        <div class="flex items-center gap-3">
            <template x-if="view === 'chat'">
                <button @click="view = 'list'" class="p-1.5 hover:bg-gray-50/60 dark:hover:bg-white/[0.02] rounded-full transition text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                </button>
            </template>
            <h3 class="font-extrabold text-xl tracking-tight" style="color: var(--text-main);" x-text="view === 'list' ? 'Mensajes' : selectedContact.name">Mensajes</h3>
        </div>
        <div class="flex items-center gap-1">
            @if(auth()->check() && !auth()->user()->tieneRol('paciente'))
                <a href="#" title="Ver mensajería completa" class="hidden sm:inline-flex p-2 hover:bg-sky-50 dark:hover:bg-sky-900/50 rounded-full transition text-gray-400 dark:text-gray-500 hover:text-sky-600 dark:hover:text-sky-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                </a>
            @endif

            <button @click="isChatOpen = false" class="p-1.5 hover:bg-rose-50 dark:hover:bg-rose-950/50 rounded-full transition text-gray-400 dark:text-gray-500 hover:text-rose-500 dark:hover:text-rose-400 ml-1">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto no-scrollbar" style="background-color: var(--bg-card);">
        <div x-show="view === 'list'" class="h-full flex flex-col">
            <div class="px-4 py-4">
                <div class="relative group">
                    <input type="text" placeholder="Buscar pacientes..." style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);" class="w-full pl-11 pr-4 py-2.5 rounded-xl border text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all placeholder-gray-500 dark:placeholder-gray-400">
                    <svg class="w-5 h-5 absolute left-3.5 top-2.5 text-gray-400 dark:text-gray-500 group-focus-within:text-sky-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-2 space-y-0.5 no-scrollbar">
                <div class="px-3 py-1.5 text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Recientes</div>
                <template x-for="contact in contacts" :key="contact.id">
                    <div
                        @click="selectContact(contact)"
                        class="flex items-center gap-3 p-3 rounded-2xl cursor-pointer transition-colors select-none hover:bg-gray-50/60 dark:hover:bg-white/[0.02] border border-transparent"
                    >
                        <div class="relative shrink-0">
                            <template x-if="contact.profile_photo">
                                <div class="w-12 h-12 rounded-full overflow-hidden bg-gradient-to-tr from-sky-500 to-indigo-500 shadow-sm group-hover:scale-105 transition transform">
                                    <img :src="contact.profile_photo" alt="Foto de perfil" class="w-full h-full object-cover">
                                </div>
                            </template>
                            <template x-if="!contact.profile_photo">
                                <div class="w-12 h-12 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shadow-sm group-hover:scale-105 transition transform" x-text="contact.avatar"></div>
                            </template>
                        </div>
                        <div class="flex-1 min-w-0 pr-2">
                            <div class="flex justify-between items-baseline mb-0.5">
                                <h4 class="font-bold text-[14px] truncate" style="color: var(--text-main);" x-text="contact.name"></h4>
                                <div x-show="contact.unreadCount > 0" class="w-2.5 h-2.5 bg-sky-500 rounded-full shrink-0"></div>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-[12px] truncate font-medium" :class="contact.unreadCount > 0 ? 'text-gray-900 dark:text-gray-200' : 'text-gray-500 dark:text-gray-400'" x-text="contact.lastMessage"></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="view === 'chat'" class="h-full flex flex-col bg-gray-50 dark:bg-[#0f1115]" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-4">
            <div id="sidebar-messages-container" class="flex-1 p-4 md:p-6 flex flex-col gap-4 overflow-y-auto no-scrollbar" style="scroll-behavior: smooth;">
                <div x-show="isLoading" class="text-center text-gray-400 dark:text-gray-500 text-xs font-medium py-4 uppercase tracking-wider">Cargando...</div>
                <template x-for="msg in messages" :key="msg.id">
                    <div>
                        <template x-if="!msg.is_mine">
                            <div class="flex items-end gap-2 group">
                                <template x-if="selectedContact && selectedContact.profile_photo">
                                    <div class="w-8 h-8 rounded-full overflow-hidden bg-gray-200 dark:bg-gray-800 shrink-0 shadow-sm">
                                        <img :src="selectedContact.profile_photo" alt="Foto de perfil" class="w-full h-full object-cover">
                                    </div>
                                </template>
                                <template x-if="!selectedContact || !selectedContact.profile_photo">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-sky-500 to-indigo-500 shrink-0 text-[10px] flex items-center justify-center font-bold text-white shadow-sm" x-text="selectedContact ? selectedContact.avatar : ''"></div>
                                </template>
                                <div class="max-w-[75%]">
                                    <div style="background-color: var(--bg-card); border-color: var(--border-color); color: var(--text-main);" class="p-3.5 border rounded-2xl rounded-bl-none text-[14px] font-medium leading-relaxed shadow-sm flex flex-col">
                                        <span x-text="msg.body"></span>
                                        <span class="text-[9px] text-gray-400 dark:text-gray-500 self-start mt-1 font-bold tracking-wider" x-text="msg.time"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <template x-if="msg.is_mine">
                            <div class="flex flex-col items-end gap-1">
                                <div class="max-w-[75%]">
                                    <div class="p-3.5 bg-sky-600 text-white rounded-2xl rounded-br-none text-[14px] font-medium leading-relaxed shadow-sm shadow-sky-200/50 dark:shadow-sky-900/20 flex flex-col">
                                        <span x-text="msg.body"></span>
                                        <span class="text-[9px] text-sky-200 self-end mt-1 font-bold tracking-wider" x-text="msg.time"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <div style="background-color: var(--bg-card); border-color: var(--border-color);" class="p-4 border-t shrink-0">
                <div class="flex items-center gap-2">
                    <div class="flex-1 relative">
                        <input type="text" x-model="newMessage" @keydown.enter="sendMessage" placeholder="Escribe un mensaje..." style="background-color: rgba(0,0,0,0.02); border-color: var(--border-color); color: var(--text-main);" class="w-full rounded-xl border py-2.5 px-4 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-sky-500 transition-all placeholder-gray-500 dark:placeholder-gray-400">
                    </div>
                    <button @click="sendMessage" class="p-2.5 text-sky-600 dark:text-sky-400 hover:bg-sky-50 dark:hover:bg-sky-900/30 rounded-xl transition-all active:scale-95" :class="newMessage.trim() === '' ? 'opacity-50 cursor-not-allowed' : 'shadow-sm'">
                        <svg class="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
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