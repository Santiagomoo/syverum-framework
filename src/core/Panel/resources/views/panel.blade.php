
<div class="bg-blue-950 text-gray-400 text-sm flex flex-col px-10">
    
    {{-- Navegación de categorías --}}
    <ul class="flex justify-end py-3 border-b border-blue-800">
        <li>
            <button onclick="toggleSection('routesSection')" class="text-blue-200 hover:underline focus:outline-none cursor-pointer mx-3">
                Routes
            </button>
        </li>
        <li>
            <button onclick="toggleSection('httpSection')" class="text-blue-200 hover:underline focus:outline-none cursor-pointer mx-3">
                Http
            </button>
        </li>
        <li>
            <button onclick="toggleSection('databaseSection')" class="text-blue-200 hover:underline focus:outline-none cursor-pointer mx-3">
                Database
            </button>
        </li>
    </ul>

    @foreach($monitoring as $key => $value)
        <div id="{{ $key }}Section" class="max-h-0 opacity-0 hidden">
            <h3 class="font-bold text-blue-300 text-md mb-4">{{ ucfirst($key) }}</h3>

            @if($key === 'routes' && is_array($value))
                @foreach($value as $method => $routes)
                    <h4 class="text-blue-400 text-sm mb-2 font-semibold uppercase">{{ $method }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                        @foreach($routes as $route)
                            <div class="bg-gray-950 p-4 rounded-xl shadow-md hover:shadow-lg transition duration-200 border border-blue-700 space-y-1">
                                <p><span class="text-blue-400 text-xs">Ruta:</span> <span class="text-white">{{ $route['endPoint'] }}</span></p>
                                <p><span class="text-blue-400 text-xs">Controlador:</span> <span class="text-gray-300">{{ $route['controller'] }}</span></p>
                                <p><span class="text-blue-400 text-xs">Metodo usado del controlador:</span> <span class="text-gray-300">{{ $route['function'] }}</span></p>
                                <p><span class="text-blue-400 text-xs">Nombre:</span> <span class="text-gray-300">{{ $route['routeName'] }}</span></p>

                                <div class="flex justify-between items-center pt-2">
                                    <span class="text-sm {{ $route['onUse'] === 'on' ? 'text-green-400' : 'text-red-400' }}">
                                        {{ $route['onUse'] === 'on' ? 'Activa' : 'Inactiva' }}
                                    </span>
                                    <span class="text-xs bg-blue-600 px-2 py-1 rounded text-blue-100 uppercase">
                                        {{ $method }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

            @elseif(is_array($value))
                @if($key === 'database')
                    <div class="bg-gray-950 p-4 rounded-lg text-sm space-y-2 border border-blue-800">
                        <p class="text-blue-300 font-semibold mb-2">Estado de conexión a la base de datos:</p>
                        <ul class="ml-2 space-y-1">
                            <li>
                                <span class="text-gray-300">Conexión:</span>
                                @if($value['connected'])
                                    <span class="text-green-400 font-semibold">Establecida ✅</span>
                                @else
                                    <span class="text-red-500 font-semibold">Fallida ❌</span>
                                @endif
                            </li>
                            <li>
                                <span class="text-gray-300">Driver:</span>
                                <span class="text-white">{{ $value['driver'] ?? 'N/A' }}</span>
                            </li>
                            <li>
                                <span class="text-gray-300">Base de datos:</span>
                                <span class="text-white">{{ $value['database'] ?? 'N/A' }}</span>
                            </li>


                                <li>
                                    <span class="text-gray-300">Error:</span>
                                    <span class="text-red-400">{{ $value['error'] ?? 'N/A' }}</span>
                                </li>
                        </ul>
                    </div>
                @else
                    {{-- Renderizado por defecto de arrays --}}
                    <div class="bg-gray-950 p-4 rounded-lg text-sm space-y-2">
                        @foreach($value as $i => $item)
                            <div class="bg-gray-950 border border-blue-900 p-3 rounded">
                                <p class="text-blue-400">
                                    <strong>#{{ is_numeric($i) ? $i + 1 : $i }}</strong>
                                </p>

                                @if(is_array($item))
                                    <ul class="ml-4 list-disc">
                                        @foreach($item as $k => $v)
                                            <li><span class="text-gray-300">{{ $k }}:</span> <span class="text-white">{{ is_array($v) ? json_encode($v) : $v }}</span></li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-white">{{ $item }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    @endforeach
</div>



<script>
    function toggleSection(id) {
        const allSections = document.querySelectorAll('[id$="Section"]');
        allSections.forEach(sec => {
            const isTarget = sec.id === id;
            const isOpen = !sec.classList.contains('hidden');
    
            if (isTarget) {
                if (isOpen) {
                    // Cerrar si está abierta
                    sec.classList.remove("max-h-[1000px]", "opacity-100", "p-5");
                    sec.classList.add("max-h-0", "opacity-0", "pointer-events-none");

                    
                    setTimeout(() => {
                        sec.classList.remove("transition-all", "duration-200", "ease-in-out");
                        sec.classList.add("hidden");
                    }, 250);
                } else {
                    // Abrir si está cerrada
                    sec.classList.remove("hidden");
                    requestAnimationFrame(() => {
                        sec.classList.add("transition-all", "duration-300", "ease-in-out");
                        sec.classList.remove("max-h-0", "opacity-0", "pointer-events-none");
                        sec.classList.add("max-h-[1000px]", "opacity-100", "p-5");
                    });
                }
            } else {
                // Cerrar todas las demás
                if (!sec.classList.contains('hidden')) {
                    sec.classList.remove("max-h-[1000px]", "opacity-100", "p-5");
                    sec.classList.add("max-h-0", "opacity-0", "pointer-events-none");
                    setTimeout(() => {
                        sec.classList.remove("transition-all", "duration-200", "ease-in-out");
                        sec.classList.add("hidden");
                    }, 250);
                }
            }
        });
    }
    </script>
