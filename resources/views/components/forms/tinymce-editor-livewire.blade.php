{{--  resources/views/components/forms/tinymce-editor-livewire.blade.php  --}}
@props([
    'wireModel', // wire:model attribute name (e.g., "about") - REQUIRED
    'elementId' => 'tinymce-lw-' . Illuminate\Support\Str::uuid()->toString(),
])

<div
    wire:key="{{ $elementId }}"
    wire:ignore {{-- TinyMCE mutates DOM; keep Livewire off --}}
    x-data="tinyMceEditor({
        id: '{!! addslashes($elementId) !!}',
        entangled: @entangle($wireModel)
    })"
>
    {{-- The textarea is primarily a target for TinyMCE; content is managed via Livewire/Alpine --}}
    <textarea
        x-ref="tinymce"
        id="{{ $elementId }}"
        {{ $attributes->except(['wireModel', 'elementId']) }} {{-- Pass through other attributes like class --}}
        class="hidden"
    ></textarea>
</div>

{{-- Alpine factory - this will be included once per page load, even if multiple editors exist --}}
@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('tinyMceEditor', (cfg) => ({
        /* Alpine state */
        value: cfg.entangled,
        editor: null,

        /* ----------  Alpine lifecycle  ---------- */
        init() {
            // Ensure DOM is ready and elementId is available for TinyMCE target
            this.$nextTick(() => {
                this.mountTiny(cfg);
            });

            /* destroy TinyMCE cleanly when node is removed */
            this.$el.addEventListener('alpine:clean', () => this.destroyTiny(cfg.id));
        },

        /* ----------  TinyMCE helpers  ---------- */
        mountTiny({ id }) {
            if (!window.tinymce) {
                console.error('TinyMCE script not loaded.');
                return;
            }

            const targetElement = this.$refs.tinymce;
            if (!targetElement) {
                console.error(`TinyMCE target element (textarea with x-ref=\"tinymce\") not found for id: ${id}.`);
                return;
            }

            tinymce.init({
                target:     targetElement,
                base_url:   "{{ asset('vendor/tinymce') }}",
                height:     300,
                menubar:    false,
                statusbar:  false,
                plugins:    'advlist autolink lists link code wordcount',
                toolbar:    'undo redo | blocks | bold italic forecolor | bullist numlist | removeformat | link | code',
                skin:       false, // Set to 'oxide' or your preferred skin if not using custom CSS
                content_css:false, // Set to your custom CSS path or 'default'
                content_style:
                    'body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;font-size:14px}',

                setup: (ed) => {
                    this.editor = ed;

                    /* Set initial content once editor is ready */
                    ed.on('init', () => {
                        ed.setContent(this.value || '');
                    });

                    /* Push edits back to Alpine / Livewire */
                    ed.on('blur', () => { this.value = ed.getContent(); }); // Use blur to avoid too many updates
                    ed.on('change', () => { this.value = ed.getContent(); }); // Also on change for toolbar actions
                },
            });

            /* Reflect Livewire updates inside TinyMCE */
            this.$watch('value', (newValue) => {
                if (this.editor && this.editor.initialized && newValue !== this.editor.getContent()) {
                    this.editor.setContent(newValue || '');
                }
            });
        },

        destroyTiny(id) {
            if (this.editor) {
                tinymce.get(this.editor.id)?.remove();
                this.editor = null;
            } else if (id) { // Fallback if editor instance wasn't stored on `this`
                tinymce.get(id)?.remove();
            }
        },
    }));
});
</script>
@endonce
