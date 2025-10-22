<div class="mx-auto max-w-6xl space-y-12 px-4 py-10 sm:px-6 lg:px-0">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('Settings') }}</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Manage your preferences and profile information.') }}</p>
    </div>

    {{-- Tabs Navigation --}}
    <div class="border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
            <button 
                wire:click="$set('activeTab', 'profile')"
                @class([
                    'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors',
                    'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500' => $activeTab === 'profile',
                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300' => $activeTab !== 'profile',
                ])
            >
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    {{ __('settings.profile.section') }}
                </div>
            </button>

            <button 
                wire:click="$set('activeTab', 'social')"
                @class([
                    'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors',
                    'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500' => $activeTab === 'social',
                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300' => $activeTab !== 'social',
                ])
            >
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                    {{ __('settings.social.section') }}
                </div>
            </button>

            <button 
                wire:click="$set('activeTab', 'notifications')"
                @class([
                    'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors',
                    'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500' => $activeTab === 'notifications',
                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300' => $activeTab !== 'notifications',
                ])
            >
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    {{ __('settings.notifications.section') }}
                </div>
            </button>

            <button 
                wire:click="$set('activeTab', 'ai')"
                @class([
                    'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors',
                    'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500' => $activeTab === 'ai',
                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300' => $activeTab !== 'ai',
                ])
            >
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    {{ __('settings.ai.section') }}
                </div>
            </button>

            <button 
                wire:click="$set('activeTab', 'integrations')"
                @class([
                    'whitespace-nowrap border-b-2 py-4 px-1 text-sm font-medium transition-colors',
                    'border-primary-600 text-primary-600 dark:border-primary-500 dark:text-primary-500' => $activeTab === 'integrations',
                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:border-gray-600 dark:hover:text-gray-300' => $activeTab !== 'integrations',
                ])
            >
                <div class="flex items-center gap-2">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z" />
                    </svg>
                    {{ __('settings.integrations.section') }}
                </div>
            </button>
        </nav>
    </div>

    {{-- Tab Content --}}
    <x-filament::card class="overflow-hidden">
        <form wire:submit.prevent="save" class="space-y-6">
            {{-- Profile Tab --}}
            @if($activeTab === 'profile')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ __('settings.profile.section') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Update your public profile information.') }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{ $this->form->getComponent('profile.name') }}
                        {{ $this->form->getComponent('profile.username') }}
                        {{ $this->form->getComponent('profile.focus_area') }}
                        {{ $this->form->getComponent('profile.avatar_url') }}
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        {{ $this->form->getComponent('profile.bio') }}
                        {{ $this->form->getComponent('profile.is_public') }}
                    </div>
                </div>
            @endif

            {{-- Social Tab --}}
            @if($activeTab === 'social')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ __('settings.social.section') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Manage your social media links.') }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        {{ $this->form->getComponent('profile.social_links') }}
                    </div>
                </div>
            @endif

            {{-- Notifications Tab --}}
            @if($activeTab === 'notifications')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ __('settings.notifications.section') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Configure how and when you receive notifications.') }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{ $this->form->getComponent('notifications.language') }}
                        {{ $this->form->getComponent('notifications.timezone') }}
                        {{ $this->form->getComponent('notifications.reminder_time') }}
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        {{ $this->form->getComponent('notifications.channels') }}
                        {{ $this->form->getComponent('notifications.telegram_config') }}
                        {{ $this->form->getComponent('notifications.notification_types') }}
                    </div>
                </div>
            @endif

            {{-- AI Tab --}}
            @if($activeTab === 'ai')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ __('settings.ai.section') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Customize AI-powered features and content generation.') }}</p>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{ $this->form->getComponent('ai.provider') }}
                        {{ $this->form->getComponent('ai.tone') }}
                    </div>
                    
                    <div class="grid grid-cols-1 gap-6">
                        {{ $this->form->getComponent('ai.share_hashtags') }}
                    </div>
                </div>
            @endif

            {{-- Integrations Tab --}}
            @if($activeTab === 'integrations')
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">{{ __('settings.integrations.section') }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('Connect external services and tools.') }}</p>
                    </div>
                    
                    {{-- WakaTime Section --}}
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-800">
                                <img src="{{ asset('images/wakatime-white.svg') }}" alt="WakaTime" class="h-6 w-6">
                            </div>
                            <div>
                                <h4 class="text-base font-semibold text-gray-900 dark:text-white">WakaTime</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    @if($hasWakatimeKey)
                                        {{ __('API key saved. Data syncs automatically each day.') }}
                                    @else
                                        {{ __('Track your coding time automatically.') }}
                                    @endif
                                </p>
                            </div>
                            <div class="ml-auto">
                                <x-filament::badge :color="$hasWakatimeKey ? 'success' : 'gray'">
                                    {{ $hasWakatimeKey ? __('Enabled') : __('Inactive') }}
                                </x-filament::badge>
                            </div>
                        </div>

                        @if($hasWakatimeKey && ($profile?->wakatime_settings['last_synced_at'] ?? false))
                            <p class="text-xs text-gray-500 dark:text-gray-500">
                                {{ __('Last sync:') }}
                                {{ \Illuminate\Support\Carbon::parse($profile->wakatime_settings['last_synced_at'])->diffForHumans() }}
                            </p>
                        @endif

                        @if($hasWakatimeKey && ($profile?->wakatime_settings['last_error'] ?? false))
                            <div class="rounded-md bg-rose-50 dark:bg-rose-900/20 p-3">
                                <p class="text-sm text-rose-600 dark:text-rose-400">
                                    {{ __('Last error: :message', ['message' => $profile->wakatime_settings['last_error']]) }}
                                </p>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-6 pt-4">
                            {{ $this->form->getComponent('integrations.wakatime_api_key') }}
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{ $this->form->getComponent('integrations.wakatime_hide_project_names') }}
                                @if($hasWakatimeKey)
                                    {{ $this->form->getComponent('integrations.wakatime_remove_key') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- GitHub Section --}}
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-4">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800">
                                    <svg class="h-5 w-5 text-gray-600 dark:text-gray-400" viewBox="0 0 16 16" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-.98.08-2.04 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.06.16 1.84.08 2.04.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z" />
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-base font-semibold text-gray-900 dark:text-white">GitHub</h4>
                                    @if($profile?->github_id)
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('Signed in as :username', ['username' => $profile->github_username ?? __('linked profile')]) }}
                                        </p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">
                                            {{ __('Your GitHub avatar and handle stay in sync with your profile.') }}
                                        </p>
                                    @else
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('Link your GitHub account to streamline sign-in.') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                @if($profile?->github_id)
                                    <x-filament::badge color="success">
                                        {{ __('Connected') }}
                                    </x-filament::badge>
                                @else
                                    <x-filament::button tag="a" href="{{ route('auth.github.redirect') }}" color="gray" outlined size="sm">
                                        <svg class="mr-2 h-4 w-4" fill="currentColor" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-.98.08-2.04 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.06.16 1.84.08 2.04.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z" />
                                        </svg>
                                        {{ __('Connect GitHub') }}
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Action buttons --}}
            <div class="flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700 pt-6 mt-6">
                <x-filament::button type="button" color="gray" outlined wire:click="mount">
                    {{ __('Reset') }}
                </x-filament::button>

                <x-filament::button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-50">
                    <span wire:loading.remove wire:target="save">{{ __('Save changes') }}</span>
                    <span wire:loading wire:loading.flex wire:target="save" class="flex items-center gap-2 flex-row">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('Saving...') }}
                    </span>
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</div>