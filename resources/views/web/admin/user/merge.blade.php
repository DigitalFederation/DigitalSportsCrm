<x-layout>
    <div class="previous-layout-classes">
        <!-- Page header -->
        <div class="mb-8 flex justify-between items-center">
            <!-- Title -->
            <h1 class="page-first-title">{{ __('Merge Users') }}</h1>
            <!-- Right: Actions -->
            <a class="btn btn-primary" href="{{ route('admin.users.index') }}">
                <span>{{ __('Users list') }}</span>
            </a>
        </div>

        <div class="flex information-box items-center w-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-4" width="24" height="24" viewBox="0 0 24 24"
                 stroke-width="1.5" stroke="#9e9e9e" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <circle cx="12" cy="12" r="9" />
                <line x1="12" y1="8" x2="12.01" y2="8" />
                <polyline points="11 12 12 12 12 16 13 16" />
            </svg>
            <p class="text-sm">
                {{ __('Use this form to merge two user accounts.') }} <br>
                <strong>{{ __('The source user account will be deleted and its data transferred to the target account.') }}</strong>
                <br>
                {{ __('This action cannot be undone. Please double-check the information before confirming.') }}
            </p>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <form id="mergeForm">
                    @csrf
                    <div class="mb-4">
                        <label for="source_email" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Source User Email (will be deleted):') }}
                        </label>
                        <input type="email" id="source_email" name="source_email" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="email@example.com">
                    </div>
                    <div class="mb-6">
                        <label for="target_email" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Target User Email (will be kept):') }}
                        </label>
                        <input type="email" id="target_email" name="target_email" required
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                               placeholder="email@example.com">
                    </div>
                    <div class="flex justify-end">
                        <button type="button" id="previewButton" class="btn btn-primary">
                            {{ __('Preview Merge') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="previewModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog"
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                        {{ __('Merge Preview') }}
                    </h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            {{ __('Please review the information below before proceeding with the merge.') }}
                        </p>
                        <div id="previewContent" class="mt-4"></div>

                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="confirmMerge" class="btn btn-primary ml-3">
                        {{ __('Confirm Merge') }}
                    </button>
                    <button type="button" id="cancelMerge" class="btn btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                </div>
            </div>
        </div>
    </div>


    <style>
        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            color: white;
            max-width: 24rem;
            z-index: 50;
        }

        .notification-success {
            background-color: #10B981;
        }

        .notification-error {
            background-color: #EF4444;
        }
    </style>


    @push('footer-scripts')
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const form = document.getElementById("mergeForm");
                const previewButton = document.getElementById("previewButton");
                const previewModal = document.getElementById("previewModal");
                const previewContent = document.getElementById("previewContent");
                const confirmMerge = document.getElementById("confirmMerge");
                const cancelMerge = document.getElementById("cancelMerge");

                function showNotification(message, type) {
                    const notification = document.createElement("div");
                    notification.textContent = message;
                    notification.className = `notification notification-${type}`;
                    document.body.appendChild(notification);
                    setTimeout(() => notification.remove(), 5000);
                }

                previewButton.addEventListener("click", async function() {
                    try {
                        const response = await fetch('{{ route("admin.users.merge.preview") }}', {
                            method: "POST",
                            body: new FormData(form),
                            headers: {
                                "X-CSRF-TOKEN": '{{ csrf_token() }}',
                                "Accept": "application/json"
                            }
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || "An error occurred");
                        }

                        const data = await response.json();
                        let individualSection = "";

                        if (data.source.individual && data.target.individual) {
                            individualSection = `
                            <div>
                                <h4 class="font-semibold">Individuals</h4>
                                <p>Both users have an individual associated. Please select which individual to keep:</p>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="individual_choice" value="source" checked class="form-radio text-indigo-600">
                                        <span class="ml-2">Keep Source User's Individual: <strong>${data.source.individual.name}</strong></span>
                                    </label>
                                </div>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="radio" name="individual_choice" value="target" class="form-radio text-indigo-600">
                                        <span class="ml-2">Keep Target User's Individual: <strong>${data.target.individual.name}</strong></span>
                                    </label>
                                </div>
                            </div>
                        `;
                        } else if (data.source.individual) {
                            individualSection = `
                            <div>
                                <h4 class="font-semibold">Individuals</h4>
                                <p>The source user has an individual that will be transferred to the target user:</p>
                                <p><strong>${data.source.individual.name}</strong></p>
                            </div>
                        `;
                        } else if (data.target.individual) {
                            individualSection = `
                            <div>
                                <h4 class="font-semibold">Individuals</h4>
                                <p>The target user has an individual. No individual will be transferred from the source user.</p>
                                <p><strong>${data.target.individual.name}</strong></p>
                            </div>
                        `;
                        } else {
                            individualSection = `
                            <div>
                                <h4 class="font-semibold">Individuals</h4>
                                <p>No individuals are associated with either user.</p>
                            </div>
                        `;
                        }

                        previewContent.innerHTML = `
                        <div class="space-y-4">
                            <div>
                                <h4 class="font-semibold">Source User (Will be deleted)</h4>
                                <p>${data.source.name} (${data.source.email})</p>
                                <p><strong>Roles:</strong> ${data.source.roles.join(", ") || "None"}</p>
                                <p><strong>Federations:</strong> ${data.source.federations.join(", ") || "None"}</p>
                                <p><strong>Entities:</strong> ${data.source.entities.join(", ") || "None"}</p>
                                ${data.source.individuals && data.source.individuals.length > 0 ? `
                                    <p><strong>Individuals:</strong></p>
                                    <ul>
                                        ${data.source.individuals.map(individual => `
                                            <li>
                                                ${individual.name}
                                                <ul>
                                                    <li><strong>Certifications:</strong> ${individual.certifications.join(", ") || "None"}</li>
                                                    <li><strong>Licenses:</strong> ${individual.licenses.join(", ") || "None"}</li>
                                                </ul>
                                            </li>
                                        `).join("")}
                                    </ul>
                                ` : ""}
                            </div>
                            <div>
                                <h4 class="font-semibold">Target User (Will be kept)</h4>
                                <p>${data.target.name} (${data.target.email})</p>
                                <p><strong>Roles:</strong> ${data.target.roles.join(", ") || "None"}</p>
                                <p><strong>Federations:</strong> ${data.target.federations.join(", ") || "None"}</p>
                                <p><strong>Entities:</strong> ${data.target.entities.join(", ") || "None"}</p>
                                ${data.target.individuals && data.target.individuals.length > 0 ? `
                                    <p><strong>Individuals:</strong></p>
                                    <ul>
                                        ${data.target.individuals.map(individual => `
                                            <li>${individual.name}</li>
                                        `).join("")}
                                    </ul>
                                ` : ""}
                            </div>
                            <div>
                                <h4 class="font-semibold">Data to be merged</h4>
                                <ul class="list-disc pl-5">
                                    ${data.mergeDetails.map(detail => `<li>${detail}</li>`).join("")}
                                </ul>
                            </div>
                            ${individualSection}
                        </div>
                    `;

                        previewModal.classList.remove("hidden");
                    } catch (error) {
                        console.error("Error:", error);
                        showNotification(error.message, "error");
                    }
                });

                confirmMerge.addEventListener("click", async function() {
                    try {
                        const formData = new FormData(form);

                        // Append individual choice if available
                        const individualChoice = document.querySelector("input[name=\"individual_choice\"]:checked");
                        if (individualChoice) {
                            formData.append("individual_choice", individualChoice.value);
                        }

                        const response = await fetch('{{ route("admin.users.merge") }}', {
                            method: "POST",
                            body: formData,
                            headers: {
                                "X-CSRF-TOKEN": '{{ csrf_token() }}',
                                "Accept": "application/json"
                            }
                        });

                        if (!response.ok) {
                            const errorData = await response.json();
                            throw new Error(errorData.message || "An error occurred");
                        }

                        const result = await response.json();
                        showNotification(result.message, "success");
                        setTimeout(() => {
                            window.location.href = '{{ route("admin.users.index") }}';
                        }, 2000);
                    } catch (error) {
                        console.error("Error:", error);
                        showNotification(error.message, "error");
                    }
                });

                cancelMerge.addEventListener("click", function() {
                    previewModal.classList.add("hidden");
                });
            });
        </script>
    @endpush

</x-layout>
