<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Http\Traits\ExportsToExcel;
use App\Http\Traits\ChecksExportPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    use ExportsToExcel, ChecksExportPermission;
    /**
     * Muestra la lista de clientes.
     * Reemplaza la lógica GET de 'modules/clientes.php' [cite: 390]
     */
    public function index(Request $request)
    {
        $buscar = $request->query('buscar_nombre');

        $query = Cliente::orderBy('created_at', 'desc');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                    ->orWhere('apellido', 'LIKE', "%{$buscar}%")
                    ->orWhere('dni', 'LIKE', "%{$buscar}%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $clientes = $query->paginate($perPage)->appends(['per_page' => $perPage])->withQueryString();

        // Esto carga la vista 'resources/views/clientes/index.blade.php'
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Guarda un nuevo cliente en la base de datos.
     * Reemplaza la lógica POST de 'modules/clientes.php' [cite: 387-389]
     */
    public function store(Request $request)
    {
        // Validación de Laravel (basada en tu JS [cite: 11-12])
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'celular' => 'required|string|regex:/^[0-9]{9}$/',
            'dni' => 'nullable|string|regex:/^[0-9]{8}$/|unique:clientes,dni',
            'direccion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'Completa el campo de nombre.',
            'apellido.required' => 'Completa el campo de apellido.',
            'celular.required' => 'Completa el campo de celular.',
            'celular.regex' => 'El celular debe tener exactamente 9 números.',
            'dni.regex' => 'El DNI debe tener exactamente 8 números.',
            'dni.unique' => 'Ya existe un cliente registrado con este DNI.'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        // Crear el cliente usando el Modelo
        try {
            Cliente::create($validator->validated());
            return response()->json(['success' => true, 'message' => 'Cliente registrado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Muestra el formulario de edición (para AJAX en modal).
     * Reemplaza 'modules/editar_cliente.php' (GET) [cite: 664]
     */
    public function edit(Cliente $cliente)
    {
        // Gracias al Route-Model Binding, Laravel busca el cliente automáticamente
        // Esto devuelve solo el HTML del modal
        return view('clientes.partials.edit_modal_content', compact('cliente'));
    }

    /**
     * Actualiza un cliente.
     * Reemplaza 'modules/editar_cliente.php' (POST) [cite: 691-694]
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'celular' => 'required|string|regex:/^[0-9]{9}$/',
            // Validar 'unique' ignorando el DNI del cliente actual
            'dni' => 'nullable|string|regex:/^[0-9]{8}$/|unique:clientes,dni,' . $cliente->id_cliente . ',id_cliente',
            'direccion' => 'nullable|string|max:255',
        ], [
            'nombre.required' => 'Completa el campo de nombre.',
            'apellido.required' => 'Completa el campo de apellido.',
            'celular.required' => 'Completa el campo de celular.',
            'celular.regex' => 'El celular debe tener exactamente 9 números.',
            'dni.regex' => 'El DNI debe tener exactamente 8 números.',
            'dni.unique' => 'Ya existe un cliente registrado con este DNI.'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        try {
            $cliente->update($validator->validated());
            return response()->json(['success' => true, 'message' => 'Cliente actualizado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un cliente.
     * Reemplaza 'modules/eliminar_cliente.php' [cite: 869-879]
     */
    public function destroy(Cliente $cliente)
    {
        try {
            // Replicamos tu lógica de seguridad [cite: 871]
            if ($cliente->mascotas()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este cliente tiene mascotas registradas. Debe eliminar primero las mascotas.'
                ], 400);
            }

            // (Aquí también replicarías la lógica para 'ventas', 'citas', etc.)

            $cliente->delete();
            return response()->json(['success' => true, 'message' => 'Cliente eliminado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Busca propietarios para el modal de mascotas.
     * Reemplaza 'modules/buscar_propietario.php' [cite: 278-280]
     */
    public function buscarPropietarios(Request $request)
    {
        $query = $request->input('q');

        $clientes = Cliente::where('nombre', 'LIKE', "%{$query}%")
            ->orWhere('apellido', 'LIKE', "%{$query}%")
            ->orWhere('dni', 'LIKE', "%{$query}%")
            ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$query}%"])
            ->limit(10)
            ->get();

        // Devolvemos el HTML listo para el dropdown (como en tu código original)
        return view('clientes.partials.search_results', compact('clientes'));
    }

    /**
     * Proxy para la API de RENIEC.
     * Reemplaza 'modules/consultar_reniec.php'
     */
    public function buscarReniec(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|size:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 400);
        }

        $dni = $request->input('dni');
        $apiKey = config('services.reniec.api_key', env('RENIEC_API_KEY'));
        $apiUrl = config('services.reniec.api_url', env('RENIEC_API_URL'));

        // Log para depuración
        \Log::info('RENIEC Config Check', [
            'apiKey_exists' => !empty($apiKey),
            'apiUrl_exists' => !empty($apiUrl),
            'apiKey_length' => $apiKey ? strlen($apiKey) : 0,
            'apiUrl' => $apiUrl
        ]);

        if (!$apiKey || !$apiUrl) {
            return response()->json([
                'success' => false, 
                'message' => 'API de RENIEC no configurada en el servidor.',
                'debug' => [
                    'apiKey_present' => !empty($apiKey),
                    'apiUrl_present' => !empty($apiUrl)
                ]
            ], 500);
        }

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $apiUrl . $dni, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                ],
                'verify' => false // Opcional: Desactiva verificación SSL si tienes problemas
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                // Adaptar la respuesta de la API a lo que tu JS espera
                return response()->json([
                    'success' => true,
                    'nombres' => $data['first_name'] ?? '',
                    'apellido_paterno' => $data['first_last_name'] ?? '',
                    'apellido_materno' => $data['second_last_name'] ?? '',
                ]);
            } else {
                return response()->json(['success' => false, 'message' => 'DNI no encontrado.'], 404);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Exporta los clientes a Excel
     */
    public function exportar(Request $request)
    {
        // Verificar permiso usando trait
        if ($response = $this->checkExportPermission('puede_exportar_clientes', 'No tienes permiso para exportar clientes.')) {
            return $response;
        }

        $buscar = $request->query('buscar_nombre');

        $query = Cliente::query();

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('nombre', 'LIKE', "%{$buscar}%")
                    ->orWhere('apellido', 'LIKE', "%{$buscar}%")
                    ->orWhere('dni', 'LIKE', "%{$buscar}%")
                    ->orWhereRaw("CONCAT(nombre, ' ', apellido) LIKE ?", ["%{$buscar}%"]);
            });
        }

        $clientes = $query->orderBy('nombre')->get();

        // Preparar datos para exportación
        $data = [];
        foreach ($clientes as $cliente) {
            $data[] = [
                $cliente->id_cliente,
                $cliente->nombre,
                $cliente->apellido,
                $cliente->dni ?? '',
                $cliente->telefono ?? '',
                $cliente->direccion ?? ''
            ];
        }

        // Exportar usando trait (elimina ~60 líneas de código duplicado)
        return $this->exportToExcel(
            'LISTA DE CLIENTES',
            ['ID', 'Nombre', 'Apellido', 'DNI', 'Teléfono', 'Dirección'],
            $data,
            'clientes_' . date('Y-m-d_His') . '.xlsx'
        );
    }
}