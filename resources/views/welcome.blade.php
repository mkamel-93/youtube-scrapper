<!DOCTYPE html>
<html
    lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
>

<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link
        href="https://fonts.googleapis.com"
        rel="preconnect"
    >
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700&family=Tajawal:wght@400;700&display=swap"
        rel="stylesheet"
    >
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-slate-50 font-sans">
    <header
        class="bg-[#1a233a] pt-16 pb-32"
        @class([
            'text-right' => app()->getLocale() === 'ar',
            'text-left' => app()->getLocale() === 'en',
        ])
    >
        <div class="max-w-6xl mx-auto px-4">
            <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">
                {{ __('messages.pages.playlists.title') }}
            </h1>
            <p class="text-slate-400 text-lg">
                {{ __('messages.pages.playlists.sub_title') }}
            </p>
        </div>
    </header>

    <main
        class="max-w-6xl mx-auto px-4"
        x-data="playlistSearchForm"
    >
        <!-- Search Form -->
        <section class="-mt-20">
            <form
                class="bg-white rounded-2xl shadow-xl p-8"
                action="{{ route('playlists.start') }}"
                method="POST"
                @submit.prevent="submitForm"
            >
                @csrf
                <div class="flex flex-col md:flex-row gap-8">

                    <div class="w-full md:w-2/3">
                        <label class="block text-slate-500 text-sm font-bold mb-3">
                            {{ __('messages.pages.playlists.fields.search-box.title') }}
                        </label>
                        <div
                            class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 min-h-[160px] flex flex-wrap gap-2 content-start focus-within:ring-2 focus-within:ring-blue-500 transition-all cursor-text"
                            @click="$refs.categoryInput.focus()"
                        >
                            <template
                                x-for="(category, index) in selectedCategories"
                                :key="index"
                            >
                                <span
                                    class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg flex items-center gap-2 text-sm font-bold border border-blue-100"
                                >
                                    <span x-text="category"></span>
                                    <button
                                        class="hover:text-red-500 transition-colors text-lg"
                                        type="button"
                                        @click.stop="removeCategory(index)"
                                    >&times;</button>
                                </span>
                            </template>

                            <input
                                class="flex-1 bg-transparent border-none outline-none p-1 min-w-[120px] text-slate-700"
                                type="text"
                                x-ref="categoryInput"
                                x-model="categoryInput"
                                @keydown.enter.prevent="addCategory"
                                @keydown.comma.prevent="addCategory"
                                @keydown.backspace="handleBackspace"
                                placeholder="{{ __('messages.pages.playlists.fields.search-box.placeholder') }}"
                            >
                        </div>
                    </div>

                    <div class="flex flex-col gap-4 w-full md:w-1/3 justify-center">
                        <button
                            class="bg-[#d9534f] hover:bg-red-600 text-white font-bold py-4 px-6 rounded-xl transition-all flex items-center justify-center gap-3 shadow-lg shadow-red-200 disabled:opacity-50 disabled:cursor-not-allowed"
                            type="submit"
                            :disabled="isLoading || selectedCategories.length === 0"
                        >
                            <svg
                                class="animate-spin h-5 w-5 text-white mr-2"
                                x-show="isLoading"
                                x-cloak
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>
                            <span
                                x-text="isLoading ? 'Loading...' : '{{ __('messages.pages.playlists.fields.buttons.start') }}'"
                            ></span>
                        </button>

                        <button
                            class="bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 font-bold py-3 px-6 rounded-xl transition-all flex items-center justify-center gap-3"
                            id="stop-btn"
                            type="button"
                        >
                            {{ __('messages.pages.playlists.fields.buttons.stop') }}
                        </button>
                    </div>
                </div>
            </form>
        </section>

        <!-- Messages -->
        <div
            class="mt-8 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 font-medium shadow-sm"
            x-cloak
            x-show="errorMessage"
            x-text="errorMessage"
        ></div>

        <!-- No Results -->
        <div
            class="mt-8 p-8 bg-yellow-50 border border-yellow-200 rounded-xl text-center"
            x-show="hasSearched && searchedPlaylists.length === 0"
            x-cloak
        >
            <svg
                class="mx-auto h-12 w-12 text-yellow-400 mb-4"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
            </svg>
            <h3 class="text-lg font-bold text-yellow-800 mb-2">{{ __('messages.pages.playlists.empty_state.title') }}
            </h3>
            <p class="text-yellow-700">{{ __('messages.pages.playlists.empty_state.message') }}</p>
        </div>

        <section
            class="mt-16 mb-10"
            x-cloak
            x-show="searchedPlaylists.length > 0"
        >
            <div
                class="flex flex-col md:flex-row justify-between items-center gap-6 mb-8 border-b border-slate-200 pb-6">

                <div class="text-center md:text-start">
                    <h2 class="text-2xl font-bold text-slate-800">
                        {{ __('messages.pages.playlists.results.title') }}
                    </h2>
                </div>

                <nav
                    class="flex flex-wrap gap-2 justify-center md:justify-end"
                    aria-label="Tabs"
                >
                    <button
                        class="px-4 py-2 rounded-full border text-sm font-bold transition-all flex items-center gap-1"
                        @click="activeTab = null"
                        :class="activeTab === null ?
                            'bg-[#d9534f] text-white shadow-md' :
                            'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                    >
                        {{ __('All') }} (<span x-text="searchedPlaylists.length"></span>)
                    </button>

                    <template
                        x-for="category in searchedCategories"
                        :key="category"
                    >
                        <button
                            class="px-4 py-2 border rounded-full text-sm font-bold transition-all flex items-center gap-1"
                            @click="activeTab = category"
                            :class="activeTab === category ?
                                'bg-[#d9534f] text-white shadow-md' :
                                'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
                        >
                            <span x-text="category"></span>
                            <span
                                :class="activeTab === category ? 'text-white/90' : 'text-slate-400'"
                                x-text="'(' + searchedPlaylists.filter(p => p.category === category).length + ')'"
                            ></span>
                        </button>
                    </template>
                </nav>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <template
                    x-for="playlist in filteredPlaylists"
                    :key="playlist.id"
                >
                    <div
                        class="bg-white rounded-2xl shadow-md border border-slate-100 hover:shadow-lg transition-all overflow-hidden">
                        <!-- Thumbnail + Badges -->
                        <div class="relative">
                            <a
                                :href="playlist.url"
                                target="_blank"
                            >
                                <img
                                    class="w-full h-48 object-cover"
                                    :src="playlist.thumbnail"
                                    :alt="playlist.title"
                                >
                                <div class="absolute bottom-2 left-2 flex flex-wrap gap-2">
                                    <span class="bg-slate-700 text-white text-xs font-bold px-3 py-1.5 rounded-full">
                                        <span x-text="playlist.lessons_count"></span>
                                        {{ __('messages.pages.playlists.card.lessons_label') }}
                                    </span>
                                    <span
                                        class="bg-slate-700 text-white text-xs font-bold px-3 py-1.5 rounded-full"
                                        x-text="playlist.total_duration"
                                    ></span>
                                </div>
                            </a>
                        </div>

                        <div class="p-5">
                            <!-- Title -->
                            <h3
                                class="font-bold text-base text-slate-800 mb-4 line-clamp-2"
                                x-text="playlist.title"
                            ></h3>

                            <!-- Instructor -->
                            <div class="flex items-center gap-2 text-slate-500 text-sm mb-4">
                                <svg
                                    class="w-4 h-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"
                                    />
                                </svg>
                                <span x-text="playlist.instructor"></span>
                            </div>

                            <!-- Footer: Views + Category -->
                            <div class="flex justify-between items-center pt-3 border-t border-slate-100">
                                <div class="flex items-center gap-1 text-slate-400 text-xs">
                                    <svg
                                        class="w-4 h-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"
                                        />
                                    </svg>
                                    <span x-text="(playlist.views || 0).toLocaleString()"></span>
                                    {{ __('messages.pages.playlists.card.views_label') }}
                                </div>
                                <span
                                    class="bg-slate-50 text-slate-600 text-xs font-bold px-3 py-1 rounded-full border border-slate-200"
                                    x-text="playlist.category"
                                ></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </section>

    </main>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('playlistSearchForm', () => ({
                // Form State
                selectedCategories: [],
                categoryInput: '',

                // UI State
                isLoading: false,
                errorMessage: '',
                hasSearched: false,

                // Results State
                searchedCategories: [],
                searchedPlaylists: [],

                activeTab: null,

                // Category Management
                addCategory() {
                    const value = this.categoryInput.trim().replace(/,$/, '');

                    if (value && !this.selectedCategories.includes(value)) {
                        this.selectedCategories.push(value);
                    }

                    this.categoryInput = '';
                    this.errorMessage = '';
                },

                removeCategory(index) {
                    this.selectedCategories.splice(index, 1);
                },

                handleBackspace() {
                    if (this.categoryInput === '' && this.selectedCategories.length > 0) {
                        this.removeCategory(this.selectedCategories.length - 1);
                    }
                },

                get filteredPlaylists() {
                    if (!this.activeTab) return this.searchedPlaylists;
                    return this.searchedPlaylists.filter(p => p.category === this.activeTab);
                },

                // Form Submission
                async submitForm() {
                    this.isLoading = true;
                    this.searchedCategories = [];
                    this.searchedPlaylists = [];
                    this.errorMessage = '';
                    this.hasSearched = false;

                    if (this.selectedCategories.length === 0) {
                        this.errorMessage =
                            '{{ __('messages.validation.playlists.categories.required') }}';
                        this.isLoading = false;
                        return;
                    }

                    const formData = new FormData();
                    formData.append('_token', document.querySelector('input[name="_token"]').value);
                    this.selectedCategories.forEach(category => {
                        formData.append('categories[]', category);
                    });

                    try {
                        const response = await axios.post('{{ route('playlists.start') }}',
                            formData, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });

                        if (response.data.success) {
                            this.searchedPlaylists = response.data.data.playlists;
                            this.searchedCategories = response.data.data.categories;
                            // this.selectedCategories = [];
                            this.hasSearched = true;
                        }
                    } catch (error) {
                        if (error.response?.data?.errors) {
                            this.errorMessage = error.response.data.errors.categories?.[0] ||
                                'Invalid data';
                        } else {
                            this.errorMessage = 'An error occurred. Please try again.';
                        }
                        console.error('Submission error:', error);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }));
        });
    </script>
</body>

</html>
