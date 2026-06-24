<div
    x-data="teamComposition({
        initialValue: {{ Js::from($value ?? []) }},
        genderOptions: ['male', 'female', 'mixed']
    })"
    class="space-y-4"
    x-init="$watch('rules', value => {
        $dispatch('team-composition-changed', rulesObject)
    }, { immediate: true })"
>
    <div class="sm:flex sm:items-start space-y-4 sm:space-y-0 sm:space-x-4 w-full">
        <div class="md:w-full">
            <label class="block text-sm font-medium mb-1">{{ __('Team Composition') }}</label>

            <!-- Rules List -->
            <div class="space-y-3 mb-2">
                <template x-for="(rule, index) in rules" :key="index">
                    <div class="flex items-center space-x-4 bg-gray-50 p-3 rounded-md">
                        <select
                            x-model="rule.gender"
                            @change="updateRules()"
                            class="form-select text-sm"
                            :name="`team_composition[${index}][gender]`"
                        >
                            <template x-for="option in genderOptions" :key="option">
                                <option
                                    :value="option"
                                    :selected="rule.gender === option"
                                    x-text="option.charAt(0).toUpperCase() + option.slice(1)"
                                ></option>
                            </template>
                        </select>

                        <div class="flex items-center space-x-2">
                            <input
                                type="number"
                                x-model.number="rule.count"
                                @change="updateRules()"
                                :name="`team_composition[${index}][count]`"
                                class="form-input w-20 text-sm"
                                min="0"
                            >
                            <span class="text-sm text-gray-600">{{ __('players') }}</span>
                        </div>

                        <button
                            type="button"
                            @click="removeRule(index)"
                            class="text-red-500 hover:text-red-700"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Add Rule Button -->
            <button
                type="button"
                @click="addRule"
                class="text-sm btn-warning px-2 py-1"
            >
                {{ __('Add composition rule') }}
            </button>

            <!-- Hidden JSON Input -->
            <input
                type="hidden"
                name="team_composition_requirements"
                :value="JSON.stringify(rulesObject)"
            >

            <!-- Preview -->
            <div x-show="rules.length > 0" class="mt-2 text-sm text-gray-500 border-indigo-100 border p-2">
                <div class="font-medium">Current Requirements:</div>
                <div x-text="formatSummary()" class="italic"></div>
            </div>

            <p class="cursor-help text-xs mt-1 text-gray-400">
                {{ __('Specify the required number of players by gender for team composition') }}
            </p>
        </div>
    </div>
</div>

@push('footer-scripts')
<script>
    document.addEventListener("alpine:init", () => {
        Alpine.data("teamComposition", ({ initialValue = {}, genderOptions = [] }) => ({
            rules: [],
            genderOptions,

            init() {
                console.log('init');
                console.log(initialValue);
                // Convert initial JSON object to rules array
                if (typeof initialValue === 'string') {
                    try {
                        initialValue = JSON.parse(initialValue);
                    } catch (e) {
                        initialValue = {};
                    }
                }

                this.rules = Object.entries(initialValue).map(([gender, count]) => ({
                    gender,
                    count: parseInt(count)
                }));

                if (this.rules.length === 0) {
                    this.addRule();
                }
            },

            updateRules() {
                // Force Alpine to recognize the change
                this.rules = [...this.rules];
            },

            get rulesObject() {
                return this.rules.reduce((acc, rule) => {
                    if (rule.gender && rule.count > 0) {
                        acc[rule.gender] = parseInt(rule.count);
                    }
                    return acc;
                }, {});
            },

            addRule() {
                this.rules.push({ gender: this.genderOptions[0], count: 1 });
                this.updateRules();
            },

            removeRule(index) {
                this.rules.splice(index, 1);
                this.updateRules();
            },

            formatSummary() {
                if (this.rules.length === 0) {
                    return "No composition rules defined";
                }
                return this.rules
                    .filter(rule => rule.gender && rule.count > 0)
                    .map(rule => `${rule.count} ${rule.gender} player${rule.count !== 1 ? "s" : ""}`)
                    .join(", ");
            }
        }));
    });
</script>
@endpush
