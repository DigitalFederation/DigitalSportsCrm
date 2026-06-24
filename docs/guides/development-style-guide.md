---
title: Development Style Guide
description: Code style, UI patterns, and component usage guidelines
---

# Digital Sports CRM UI Style Guide for LLMs

This guide defines how to design and implement views that align with Digi‑Federation’s established system. It is optimized for LLM output: be precise, reuse existing patterns, and prefer consistency over novelty.

## Principles

- Consistency: use `<x-layout>`, standard containers, and approved classes.
- Internationalization: never hardcode strings; always use translation helpers.
- Responsiveness: design mobile‑first with Tailwind breakpoints.
- Accessibility: provide labels, focus states, and sufficient contrast.
- Reuse: prefer existing components/utilities over custom CSS.

## Layout Structure

### Basic Page Layout
```blade
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('module.page_title') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <!-- Action buttons -->
            </div>
        </div>

        <!-- Main Content -->
        <!-- Content goes here -->
    </div>
</x-layout>
```

### Page Container
- Wrap content with `<div class="previous-layout-classes">` for unified spacing.

## Cards & Containers

### Standard Card
```blade
<div class="card">
    <!-- Card content with automatic padding -->
</div>
```

### Card Without Padding
```blade
<div class="card-no-padding">
    <!-- Content without padding - useful for tables -->
</div>
```

### Information Box
```blade
<div class="information-box">
    <!-- Highlighted information content -->
</div>
```

### Panel Box
```blade
<div class="panel-box">
    <!-- Secondary container content -->
</div>
```

## Buttons

### Primary Actions
```blade
<button type="submit" class="btn btn-primary">
    {{ __('common.save') }}
</button>
```

### Secondary Actions
```blade
<a href="{{ route('route.name') }}" class="btn btn-secondary">
    {{ __('common.cancel') }}
</a>
```

### Destructive Actions
```blade
<button type="button" class="btn btn-danger">
    {{ __('common.delete') }}
</button>
```

### Success Actions
```blade
<button type="button" class="btn btn-success">
    {{ __('common.approve') }}
</button>
```

### Informational Actions
```blade
<button type="button" class="btn btn-info">
    {{ __('common.export') }}
    </button>
```

### Button Sizes
- Default: `btn btn-primary`
- Large: `btn btn-lg btn-primary`
- Small: `btn btn-sm btn-primary`
- Extra Small: `btn btn-xs btn-primary`

### Button with Icon
```blade
<button type="button" class="btn btn-primary">
    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
    </svg>
    <span class="hidden xs:block ml-2">{{ __('common.create') }}</span>
</button>
```

## Forms

### Form Container
```blade
<form method="POST" action="{{ route('route.name') }}" class="card">
    @csrf
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Form fields -->
    </div>
</form>
```

### Text Input
```blade
<div>
    <label class="block text-sm font-medium mb-1" for="field_name">
        {{ __('module.field_label') }} <span class="text-rose-500">*</span>
    </label>
    <input type="text" 
           id="field_name" 
           name="field_name" 
           class="form-input w-full @error('field_name') border-rose-300 @enderror" 
           value="{{ old('field_name', $model->field_name ?? '') }}"
           required>
    @error('field_name')
        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
    @enderror
</div>
```

### Select Dropdown
```blade
<div>
    <label class="block text-sm font-medium mb-1" for="select_field">
        {{ __('module.select_label') }}
    </label>
    <select id="select_field" 
            name="select_field" 
            class="form-select w-full @error('select_field') border-rose-300 @enderror">
        <option value="">{{ __('common.select') }}</option>
        @foreach($options as $option)
            <option value="{{ $option->id }}" {{ old('select_field') == $option->id ? 'selected' : '' }}>
                {{ $option->name }}
            </option>
        @endforeach
    </select>
    @error('select_field')
        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
    @enderror
</div>
```

### Textarea
```blade
<div>
    <label class="block text-sm font-medium mb-1" for="description">
        {{ __('module.description') }}
    </label>
    <textarea id="description" 
              name="description" 
              rows="3" 
              class="form-textarea w-full @error('description') border-rose-300 @enderror">{{ old('description', $model->description ?? '') }}</textarea>
    @error('description')
        <div class="text-xs mt-1 text-rose-500">{{ $message }}</div>
    @enderror
</div>
```

### Checkbox
```blade
<div>
    <label class="flex items-center">
        <input type="checkbox" 
               name="is_active" 
               value="1" 
               class="form-checkbox" 
               {{ old('is_active', $model->is_active ?? false) ? 'checked' : '' }}>
        <span class="text-sm ml-2">{{ __('module.is_active') }}</span>
    </label>
</div>
```

### Form Actions
```blade
<div class="flex flex-wrap justify-end space-x-2 mt-6">
    <a href="{{ route('module.index') }}" class="btn btn-secondary">
        {{ __('common.cancel') }}
    </a>
    <button type="submit" class="btn btn-primary">
        {{ __('common.save') }}
    </button>
</div>
```

## Tables

### Dynamic Table Component

`<x-dynamic-table>` takes a `:headers` list (strings, or arrays with `text`/`field`/`sortable`/`alignment` keys) and renders its default slot as the table body. There are no `:items`, `:route`, `:enable-actions`, or `:enable-search` props and no named slots — write the `<tr>` rows directly in the slot.

```blade
<x-dynamic-table :headers="[
    __('entity.code'),
    __('entity.name'),
    __('entity.location'),
    ['text' => __('entity.country'), 'field' => 'country', 'sortable' => false],
]">
    @foreach($entities as $entity)
        <tr>
            <td>{{ $entity->code }}</td>
            <td>{{ $entity->name }}</td>
            <td>{{ $entity->location }}</td>
            <td>
                @if($entity->country)
                    <img src="{{ asset('img/flags/' . strtolower($entity->country->iso) . '.svg') }}"
                         alt="{{ $entity->country->name }}"
                         class="inline-block w-6 h-6 mr-2">
                    {{ $entity->country->name }}
                @endif
            </td>
        </tr>
    @endforeach
</x-dynamic-table>
```

### Manual Table Structure
```blade
<div class="card-no-padding">
    <div class="overflow-x-auto">
        <table class="table-auto w-full divide-y divide-slate-200">
            <thead class="text-xs font-semibold uppercase text-slate-500 bg-slate-50 border-t border-slate-200">
                <tr>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('module.column_1') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-left">{{ __('module.column_2') }}</div>
                    </th>
                    <th class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-right">{{ __('common.actions') }}</div>
                    </th>
                </tr>
            </thead>
            <tbody class="text-sm divide-y divide-slate-200">
                @forelse($items as $item)
                    <tr class="table-row">
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $item->field_1 }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            {{ $item->field_2 }}
                        </td>
                        <td class="px-2 first:pl-5 last:pr-5 py-3 whitespace-nowrap">
                            <div class="space-x-1 flex justify-end items-end">
                                <x-dynamic-table-buttons type="show" :route="route('module.show', $item)" />
                                <x-dynamic-table-buttons type="edit" :route="route('module.edit', $item)" />
                                <x-dynamic-table-buttons type="delete" :route="route('module.destroy', $item)" method="DELETE" />
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-2 first:pl-5 last:pr-5 py-12 text-center">
                            <div class="text-slate-500">{{ __('common.no_results') }}</div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
```

### Table Action Buttons
Use `<x-dynamic-table-buttons>` for row actions:

```blade
<!-- Show button only -->
<div class="space-x-1 flex justify-end items-end">
    <x-dynamic-table-buttons type="show" :route="route('module.show', $item)" />
</div>

<!-- Show, Edit, and Delete buttons -->
<div class="space-x-1 flex justify-end items-end">
    <x-dynamic-table-buttons type="show" :route="route('module.show', $item)" />
    <x-dynamic-table-buttons type="edit" :route="route('module.edit', $item)" />
    <x-dynamic-table-buttons type="delete" :route="route('module.destroy', $item)" method="DELETE" />
</div>
```

Important: Wrap action groups with `space-x-1 flex justify-end items-end` for alignment.

## Typography

### Page Titles
```blade
<h1 class="page-first-title">{{ __('module.title') }}</h1>
```

### Section Headers
```blade
<h2 class="text-xl leading-snug text-slate-800 font-bold mb-5">{{ __('module.section_title') }}</h2>
```

### Subsection Headers
```blade
<h3 class="grow font-semibold text-slate-800 truncate mb-3">{{ __('module.subsection_title') }}</h3>
```

### Body Text
```blade
<p class="text-sm text-slate-600">{{ __('module.description') }}</p>
```

### Helper Text
```blade
<p class="text-gray-500 text-sm">{{ __('module.help_text') }}</p>
```

### Error Text
```blade
<div class="text-xs mt-1 text-rose-500">{{ __('module.error_message') }}</div>
```

## Color, Spacing & Icons
### Text Colors
- Primary text: `text-slate-800`
- Secondary text: `text-slate-600`
- Muted text: `text-slate-500`
- Error text: `text-rose-500`
- Success text: `text-emerald-600`

### Background Colors
- White background: `bg-white`
- Gray background: `bg-slate-50`
- Active state: `bg-active`
- Pending state: `bg-yellow-400`
- Canceled state: `bg-red-700`
- Draft state: `bg-gray-400`

### Border Colors
- Default border: `border-slate-200`
- Error border: `border-rose-300`
- Focus border: `border-indigo-500`

## Design Tokens

### Brand & Status Colors
- Brand: `primary` `#6482AD` (light: `#7FA1C3`), `secondary` `#E2DAD6` (light: `#F5EDED`).
- Status (BG/Text prefixes apply): `draft #71717a`, `pending/#waiting-director #fbbf24`, `paid/#active #10b981`, `approved #1570EF`, `canceled/#suspended #ef4444`, `admin_brown #9D915F`, `admin_blue #1b6cb3`, `admin_green #10B981`.
- Utility: Filament preset exposes `info`, `success`, `warning`, and `danger` scales.

Usage:
- Prefer semantic tokens (e.g., `bg-pending`, `text-approved`) over raw hex.
- Do not invent new tokens; request additions if needed.

### Typography
- Sans stack: `Nunito` (default) and `Inter` available via `font-sans` / `font-inter`.
- Sizes: use Tailwind scale; tiny labels may use `text-xxs` (0.65rem). Avoid `text-xxxs` unless space constrained.

### Breakpoints
- Tailwind defaults: `sm` ≥ 640px, `md` ≥ 768px, `lg` ≥ 1024px, `xl` ≥ 1280px, `2xl` ≥ 1536px.
- Author mobile‑first; add larger breakpoints only when needed.

### Spacing
- Use Tailwind spacing scale. Default density targets: container gutters `px-4`/`md:px-6`, grid gaps `gap-4`, stack `space-y-4`.
- Compact contexts (tables, filters): `gap-3` and `space-x-2`. Never reduce tap targets below 40×40px.

### Dark Mode
- Enabled via class: `dark`. Ensure text and icon contrast in both modes; avoid brand tones that fail contrast on dark backgrounds.

### Background Imagery
- Approved utilities: `bg-waves-pattern`, `bg-wave-blue(-*)`, and card waves (`bg-card-waves-*`). Use sparingly behind cards with adequate contrast and `bg-white/90` overlays when needed.

### Page‑level Spacing
- Page header margin: `mb-5 mt-5`
- Section spacing: `my-8`
- Content blocks: `mb-8`

### Component Spacing
- Form field spacing: `gap-4`
- Button group spacing: `space-x-2`
- Card padding: `md:p-6 p-4` (handled by card class)

### Text Spacing
- Label margin: `mb-1`
- Helper text margin: `mt-1`
- Paragraph margin: `mb-4`

### SVG Icon Pattern
```blade
<svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
    <path d="...svg path data..." />
</svg>
```

### Using Icon Components
```blade
<x-svg.info class="h-5 w-5 text-slate-500" />
```

### Country Flags
```blade
<img src="{{ asset('img/flags/' . strtolower($country->iso) . '.svg') }}" 
     alt="{{ $country->name }}" 
     class="inline-block w-6 h-6">
```

## Internationalization

Never hardcode strings. Use translation helpers:

```blade
<!-- Simple translation -->
{{ __('module.key') }}

<!-- Translation with parameters -->
{{ __('module.message', ['name' => $user->name]) }}

<!-- Plural translation -->
{{ trans_choice('module.items', $count) }}

<!-- Check if translation exists -->
@if(__('module.optional_key') !== 'module.optional_key')
    {{ __('module.optional_key') }}
@endif
```

### Translation File Organization
- Common strings: `lang/{locale}/common.php`
- Module-specific: `lang/{locale}/{module}.php`
- Validation messages: `lang/{locale}/validation.php`

### Required Common Translations
```php
// lang/en/common.php
return [
    'create' => 'Create',
    'edit' => 'Edit',
    'delete' => 'Delete',
    'save' => 'Save',
    'cancel' => 'Cancel',
    'back' => 'Back',
    'search' => 'Search',
    'filter' => 'Filter',
    'clear' => 'Clear',
    'yes' => 'Yes',
    'no' => 'No',
    'active' => 'Active',
    'inactive' => 'Inactive',
    'status' => 'Status',
    'actions' => 'Actions',
    'no_results' => 'No results found',
    'confirm_delete' => 'Are you sure you want to delete this item?',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'select' => 'Select...',
    'all' => 'All',
];
```

## Common Patterns

### Page with Filters and Table
```blade
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="sm:flex sm:justify-between sm:items-center mb-5 mt-5">
            <div class="mb-4 sm:mb-0">
                <h1 class="page-first-title">{{ __('module.title') }}</h1>
            </div>
            <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
                <a href="{{ route('module.create') }}" class="btn btn-primary">
                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0" viewBox="0 0 16 16">
                        <path d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                    </svg>
                    <span class="hidden xs:block ml-2">{{ __('module.create') }}</span>
                </a>
            </div>
        </div>

        <!-- Filters -->
        <x-filter-form :post="route('module.index')">
            <!-- Filter fields -->
        </x-filter-form>

        <!-- Table -->
        <x-dynamic-table :headers="$headers">
            <!-- Render <tr> rows here -->
        </x-dynamic-table>
    </div>
</x-layout>
```

### Create/Edit Form Page
```blade
<x-layout>
    <div class="previous-layout-classes">
        <!-- Page Header -->
        <div class="mb-5 mt-5">
            <h1 class="page-first-title">
                {{ isset($model) ? __('module.edit_title') : __('module.create_title') }}
            </h1>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ isset($model) ? route('module.update', $model) : route('module.store') }}" class="card">
            @csrf
            @if(isset($model))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Form fields -->
            </div>

            <div class="flex flex-wrap justify-end space-x-2 mt-6">
                <a href="{{ route('module.index') }}" class="btn btn-secondary">
                    {{ __('common.cancel') }}
                </a>
                <button type="submit" class="btn btn-primary">
                    {{ __('common.save') }}
                </button>
            </div>
        </form>
    </div>
</x-layout>
```

### Statistics Cards

`<x-utility.card-total>` takes `title` and `:count`. Note: this component is currently disabled (its markup is commented out, so it renders nothing) — re-enable it in `resources/views/components/utility/card-total.blade.php` before relying on it.

```blade
<div class="grid grid-cols-12 gap-6 mb-8">
    <div class="col-span-full sm:col-span-6 xl:col-span-3">
        <x-utility.card-total :title="__('module.total_items')" :count="$totalItems" />
    </div>
</div>
```

## UX & Interaction Guidelines

### States & Feedback
- Hover/Focus/Active: ensure visible affordances. Use Tailwind focus rings and maintain WCAG AA contrast.
- Loading: use skeletons or `aria-busy` with spinners in buttons. Disable actions while pending.
- Success/Warning/Error: prefer semantic tokens (`text-success`, `bg-warning-100`) and inline feedback near the control.

### Forms & Validation
- Labels are required; associate `for`/`id`. Use `@error` blocks directly beneath inputs.
- Helpers go under labels; errors replace helpers when present.
- Required: show red asterisk in label; validate client and server side.
- Keyboard: logical tab order, `autofocus` only on create forms, Esc closes modals.

### Empty & Error States
- Empty: concise headline, 1‑2 sentence explanation, and a primary action (e.g., “Create …”).
- Errors: actionable copy, avoid blame, provide retry and support guidance. For tables, show full‑width row message.

### Tables
- Density: default row padding; never reduce icon touch targets. Align numeric columns right; text left.
- Sorting/Filters: persist selection; show active filter chips; provide clear reset.
- Actions: place on the far right, grouped, with consistent order Show → Edit → Delete.

### Accessibility
- Forms: visible labels, descriptive placeholders are optional. Include `aria-invalid` when errors present.
- Icons: provide `aria-label` or visible text; decorative SVGs should be hidden from AT.
- Color: never communicate state by color alone; include text or icons.

## Standards & Anti‑Patterns

### DO
1. Use existing component classes (`card`, `btn btn-primary`, etc.).
2. Translate all strings using `__()` (or `trans_choice`).
3. Wrap every page with `<x-layout>` and `previous-layout-classes` container.
4. Apply responsive classes (`sm:`, `md:`, `lg:`) thoughtfully.
5. Handle empty states and show validation errors near fields.
6. Use `old()` for form values; include `@csrf` (and method spoofing when needed).

### DON’T
1. Create custom CSS classes or inline styles.
2. Hardcode copy, currency, or dates.
3. Mix button variants within a single action group.
4. Introduce new color tokens or layout patterns.
5. Omit focus states or accessible labels.

## Quick Reference

### Essential Classes
- Layout wrapper: `<x-layout>`
- Content container: `previous-layout-classes`
- Cards: `card`, `card-no-padding`, `information-box`, `panel-box`
- Buttons: `btn btn-{primary|secondary|danger|success|warning}`
- Forms: `form-input`, `form-select`, `form-textarea`, `form-checkbox`
- Tables: `<x-dynamic-table>` component
- Page title: `page-first-title`

### Essential Translations
Always ensure these keys exist in your translation files:
- `common.create`, `common.edit`, `common.delete`
- `common.save`, `common.cancel`, `common.back`
- `common.yes`, `common.no`
- `common.active`, `common.inactive`
- `common.status`, `common.actions`
- `common.no_results`
- `common.confirm_delete`

### Review Checklist
Before submitting a view:
- [ ] `<x-layout>` + `previous-layout-classes` used
- [ ] Title uses `page-first-title`
- [ ] All strings translated (including buttons, empty states)
- [ ] Correct button variant and size; consistent grouping
- [ ] Responsive layout verified on small and large screens
- [ ] Validation, errors, and empty states handled
- [ ] No custom CSS or inline styles introduced

Use this guide whenever creating or modifying views to ensure brand consistency, accessibility, and maintainability across Digi‑Federation.
