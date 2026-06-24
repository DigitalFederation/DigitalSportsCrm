{{-- 
UI Component Showcase 
This is a demonstration of the unified card component system
--}}

<div class="space-y-12 p-6 bg-gray-50 min-h-screen">
    
    <!-- Page Header -->
    <div class="text-center">
        <h1 class="text-3xl font-bold text-slate-900 mb-4">
            Unified Card Component System
        </h1>
        <p class="text-slate-600 max-w-2xl mx-auto">
            A Fortune 500-grade, consistent card system for membership packages and insurance plans across all views.
            Designed for scalability, accessibility, and visual consistency.
        </p>
    </div>

    <!-- Basic Cards -->
    <section>
        <h2 class="text-2xl font-semibold text-slate-900 mb-6">Base Card Components</h2>
        <x-ui.card-grid columns="4">
            <x-ui.card variant="default">
                <h3 class="font-semibold text-slate-900">Default Card</h3>
                <p class="text-slate-600 mt-2">Standard card with shadow and border</p>
            </x-ui.card>
            
            <x-ui.card variant="elevated">
                <h3 class="font-semibold text-slate-900">Elevated Card</h3>
                <p class="text-slate-600 mt-2">Higher shadow, no border</p>
            </x-ui.card>
            
            <x-ui.card variant="outlined">
                <h3 class="font-semibold text-slate-900">Outlined Card</h3>
                <p class="text-slate-600 mt-2">Subtle shadow with border</p>
            </x-ui.card>
            
            <x-ui.card variant="interactive" hoverable>
                <h3 class="font-semibold text-slate-900">Interactive Card</h3>
                <p class="text-slate-600 mt-2">Hover and focus states</p>
            </x-ui.card>
        </x-ui.card-grid>
    </section>

    <!-- UI Elements -->
    <section>
        <h2 class="text-2xl font-semibold text-slate-900 mb-6">UI Elements</h2>
        
        <div class="space-y-6">
            <!-- Buttons -->
            <div>
                <h3 class="text-lg font-medium text-slate-900 mb-3">Buttons</h3>
                <div class="flex flex-wrap gap-3">
                    <x-ui.button variant="primary">Primary Button</x-ui.button>
                    <x-ui.button variant="secondary">Secondary Button</x-ui.button>
                    <x-ui.button variant="outline">Outline Button</x-ui.button>
                    <x-ui.button variant="ghost">Ghost Button</x-ui.button>
                    <x-ui.button variant="danger">Danger Button</x-ui.button>
                    <x-ui.button variant="primary" loading>Loading...</x-ui.button>
                </div>
            </div>
            
            <!-- Badges -->
            <div>
                <h3 class="text-lg font-medium text-slate-900 mb-3">Badges</h3>
                <div class="flex flex-wrap gap-2">
                    <x-ui.badge variant="default">Default</x-ui.badge>
                    <x-ui.badge variant="blue">Active</x-ui.badge>
                    <x-ui.badge variant="green">Success</x-ui.badge>
                    <x-ui.badge variant="yellow">Warning</x-ui.badge>
                    <x-ui.badge variant="red">Error</x-ui.badge>
                    <x-ui.badge variant="gray">Inactive</x-ui.badge>
                    <x-ui.badge variant="purple">Premium</x-ui.badge>
                </div>
            </div>
        </div>
    </section>

    <!-- Empty State -->
    <section>
        <h2 class="text-2xl font-semibold text-slate-900 mb-6">Empty States</h2>
        <div class="max-w-md mx-auto">
            <x-ui.empty-state-card 
                title="No Packages Available"
                description="There are currently no membership packages available for your selection. Please check back later or contact support."
                action-text="Contact Support"
                action-href="#"
            />
        </div>
    </section>

    <!-- Mock Package Data for Demo -->
    @php
        $mockPackage = (object) [
            'id' => 1,
            'name' => 'Premium Membership',
            'description' => 'Complete membership package with full access to all federation benefits, insurance coverage, and certification programs.',
            'target_type' => 'individual',
            'is_active' => true,
            'distribution_methods' => ['direct', 'entity_managed'],
            'affiliationPlans' => collect([
                (object) ['name' => 'CMAS 1-Star Diver', 'price' => 45.00],
                (object) ['name' => 'Advanced Open Water', 'price' => 65.00],
                (object) ['name' => 'Rescue Diver', 'price' => 85.00],
            ]),
            'insurancePlans' => collect([
                (object) ['name' => 'Diving Insurance Premium', 'price' => 120.00],
                (object) ['name' => 'Equipment Coverage', 'price' => 75.00],
            ]),
        ];
        
        $mockInsurance = (object) [
            'id' => 1,
            'name' => 'Comprehensive Diving Insurance',
            'description' => 'Full coverage for diving activities including equipment, medical, and emergency evacuation.',
            'price' => 189.99,
            'monthly_price' => 18.99,
            'coverage_amount' => 100000,
            'geographic_coverage' => 'Worldwide',
            'is_active' => true,
            'activities_covered' => [
                'Recreational Diving',
                'Technical Diving',
                'Cave Diving',
                'Wreck Diving',
                'Equipment Protection'
            ]
        ];
    @endphp

    <!-- Membership Package Cards -->
    <section>
        <h2 class="text-2xl font-semibold text-slate-900 mb-6">Membership Package Cards</h2>
        
        <x-ui.card-grid columns="3">
            <!-- Standard Package Card -->
            <x-ui.membership-package-card 
                :package="$mockPackage"
                action-type="subscribe"
                user-type="individual"
            />
            
            <!-- Package Selection Card -->
            <x-ui.package-selection-card 
                :package="$mockPackage"
                :selected="false"
                show-description="true"
            />
            
            <!-- Selected Package Card -->
            <x-ui.package-selection-card 
                :package="$mockPackage"
                :selected="true"
                show-description="true"
            />
        </x-ui.card-grid>
    </section>

    <!-- Insurance Package Cards -->
    <section>
        <h2 class="text-2xl font-semibold text-slate-900 mb-6">Insurance Package Cards</h2>
        
        <x-ui.card-grid columns="2">
            <!-- Standard Insurance Card -->
            <x-ui.insurance-package-card 
                :package="$mockInsurance"
                action-type="subscribe"
            />
            
            <!-- Current Insurance Card -->
            <x-ui.insurance-package-card 
                :package="$mockInsurance"
                action-type="subscribe"
                :current-insurance="(object) ['insurance_plan_id' => 1, 'end_date' => now()->addMonths(6), 'policy_number' => 'POL-2024-001234']"
            />
        </x-ui.card-grid>
    </section>

    <!-- Grid Layouts -->
    <section>
        <h2 class="text-2xl font-semibold text-slate-900 mb-6">Responsive Grid Layouts</h2>
        
        <div class="space-y-8">
            <!-- Auto Columns -->
            <div>
                <h3 class="text-lg font-medium text-slate-900 mb-3">Auto-fit Grid</h3>
                <x-ui.card-grid columns="auto">
                    @for($i = 1; $i <= 6; $i++)
                        <x-ui.card size="compact">
                            <h4 class="font-medium text-slate-900">Card {{ $i }}</h4>
                            <p class="text-sm text-slate-600 mt-1">Auto-fit responsive grid</p>
                        </x-ui.card>
                    @endfor
                </x-ui.card-grid>
            </div>
            
            <!-- Fixed 4 Columns -->
            <div>
                <h3 class="text-lg font-medium text-slate-900 mb-3">4-Column Grid</h3>
                <x-ui.card-grid columns="4" gap="sm">
                    @for($i = 1; $i <= 4; $i++)
                        <x-ui.card size="compact" variant="outlined">
                            <h4 class="font-medium text-slate-900">Item {{ $i }}</h4>
                            <p class="text-xs text-slate-600 mt-1">Fixed columns</p>
                        </x-ui.card>
                    @endfor
                </x-ui.card-grid>
            </div>
        </div>
    </section>

    <!-- Design Principles -->
    <section>
        <h2 class="text-2xl font-semibold text-slate-900 mb-6">Design Principles</h2>
        
        <x-ui.card>
            <div class="prose prose-slate max-w-none">
                <h3>Fortune 500 Design Standards</h3>
                
                <div class="grid md:grid-cols-2 gap-6 mt-6">
                    <div>
                        <h4 class="text-lg font-semibold text-slate-900 mb-3">Consistency</h4>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>• Unified color palette (slate/blue system)</li>
                            <li>• Consistent spacing and typography</li>
                            <li>• Standardized hover and focus states</li>
                            <li>• Reusable component patterns</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold text-slate-900 mb-3">Accessibility</h4>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>• WCAG 2.1 compliant color contrast</li>
                            <li>• Keyboard navigation support</li>
                            <li>• Screen reader friendly structure</li>
                            <li>• Focus indicators on all interactive elements</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold text-slate-900 mb-3">Scalability</h4>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>• Component-based architecture</li>
                            <li>• Flexible prop system</li>
                            <li>• Responsive grid layouts</li>
                            <li>• Customizable variants</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-lg font-semibold text-slate-900 mb-3">Performance</h4>
                        <ul class="space-y-2 text-sm text-slate-600">
                            <li>• Minimal CSS classes</li>
                            <li>• Optimized animations</li>
                            <li>• Lazy loading support</li>
                            <li>• Mobile-first responsive design</li>
                        </ul>
                    </div>
                </div>
            </div>
        </x-ui.card>
    </section>
    
</div>