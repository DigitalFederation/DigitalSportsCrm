@props(['user', 'sendRoute'])

<div class="card">
    <section>
        <h3 class="text-xl leading-snug text-slate-800 dark:text-slate-100 font-bold">
            {{ __('notifications.welcome_email.title') }}
        </h3>

        <div class="mt-3">
            @if($user)
                <div class="flex items-center text-sm mb-3">
                    <span class="text-gray-600 dark:text-gray-400">{{ __('notifications.welcome_email.user_email') }}:</span>
                    <span class="ml-2 font-medium">{{ $user->email }}</span>
                </div>

                @if($user->welcome_email_sent_at)
                    <div class="flex items-center text-sm mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                            {{ __('notifications.welcome_email.sent_status') }}
                        </span>
                        <span class="ml-2 text-gray-500 dark:text-gray-400">
                            {{ $user->welcome_email_sent_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                @else
                    <div class="flex items-center text-sm mb-3">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100">
                            {{ __('notifications.welcome_email.not_sent_status') }}
                        </span>
                    </div>
                @endif

                <form action="{{ $sendRoute }}" method="POST" class="mt-4">
                    @csrf
                    <button type="submit"
                        class="btn bg-indigo-500 hover:bg-indigo-600 text-white w-full"
                        onclick="return confirm('{{ __('notifications.welcome_email.confirm_send') }}')">
                        @if($user->welcome_email_sent_at)
                            {{ __('notifications.welcome_email.resend_button') }}
                        @else
                            {{ __('notifications.welcome_email.send_button') }}
                        @endif
                    </button>
                </form>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    {{ __('notifications.welcome_email.description') }}
                </p>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('notifications.welcome_email.no_user') }}
                </p>
            @endif
        </div>
    </section>
</div>
