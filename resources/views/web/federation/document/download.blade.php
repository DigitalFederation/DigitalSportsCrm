<x-print>
  <section class="relative bg-gray-100 py-12 z-2">
      <div class="container px-4">
        <div class="flex flex-wrap -mx-4">
            <div class="mx-auto px-4 relative w-full lg:w-10/12">
              <div class="bg-white shadow-lg rounded-lg relative flex flex-col min-w-0 break-words w-full mb-6">
                  <div class="px-5 pt-6 pb-4 border-b border-gray-600 ">
                    <div class="justify-between flex flex-wrap -mx-4">
                        <div class="text-left px-4 relative w-full md:w-4/12">
                          <div class="text-left">
                              <img alt="..." src="{{ asset(config('branding.primary.logo_path', 'img/project-logo.svg'))}}" class="w-60 mb-2">

                              <h6 class="block capitalize text-gray-700 mt-6">{{ __('main.cmas_full_name') }}</h6>
                              <h6 class="text-gray-700">Viale Tiziano, 74 <br> 00196 Rome <br> Italy <br> C.F. 97091690582</h6>
                          </div>
                        </div>
                        <div class="text-left px-4 relative w-full lg:w-3/12 md:w-5/12">
                          <div class="flex justify-center py-6 lg:pt-6 pt-12">
                              <div class="text-left">
                                <h3 class="text-2xl font-bold leading-normal mt-0 mb-2">{{ __('official_documents.billed_to') }}:</h3>
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
                            @if($document->stateName() != 'paid')
                                <h4 class="text-2xl font-semibold leading-normal mb-2 mt-12 text-left">{{ __('official_documents.order_number') }}</h4>
                                <h3 class="text-2xl leading-normal mt-1 mb-2 font-light">#{{ $document->number_extended }}</h3>
                            @else
                                <h4 class="text-2xl font-semibold leading-normal mb-2 mt-12 text-left">{{ __('official_documents.invoice_number') }}</h4>
                                <h3 class="text-2xl leading-normal mt-1 mb-2 font-light">#{{ $document->invoice_extended }}</h3>
                           @endif

                        </div>
                        <div class="text-left px-4 relative w-full lg:w-3/12 md:w-5/12">
                          <div class="flex gap-x-2 justify-start py-6 lg:pt-4">
                                <p class="font-bold">{{ __('official_documents.document_date') }}</p>
                                <p>
                                  {{ $document->created_at?->format('d/m/Y') }}
                                </p>

                          </div>
                        </div>
                    </div>
                  </div>
                  <div class="px-4 py-5 flex-auto">
                    <table class=" w-full mb-4 text-gray-800 border-collapse">
                        <thead class="bg-gray-800">
                          <tr class="text-white uppercase font-light">
                              <th class="text-left p-3 border-t" scope="col">{{ __('official_documents.item') }}</th>
                              <th class="text-right p-3 border-t" scope="col">{{ __('official_documents.qty') }}</th>
                              <th class="text-right  p-3 border-t" scope="col">{{ __('official_documents.unit_price') }}</th>
                              <th class="text-right  p-3 border-t" scope="col">{{ __('official_documents.amount') }}</th>
                          </tr>
                        </thead>
                        <tbody>
                        @foreach($document->details as $detail)
                          <tr>
                              <td class="text-left py-4 p-3 flex items-start justify-start">
                                  <div class="font-bold">
                                    {{ $detail->owner->morphName ?? null }}
                                  </div>
                                  <div class="gap-x-2 ml-4">
                                      <p>{{ $detail->description }}</p>
                                  </div>
                              </td>
                              <td class="py-4 p-3 text-right ">{{ $detail->quantity }}</td>
                              <td class="py-4 p-3 text-right ">{{ $detail->unit_value }}€</td>
                              <td class="py-4 p-3 text-right ">{{ $detail->total_value }}€</td>
                          </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                          <tr class="mt-4">
                              <th class="border-b-0 p-3 border-t text-right">
                                <p class="text-lg font-semibold pt-2">{{ __('official_documents.total') }}</p>
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
</x-print>
