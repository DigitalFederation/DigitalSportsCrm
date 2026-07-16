{{-- Step 8: Budget - Expenses, Revenue, Balance (Sections 11 + 12 + 13) --}}
<div x-data="{
    calcTotal(items) {
        let total = 0;
        for (const key in items) {
            if (items[key] && items[key].qty && items[key].value) {
                total += Number(items[key].qty) * Number(items[key].value);
            }
        }
        return total;
    },
    formatCurrency(val) {
        return new Intl.NumberFormat('{{ str_replace('_', '-', app()->getLocale()) }}', { style: 'currency', currency: @js(currency_code()) }).format(val || 0);
    }
}">

{{-- EXPENSES --}}
<div class="mb-8">
    <h4 class="text-base font-semibold text-slate-700 mb-4">{{ __('event_applications.wizard.sections.expenses') }}</h4>

    @php
        $expenseGroups = [
            'infrastructure' => [
                'title' => 'event_applications.wizard.expense_groups.infrastructure',
                'items' => ['installations', 'licenses', 'audiovisual', 'other'],
            ],
            'human_resources' => [
                'title' => 'event_applications.wizard.expense_groups.human_resources',
                'items' => ['technical_delegate', 'technical_officials', 'chief_technical_officials', 'event_director', 'safety_emergency_manager', 'specialized_technicians', 'other'],
            ],
            'travel' => [
                'title' => 'event_applications.wizard.expense_groups.travel',
                'items' => ['fuel', 'tolls', 'other'],
            ],
            'prizes' => [
                'title' => 'event_applications.wizard.expense_groups.prizes',
                'items' => ['medals', 'trophies', 'diplomas', 'other'],
            ],
            'accommodation_food' => [
                'title' => 'event_applications.wizard.expense_groups.accommodation_food',
                'items' => ['food', 'accommodation'],
            ],
            'other_expenses' => [
                'title' => 'event_applications.wizard.expense_groups.other_expenses',
                'items' => ['consumables', 'merchandise', 'streaming', 'promotion_plan'],
            ],
        ];
    @endphp

    @foreach($expenseGroups as $groupKey => $group)
        <div class="mb-4">
            <h5 class="text-sm font-medium text-slate-600 mb-2">{{ __($group['title']) }}</h5>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 pr-4 font-medium text-gray-500">{{ __('event_applications.wizard.labels.item') }}</th>
                            <th class="text-center py-2 px-2 font-medium text-gray-500 w-24">{{ __('event_applications.wizard.labels.quantity') }}</th>
                            <th class="text-center py-2 px-2 font-medium text-gray-500 w-32">{{ __('event_applications.wizard.labels.unit_value') }}</th>
                            <th class="text-right py-2 pl-4 font-medium text-gray-500 w-32">{{ __('event_applications.wizard.labels.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group['items'] as $item)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4 text-gray-700">{{ __('event_applications.wizard.expense_items.' . $item) }}</td>
                                <td class="py-2 px-2">
                                    <input type="number" wire:model.lazy="formData.expenses.{{ $groupKey }}.{{ $item }}.qty"
                                           class="form-input w-full text-sm text-center" min="0">
                                </td>
                                <td class="py-2 px-2">
                                    <input type="number" wire:model.lazy="formData.expenses.{{ $groupKey }}.{{ $item }}.value"
                                           class="form-input w-full text-sm text-center" min="0" step="0.01">
                                </td>
                                <td class="py-2 pl-4 text-right text-gray-700 tabular-nums"
                                    x-text="formatCurrency(($wire.formData.expenses?.['{{ $groupKey }}']?.['{{ $item }}']?.qty || 0) * ($wire.formData.expenses?.['{{ $groupKey }}']?.['{{ $item }}']?.value || 0))">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300">
                            <td colspan="3" class="py-2 pr-4 font-semibold text-gray-700 text-right">{{ __('event_applications.wizard.labels.group_total') }}</td>
                            <td class="py-2 pl-4 text-right font-semibold text-gray-900 tabular-nums"
                                x-text="formatCurrency(calcTotal($wire.formData.expenses?.['{{ $groupKey }}'] || {}))">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach
</div>

<hr class="border-gray-200 my-6">

{{-- REVENUE --}}
<div class="mb-8">
    <h4 class="text-base font-semibold text-slate-700 mb-4">{{ __('event_applications.wizard.sections.revenue') }}</h4>

    {{-- Revenue Partners Repeater --}}
    <div class="mb-4">
        <div class="flex items-center justify-between mb-3">
            <h5 class="text-sm font-medium text-slate-600">{{ __('event_applications.wizard.revenue_groups.partners') }}</h5>
            <button type="button" wire:click="addRepeaterRow('revenue_partners')" class="btn btn-sm btn-secondary">
                <x-heroicon-m-plus class="w-4 h-4 mr-1" />
                {{ __('common.add') }}
            </button>
        </div>

        @forelse($formData['revenue']['partners'] as $index => $rp)
            <div wire:key="rev-partner-{{ $index }}" class="border border-gray-200 rounded-lg p-4 mb-3">
                <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-medium text-gray-500">#{{ $index + 1 }}</span>
                    <button type="button" wire:click="removeRepeaterRow('revenue_partners', {{ $index }})"
                            class="text-rose-500 hover:text-rose-700">
                        <x-heroicon-m-trash class="w-4 h-4" />
                    </button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.partner_entity') }}</label>
                        <input type="text" wire:model="formData.revenue.partners.{{ $index }}.entity" class="form-input w-full text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.quantity') }}</label>
                        <input type="number" wire:model="formData.revenue.partners.{{ $index }}.qty" class="form-input w-full text-sm" min="0">
                    </div>
                    <div>
                        <label class="block text-xs font-medium mb-1">{{ __('event_applications.wizard.labels.value') }}</label>
                        <input type="number" wire:model="formData.revenue.partners.{{ $index }}.value" class="form-input w-full text-sm" min="0" step="0.01">
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 italic">{{ __('event_applications.wizard.no_entries') }}</p>
        @endforelse
    </div>

    @php
        $revenueGroups = [
            'registrations' => [
                'title' => 'event_applications.wizard.revenue_groups.registrations',
                'items' => ['clubs', 'participants'],
            ],
            'sales' => [
                'title' => 'event_applications.wizard.revenue_groups.sales',
                'items' => ['equipment', 'merch', 'stand_rental', 'other'],
            ],
            'other_revenue' => [
                'title' => 'event_applications.wizard.revenue_groups.other_revenue',
                'items' => ['meals', 'accommodation', 'equipment_rental', 'other'],
            ],
        ];
    @endphp

    @foreach($revenueGroups as $groupKey => $group)
        <div class="mb-4">
            <h5 class="text-sm font-medium text-slate-600 mb-2">{{ __($group['title']) }}</h5>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 pr-4 font-medium text-gray-500">{{ __('event_applications.wizard.labels.item') }}</th>
                            <th class="text-center py-2 px-2 font-medium text-gray-500 w-24">{{ __('event_applications.wizard.labels.quantity') }}</th>
                            <th class="text-center py-2 px-2 font-medium text-gray-500 w-32">{{ __('event_applications.wizard.labels.unit_value') }}</th>
                            <th class="text-right py-2 pl-4 font-medium text-gray-500 w-32">{{ __('event_applications.wizard.labels.subtotal') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group['items'] as $item)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4 text-gray-700">{{ __('event_applications.wizard.revenue_items.' . $item) }}</td>
                                <td class="py-2 px-2">
                                    <input type="number" wire:model.lazy="formData.revenue.{{ $groupKey }}.{{ $item }}.qty"
                                           class="form-input w-full text-sm text-center" min="0">
                                </td>
                                <td class="py-2 px-2">
                                    <input type="number" wire:model.lazy="formData.revenue.{{ $groupKey }}.{{ $item }}.value"
                                           class="form-input w-full text-sm text-center" min="0" step="0.01">
                                </td>
                                <td class="py-2 pl-4 text-right text-gray-700 tabular-nums"
                                    x-text="formatCurrency(($wire.formData.revenue?.['{{ $groupKey }}']?.['{{ $item }}']?.qty || 0) * ($wire.formData.revenue?.['{{ $groupKey }}']?.['{{ $item }}']?.value || 0))">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-gray-300">
                            <td colspan="3" class="py-2 pr-4 font-semibold text-gray-700 text-right">{{ __('event_applications.wizard.labels.group_total') }}</td>
                            <td class="py-2 pl-4 text-right font-semibold text-gray-900 tabular-nums"
                                x-text="formatCurrency(calcTotal($wire.formData.revenue?.['{{ $groupKey }}'] || {}))">
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endforeach
</div>

{{-- Section Comments --}}
@if($application)
    @include('web.entity.event-applications.components.section-comments', [
        'application' => $application,
        'section' => 'budget',
    ])
@endif

</div>
