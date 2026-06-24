<div class="flex gap-4 @if($message_type =='success') bg-gradient-to-r from-green-50 to-green-100 border-l-4 border-green-500 @elseif($message_type == 'info') bg-gradient-to-r from-blue-50 to-blue-100 border-l-4 border-blue-500 @else bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 @endif p-4 rounded-lg shadow-sm mb-8 items-center">
    <div class="w-max">
        <div class="bg-white p-1.5 rounded-full shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" 
                class="w-6 h-6 @if($message_type =='success') text-green-500 @elseif($message_type == 'info') text-blue-500 @else text-red-500 @endif">
                @if($message_type =='success')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                @elseif($message_type == 'info')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                @endif
            </svg>
        </div>
    </div>
    <div class="text-sm">
        <h6 class="font-semibold @if($message_type =='success') text-green-800 @elseif($message_type == 'info') text-blue-800 @else text-red-800 @endif"> {{ $message_title }} </h6>
        <p class="@if($message_type =='success') text-green-700 @elseif($message_type == 'info') text-blue-700 @else text-red-700 @endif leading-tight mt-1">
            {{ $message_body }}
        </p>
    </div>
</div>
