<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-5 py-2 bg-white border border-primary-light rounded-lg font-medium text-sm text-primary tracking-wide shadow-sm hover:bg-secondary-light focus:outline-none focus:border-primary focus:ring focus:ring-primary-light/30 active:text-primary active:bg-secondary disabled:opacity-50 transition-colors duration-150']) }}>
    {{ $slot }}
</button>
