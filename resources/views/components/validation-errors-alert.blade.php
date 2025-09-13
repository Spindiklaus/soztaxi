@if ($errors->any())
    <div id="validation-errors-alert" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 relative rounded">
        <div class="flex justify-between items-start">
            <div>
                <p class="font-bold">Ошибки при создании заказа:</p>
                <ul class="list-disc pl-5 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button onclick="this.closest('#validation-errors-alert').style.display='none'" 
                    class="text-red-500 hover:text-red-700 focus:outline-none ml-4">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
@endif