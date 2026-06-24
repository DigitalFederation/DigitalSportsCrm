<x-layout-full>
    <div class="container mx-auto px-4">
        <h1 class="text-2xl font-bold mb-4 mt-4">{{ __('Event Master List') }}</h1>

        <section>
            <div id="notification" class="fixed bottom-5 right-5 z-50 hidden">
                <div class="px-4 py-2 rounded text-white shadow-lg"></div>
            </div>

            <section >
                <button id="download-xlsx" class="btn btn-info">{{ __('Download XLSX') }}</button>
                <div id="events-table" class="shadow-md mt-4"></div>

            </section>

            @push('footer-scripts')
                <script src="https://cdn.jsdelivr.net/npm/luxon@3.5.0/build/global/luxon.min.js"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
                <script>
                    let eventsTable;

                    document.addEventListener("DOMContentLoaded", function() {
                        initTable();
                    });

                    function initTable() {
                        const columnDefinitions = @json($columnDefinitions);

                        eventsTable = new Tabulator("#events-table", {
                            ajaxURL: "{{ route('admin.evt-events.events.master.get') }}",
                            ajaxConfig: {
                                method: "GET",
                                headers: {
                                    "X-CSRF-TOKEN": '{{ csrf_token() }}'
                                }
                            },
                            layout: "fitDataFill",
                            pagination: true,
                            paginationMode: "remote",
                            paginationSize: 50,
                            paginationSizeSelector: [10, 50, 100, 200],
                            ajaxParams: {
                                size: 50
                            },
                            paginationInitialPage: 1,
                            height: "600px",  // Set a fixed height to enable vertical scrolling
                            movableColumns: false,  // Allow column reordering
                            resizableColumns: true,  // Allow column resizing
                            columns: columnDefinitions,
                            columnDefaults: {
                                minWidth: 100,
                                resizable: true
                            }
                        });

                        //table update callback
                        eventsTable.on("cellEdited", function(cell) {
                            var id = cell.getRow().getData().id;
                            var field = cell.getColumn().getField();
                            var value = cell.getValue();
                            updateEvent(id, field, value);
                        });
                    }

                    function updateEvent(id, field, value) {

                        fetch("{{ route('admin.evt-events.events.master.update') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ id, field, value })
                        })
                            .then(response => response.json())
                            .then(data => {
                                showNotification("success", data.message);
                                eventsTable.updateData([{ id: id, [field]: value }]);
                            })
                            .catch(error => {
                                console.error("Error:", error);
                                showNotification("error", "{{ __('Update failed') }}");
                            });
                    }

                    function showNotification(type, message) {
                        const notification = document.getElementById("notification");
                        const notificationContent = notification.querySelector("div");

                        notificationContent.textContent = message;
                        notificationContent.className = `px-4 py-2 rounded text-white shadow-lg ${type === "success" ? "bg-green-500" : "bg-red-500"}`;

                        notification.classList.remove("hidden");
                        setTimeout(() => notification.classList.add("hidden"), 3000);
                    }

                    document.getElementById("download-xlsx").addEventListener("click", function(){
                        eventsTable.download("xlsx", "data.xlsx", {sheetName:"EventsMasterList"});
                    });
                </script>
            @endpush
        </section>

    </div>
</x-layout-full>
