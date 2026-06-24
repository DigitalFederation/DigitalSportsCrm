{{--  resources/views/components/forms/tinymce-editor-static.blade.php  --}}
@props([
    'name' => null, // For <textarea name="...">, for plain form submission
    'value' => '',  // Initial HTML content
    'elementId' => 'tinymce-st-' . Illuminate\Support\Str::uuid()->toString(),
])

@php
    // Decode HTML entities from the passed value for correct initial display
    $initialContent = html_entity_decode($value ?? '', ENT_QUOTES, 'UTF-8');
@endphp

<div
    {{-- wire:key and wire:ignore are less relevant here but harmless if component is ever nested in LW view by mistake --}}
    wire:key="{{ $elementId }}"
    wire:ignore
    x-data='tinyMceEditor({
        id: {!! json_encode($elementId) !!},
        isLivewire: false,
        entangled: null, // Not used in static context
        initial: {!! json_encode($initialContent) !!}, // Initial content for the editor
        inputName: {!! json_encode($name) !!} // Name for the underlying textarea for form submission
    })'
>
    {{-- This textarea will hold the content for form submission in non-Livewire forms --}}
    <textarea
        x-ref="tinymce"
        id="{{ $elementId }}"
        {{-- Do not set name attribute here directly; Alpine will set it if inputName is provided --}}
        {{-- Do not set value here; TinyMCE will populate it, and Alpine will sync if needed --}}
        {{ $attributes->except(['name', 'value', 'elementId']) }} {{-- Pass through other attributes like class --}}
        class="hidden"
    >{{ $initialContent }}</textarea> {{-- Initial content for no-JS scenarios or SSR --}}
</div>

{{-- Alpine factory - this will be included once per page load, even if multiple editors exist --}}
@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tinyMceEditor', (cfg) => ({
        /* Alpine state */
        value : cfg.isLivewire ? cfg.entangled : cfg.initial,
        editor: null,

        /* ----------  Alpine lifecycle  ---------- */
        init() {
            this.$nextTick(() => {
                this.mountTiny(cfg);
            });

            if (!cfg.isLivewire) {
                this.$watch('value', v => {
                    if (this.$refs.tinymce) {
                        this.$refs.tinymce.value = v;
                    }
                });
            }

            this.$el.addEventListener('alpine:clean', () => this.destroyTiny(cfg.id));
        },

        /* ----------  TinyMCE helpers  ---------- */
        mountTiny({ id, isLivewire, inputName }) {
            if (!window.tinymce) {
                console.error('TinyMCE script not loaded.');
                return;
            }

            const targetElement = this.$refs.tinymce;
            if (!targetElement) {
                console.error(`TinyMCE target element (textarea with x-ref="tinymce") not found for id: ${id}.`);
                return;
            }

            if (!isLivewire && inputName) {
                targetElement.name = inputName;
            }

            tinymce.init({
                target:     targetElement,
                base_url:   "{{ asset('vendor/tinymce') }}",
                height:     300,
                menubar:    false,
                statusbar:  false,
                plugins:    'advlist autolink lists link code wordcount',
                toolbar:    'undo redo | blocks | bold italic forecolor | bullist numlist | removeformat | link | code',
                skin:       false,
                content_css:false,
                content_style:
                    'body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px}',

                setup: (ed) => {
                    this.editor = ed;

                    ed.on('init', () => {
                        ed.setContent(this.value || '');
                    });

                    ed.on('blur', () => { this.value = ed.getContent(); });
                    ed.on('change', () => { this.value = ed.getContent(); });
                },
            });

            if (isLivewire) {
                this.$watch('value', (newValue) => {
                    if (this.editor && this.editor.initialized && newValue !== this.editor.getContent()) {
                        this.editor.setContent(newValue || '');
                    }
                });
            }
        },

        destroyTiny(id) {
            if (this.editor) {
                tinymce.get(this.editor.id)?.remove();
                this.editor = null;
            } else if (id) {
                tinymce.get(id)?.remove();
            }
        },
    }));
});
</script>
@endonce
