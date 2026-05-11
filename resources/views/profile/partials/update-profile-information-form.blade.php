<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-white">
            {{ __('messages.profile_information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.profile_information_desc') }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('messages.name')" class="dark:text-gray-300" />
            <input id="name" name="name" type="text" required autofocus autocomplete="name"
                   value="{{ old('name', $user->name) }}"
                   class="mt-1 w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('messages.email')" class="dark:text-gray-300" />
            <input id="email" name="email" type="email" required autocomplete="username"
                   value="{{ old('email', $user->email) }}"
                   class="mt-1 w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-400">
                        {{ __('messages.email_unverified') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('messages.resend_verification') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('messages.verification_link_sent') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div>
            <x-input-label for="default_currency" :value="__('messages.default_currency')" class="dark:text-gray-300" />
            <select id="default_currency" name="default_currency"
                    class="mt-1 w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700">
                @foreach(\App\Models\Expense::CURRENCIES as $currency)
                    <option value="{{ $currency }}"
                        {{ old('default_currency', $user->default_currency ?? 'JPY') === $currency ? 'selected' : '' }}>
                        {{ $currency }}
                    </option>
                @endforeach
            </select>
            <x-input-error class="mt-2" :messages="$errors->get('default_currency')" />
        </div>

        @php
            $thresholdSymbol = ['JPY'=>'¥','USD'=>'$','EUR'=>'€','GBP'=>'£','AUD'=>'A$','CAD'=>'C$','SGD'=>'S$'][$user->default_currency ?? 'JPY'] ?? '';
            $thresholdPlaceholder = in_array($user->default_currency ?? 'JPY', ['JPY']) ? '10000' : '100';
        @endphp
        <div>
            <x-input-label for="high_spend_threshold" :value="__('messages.high_spend_threshold')" class="dark:text-gray-300" />
            <div class="relative mt-1">
                <span class="absolute inset-y-0 left-3 flex items-center text-gray-400 dark:text-gray-500 text-sm font-medium pointer-events-none">
                    {{ $thresholdSymbol }}
                </span>
                <input id="high_spend_threshold" name="high_spend_threshold" type="number" step="1" min="0"
                       value="{{ old('high_spend_threshold', $user->high_spend_threshold) }}"
                       placeholder="{{ $thresholdPlaceholder }}"
                       class="w-full pl-8 pr-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 border-gray-300 dark:border-gray-700 placeholder-gray-400 dark:placeholder-gray-600">
            </div>
            <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.high_spend_threshold_hint') }}</p>
            <x-input-error class="mt-2" :messages="$errors->get('high_spend_threshold')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('messages.save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('messages.saved') }}</p>
            @endif
        </div>
    </form>
</section>
