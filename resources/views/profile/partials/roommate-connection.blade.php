<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('messages.roommate_connection') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.roommate_connection_desc') }}
        </p>
    </header>

    {{-- ── Already linked ──────────────────────────────────────────────── --}}
    @if($user->roommate)
        <div class="mt-6 flex items-center justify-between gap-4 p-4 rounded-xl
                    bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-900">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <p class="text-xs uppercase tracking-wider font-semibold text-indigo-500 dark:text-indigo-400">
                        {{ __('messages.roommate_linked_with') }}
                    </p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                        {{ $user->roommate->name }}
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ $user->roommate->email }}
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.roommate.unlink') }}"
                  onsubmit="return confirm('{{ __('messages.roommate_unlink_confirm') }}');">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="px-3 py-2 text-xs font-medium rounded-lg transition-colors
                               text-red-700 dark:text-red-300
                               bg-white dark:bg-gray-900
                               border border-red-200 dark:border-red-800
                               hover:border-red-300 dark:hover:border-red-700
                               hover:bg-red-50 dark:hover:bg-red-900/30">
                    {{ __('messages.roommate_unlink') }}
                </button>
            </form>
        </div>

    {{-- ── Not yet linked ──────────────────────────────────────────────── --}}
    @else
        <form method="POST" action="{{ route('profile.roommate.link') }}" class="mt-6 space-y-4">
            @csrf

            <div>
                <label for="roommate_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('messages.roommate_invite_label') }}
                </label>
                <input id="roommate_email" name="roommate_email" type="email" required
                       value="{{ old('roommate_email') }}"
                       placeholder="roommate@example.com"
                       class="mt-1 w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 {{ $errors->has('roommate_email') ? 'border-red-400 dark:border-red-700' : 'border-gray-300 dark:border-gray-700' }}">
                <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">
                    {{ __('messages.roommate_invite_hint') }}
                </p>
                @error('roommate_email')
                    <p class="mt-1 text-xs text-red-600 dark:text-red-400 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white rounded-lg transition-colors
                           bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
                {{ __('messages.roommate_link') }}
            </button>
        </form>
    @endif
</section>
