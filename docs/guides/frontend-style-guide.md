# Public Frontend Style Guide

This document outlines the styling principles and specific Tailwind CSS class patterns for public-facing pages, ensuring a consistent, clean, and professional look inspired by Stripe's design philosophy.

## Core Principles

*   **Clean & Minimal:** Prioritize clarity and readability.
*   **Whitespace:** Use generous spacing for separation and visual hierarchy.
*   **Typography:** Rely on font size, weight, and color for emphasis over excessive borders or backgrounds.
*   **Responsiveness:** Design mobile-first, ensuring usability across all screen sizes.
*   **Consistency:** Apply these patterns uniformly across all public pages.

## Global Styles

*   **Background:** Use the wave background where appropriate for visual interest.
    ```html
    <main class="relative bg-cover min-h-screen bg-waves-full-bg-one animate-in pb-16">
    ```
*   **Page Container:** Limit content width for readability on larger screens.
    ```html
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
    ```

## Header Section (e.g., Individual Profile)

Used to display primary entity information.

*   **Layout:** White card with shadow, rounded corners, internal padding, bottom margin for spacing, and a flex layout to align content.
    ```html
    <div class="bg-white shadow-lg rounded-lg p-4 sm:p-6 mb-8 flex items-center space-x-4 sm:space-x-6">
    ```
*   **Avatar/Primary Image:**
    *   Responsive Size: `h-20 w-20 sm:h-24 sm:w-24`
    *   Shape & Border: `rounded-full object-cover border-4 border-gray-200`
    *   Layout: `flex-shrink-0` (prevents shrinking in flex container)
    *   Placeholder: `asset('img/user_placeholder.png')`
    *   Example:
        ```html
        <img class="h-20 w-20 sm:h-24 sm:w-24 rounded-full object-cover border-4 border-gray-200 flex-shrink-0"
             src="{{ $individual->getFirstMediaUrl('profile', 'thumb') ?: asset('img/user_placeholder.png') }}"
             alt="{{ $individual->full_name }} profile photo">
        ```
*   **Text Content Block:** Takes remaining space, prevents overflow.
    ```html
    <div class="flex-1 min-w-0">
    ```
*   **Primary Title (e.g., Name):** Large, bold, dark text. Truncate if necessary on smaller screens (though prefer wrapping where possible).
    ```html
    <h2 class="text-xl sm:text-2xl font-bold text-gray-800 truncate">{{ $individual->full_name }}</h2>
    ```
*   **Secondary Info (e.g., Nationality, DOB):** Smaller, muted text. Use flexbox for responsive layout (stack vertically on mobile, horizontal on larger screens).
    ```html
    <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 mt-2 text-sm text-gray-600">
        <!-- Nationality Item -->
        <span class="flex items-center whitespace-nowrap">
            <img src="{{ asset('img/flags/' . $individual->country->iso . '.svg') }}" alt="{{ $individual->country->name }} flag" class="w-5 h-auto mr-1.5 sm:mr-2 rounded-sm flex-shrink-0">
            {{ $individual->country->name }}
        </span>
        <!-- DOB Item -->
        <span class="whitespace-nowrap mt-1 sm:mt-0">
            <svg><!-- Calendar Icon --></svg>
            Born: {{ Carbon\Carbon::parse($individual->birthdate)->format('d M Y') }}
        </span>
    </div>
    ```

## Section Titles (e.g., Committee Headers)

Used to group related content lists.

*   **Placement:** Positioned *outside* and *above* the content card it titles.
*   **Styling:** Strong but neutral color, standard weight, sized appropriately.
*   **Spacing:** Use bottom margin for separation from the content card below.
    ```html
    <h3 class="text-lg sm:text-xl font-semibold text-gray-800 mb-3">Section Title</h3>
    ```

## Content Cards & Lists

Used for displaying lists of items (e.g., Certifications).

*   **Card Container:** White background, shadow, rounded corners, hides overflow.
    ```html
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
    ```
*   **List:** Unordered list with dividers between items.
    ```html
    <ul class="divide-y divide-gray-200">
    ```
*   **List Item:** Flex layout, responsive padding, hover state.
    ```html
    <li class="p-3 sm:p-4 md:p-6 hover:bg-gray-50 transition ease-in-out duration-150">
        <div class="flex items-center space-x-3 sm:space-x-4">
            <!-- Item Content -->
        </div>
    </li>
    ```

## List Item Content (e.g., Certification Row)

*   **Thumbnail Image:** Responsive size, rounded, shadow.
    *   Size: `w-12 h-auto sm:w-16`
    *   Layout: `flex-shrink-0`
    *   Styling: `shadow-sm rounded`
    *   Default: `asset('img/default_certification_card.jpg')`
    *   Example:
        ```html
        <img src="{{ $imageUrl }}" alt="..." class="w-12 h-auto sm:w-16 flex-shrink-0 shadow-sm rounded">
        ```
*   **Main Text Block:** Takes remaining space, prevents overflow.
    ```html
    <div class="flex-1 min-w-0">
    ```
*   **Item Title (e.g., Certification Name):** Primary emphasis, slightly larger, distinct color. Allow wrapping.
    ```html
    <p class="text-base sm:text-lg font-semibold text-sky-800">{{ $certification->certification_name }}</p>
    ```
*   **Detail Lines (e.g., Number, Issued Date):** Smaller, muted text with emphasized labels.
    *   Label Styling: `font-semibold text-gray-600`
    *   Value Styling: `text-sm text-gray-500`
    *   Spacing: `mt-1` (if needed)
    *   Example:
        ```html
        <p class="text-sm text-gray-500 mt-1">
            <span class="font-semibold text-gray-600">National Certification Number:</span> {{ $certification->certification_id }}
        </p>
        <p class="text-sm text-gray-500">
            <span class="font-semibold text-gray-600">Issued:</span> {{ Carbon\Carbon::parse($certification->created_at)->format('d M Y') }}
             @if($certification->federation)
                by <span class="font-medium">{{ $certification->federation->name }}</span>
                @if($certification->federation->country)
                    <img src="{{ asset('img/flags/' . $certification->federation->country->iso . '.svg') }}" alt="" class="w-4 h-auto inline ml-1 rounded-sm" />
                @endif
             @endif
        </p>
        ```
*   **Status Badge:** Right-aligned, distinct pill shape with status-dependent colors.
    *   Layout: `flex-shrink-0 ml-3 sm:ml-4`
    *   Base Styling: `px-2.5 sm:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full`
    *   Conditional Colors (Example for 'active'): `bg-green-100 text-green-800`
    *   Conditional Colors (Example for 'pending'): `bg-yellow-100 text-yellow-800`
    *   (Define other states: rejected, suspended, canceled, default)
    *   Example Structure:
        ```html
        <div class="flex-shrink-0 ml-3 sm:ml-4">
             <span class="px-2.5 sm:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                @switch($certification->stateName())
                    @case('active') bg-green-100 text-green-800 @break
                    @case('pending') bg-yellow-100 text-yellow-800 @break
                    {{-- ... other cases ... --}}
                    @default bg-gray-100 text-gray-800
                @endswitch
             ">
                {{ ucfirst($certification->stateName()) }}
            </span>
        </div>
        ```

## Empty States

Provide clear feedback when no data is found.

*   **Layout:** Centered content within a padded white card.
    ```html
    <div class="bg-white shadow-md rounded-lg p-6 text-center">
    ```
*   **Icon:** Centered, muted color SVG.
    ```html
    <svg class="mx-auto h-12 w-12 text-gray-400" ...><!-- Icon Path --></svg>
    ```
*   **Text:** Clear heading and description.
    ```html
    <h3 class="mt-2 text-sm font-medium text-gray-900">No results found</h3>
    <p class="mt-1 text-sm text-gray-500">Description of why no results were found.</p>
    ```

## Buttons

*   **Primary Action / Back Button:** Solid background, clear text, rounded corners, hover state, responsive sizing.
    *   Layout: Often centered `flex justify-center mt-8`.
    *   Base Styling: `bg-[#193044] text-white py-2 px-6 rounded-md font-semibold transition ease-in-out duration-150`
    *   Responsive Text Size: `text-base sm:text-lg`
    *   Responsive Width: `w-full sm:w-auto`
    *   Hover State: `hover:bg-[#112233]`
    *   Example:
        ```html
        <a href="{{ route('public.certification.index') }}"
           class="bg-[#193044] w-full sm:w-auto text-center text-white py-2 px-6 rounded-md text-base sm:text-lg font-semibold hover:bg-[#112233] transition ease-in-out duration-150">
           Back to Search
        </a>
        ```
