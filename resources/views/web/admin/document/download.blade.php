<x-pdf>
  <section class="relative bg-gray-100 py-12 z-2">
      <div class="container mx-auto px-4">
        <div class="flex flex-wrap -mx-4">
            <div class="mx-auto px-4 relative w-full lg:w-10/12">
              <div class="bg-white shadow-lg rounded-lg relative flex flex-col min-w-0 break-words w-full mb-6">
                  <div class="px-5 pt-6 pb-4 border-b border-gray-600 ">
                    <div class="justify-between flex flex-wrap -mx-4">
                        <div class="text-left px-4 relative w-full md:w-4/12">
                          <div class="text-left">
                              <x-brand-logo class="w-60 mb-2" text-class="text-xl font-bold text-slate-800 block mb-2" />

                              <h6 class="block capitalize text-gray-700 mt-6">Confederation Mondiale Des Activites Subatiques</h6>
                              <h6 class="text-gray-700">Viale Tiziano, 74 <br> 00196 Rome <br> Italy <br> C.F. 97091690582</h6>
                          </div>
                        </div>
                        <div class="text-left px-4 relative w-full lg:w-3/12 md:w-5/12">
                          <div class="flex justify-center py-6 lg:pt-6 pt-12">
                              <div class="text-left">
                                <h3 class="text-2xl font-bold leading-normal mt-0 mb-2">Billed to:</h3>
                                <h6 class="block mt-2 mb-0 text-lg">
                                  @if($document->owner?->legal_name) {{ $document->owner->legal_name }} @endif
                                </h6>
                                <p class="text-gray-500">
                                  {{ $document->owner->address }}
                                  {{ $document->owner->location }}, {{ $document->owner->zip_code }} <br>
                                  {{ $document?->owner?->country?->name }}
                                </p>
                              </div>
                          </div>
                        </div>
                    </div>
                    <div class="md:justify-between flex flex-wrap -mx-4">
                        <div class="text-left px-4 relative w-full md:w-4/12">
                          <h4 class="text-2xl font-semibold leading-normal mb-2 mt-12 text-left">Invoice nº</h4>
                          <h3 class="text-2xl leading-normal mt-1 mb-2 font-light">#{{ $document->number_extended }}</h3>
                        </div>
                        <div class="text-left px-4 relative w-full lg:w-3/12 md:w-5/12">
                          <div class="flex justify-center py-6 lg:pt-4">
                              <div class="mt-12">
                                <p class="float-left mb-0">Invoice date:</p>
                                <p class="ml-4 float-right mb-0">
                                  {{ $document->created_at?->format('d/m/Y') }}
                                </p>
                              </div>
                          </div>
                        </div>
                    </div>
                  </div>
                  <div class="px-4 py-5 flex-auto">
                    <table class="text-right w-full mb-4 text-gray-800 border-collapse">
                        <thead class="bg-gray-800">
                          <tr class="text-right text-white uppercase font-light">
                              <th class="p-3 border-t" scope="col">Item</th>
                              <th class="p-3 border-t" scope="col">Qty</th>
                              <th class="p-3 border-t" scope="col">Unit Price</th>
                              <th class="p-3 border-t" scope="col">Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                        @foreach($document->details as $detail)
                          <tr>
                              <td class="py-4 p-3 border-t flex text-right items-start justify-end">
                                  <div class="font-bold">
                                    {{ $detail->owner->morphName ?? null }}
                                  </div>
                                  <div class="gap-x-2 ml-4">
                                      <p>{{ $detail->description }}</p>
                                  </div>
                              </td>
                              <td class="py-4 p-3 border-t">{{ $detail->quantity }}</td>
                              <td class="py-4 p-3 border-t">{{ $detail->unit_value }}€</td>
                              <td class="py-4 p-3 border-t">{{ $detail->total_value }}€</td>
                          </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                          <tr class="mt-4">
                              <th class="border-b-0 p-3 border-t">
                                <p class="text-lg font-semibold pt-2">Total</p>
                              </th>
                              <th class="border-b-0 p-3 border-t" colspan="3">
                                <p class="text-right text-lg font-semibold pt-2"> {{ $document->total_value }}€</p>
                              </th>
                          </tr>
                        </tfoot>
                    </table>
                  </div>
                  <div class="px-4 py-3 border-t">
                    <div class="text-left ml-auto px-4 relative w-full">
                        <p class="italic text-sm">Federations and International Organisations hereby undertake to comply with and strictly
                          enforce {{ config("branding.international.name", "the International Federation") }} rules, as well as to urge their members to adopt an under water environmental
                          friendly attitude.</p>
                        <p class="text-sm mt-4 border-t border-gray-400 pt-4 italic">
                          Les fédérations et organismes internationaux s'engagent par cette commande à appliquer et
                          faire appliquer strictement les règles de {{ config("branding.international.name", "the International Federation") }} et à inciter leurs membres à adopter une
                          attitude respectueuse de l'environnement sous-marin</p>
                    </div>
                  </div>
              </div>
            </div>
        </div>
      </div>
  </section>
    <script>
        window.addEventListener('load', function() {
            window.print();
        });
    </script>

</x-pdf>
