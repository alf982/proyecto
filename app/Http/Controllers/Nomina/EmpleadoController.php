<?php
namespace App\Http\Controllers\Nomina;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Empleado;
use App\Models\EmpleadoFamiliar;
use App\Models\EmpleadoFormacion;
use App\Models\EmpleadoHistorialCargo;
use App\Models\EmpleadoBonificacion;
use App\Models\ConceptoNomina;
use App\Models\UnidadEjecutora;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

/**
 * Controlador de Expediente de Empleados
 * 
 * Gestiona la ficha técnica completa del trabajador.
 * Asegura la correcta asignación de cargo, tipo de nómina y estado laboral,
 * parámetros que son leídos directamente por el motor de cálculo de nómina.
 */
class EmpleadoController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('can:nomina.empleados.ver',    only: ['index', 'show']),
            new Middleware('can:nomina.empleados.crear',  only: ['create', 'store']),
            new Middleware('can:nomina.empleados.editar', only: [
                'edit', 'update',
                'addFamiliar', 'deleteFamiliar',
                'addHistorial', 'deleteHistorial',
                'addFormacion', 'deleteFormacion',
                'addBonificacion', 'deleteBonificacion',
            ]),
        ];
    }

    // ── LISTADO ───────────────────────────────────────────────────
    
    /**
     * Muestra la nómina (listado) de empleados con filtros operativos.
     */
    public function index(Request $request)
    {
        $empleados = Empleado::with(['cargo', 'unidadEjecutora'])
            ->when($request->estado, fn($q, $v) => $q->where('estado', $v))
            ->when($request->tipo,   fn($q, $v) => $q->where('tipo', $v))
            ->when($request->search, fn($q, $v) => $q->where(
                fn($q) => $q->where('cedula', 'like', "%$v%")
                             ->orWhere('nombres', 'like', "%$v%")
                             ->orWhere('primer_apellido', 'like', "%$v%")
                             ->orWhere('segundo_apellido', 'like', "%$v%")
            ))
            ->orderBy('primer_apellido')
            ->paginate(25)->withQueryString();

        return view('nomina.empleados.index', compact('empleados'));
    }

    // ── CREAR ─────────────────────────────────────────────────────
    public function create()
    {
        $cargos   = Cargo::activos()->orderBy('nombre')->get();
        $unidades = UnidadEjecutora::where('activo', true)->orderBy('nombre')->get();
        return view('nomina.empleados.create', compact('cargos', 'unidades'));
    }

    // ── GUARDAR ───────────────────────────────────────────────────
    
    /**
     * Registra un nuevo empleado en el sistema.
     * Al ser creado, automáticamente entra al universo de empleados elegibles
     * para la generación de nómina si su estado es 'activo'.
     */
    public function store(Request $request)
    {
        $request->validate([
            'cedula'              => 'required|unique:empleados,cedula|max:15',
            'pasaporte'           => 'nullable|max:30',
            'nombres'             => 'required|string|max:100',
            'primer_apellido'     => 'required|string|max:100',
            'segundo_apellido'    => 'nullable|string|max:100',
            'sexo'                => 'required|in:F,M',
            'cargo_id'            => 'required|exists:cargos,id',
            'unidad_ejecutora_id' => 'required|exists:unidades_ejecutoras,id',
            'fecha_ingreso'       => 'required|date',
            'tipo'                => 'required|in:fijo,contratado,obrero',
            'fecha_nacimiento'    => 'nullable|date|before:today',
            'nivel_instruccion'   => 'nullable|in:sin_instruccion,primaria,secundaria,tsu,universitario,postgrado,doctorado',
            'estado_civil'        => 'nullable|in:soltero,casado,divorciado,viudo,concubinato',
            'curriculum'          => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'carnet_militar_foto' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $data = $request->only([
            'cedula','pasaporte','nombres','primer_apellido','segundo_apellido','sexo','cargo_id','unidad_ejecutora_id',
            'fecha_ingreso','tipo','banco','numero_cuenta','telefono','email','observaciones',
            'estado_civil','nacionalidad','pais_origen','numero_carnet_militar','fecha_expedicion_militar',
            'fecha_nacimiento','lugar_nacimiento','pais_nacimiento','estado_nacimiento','municipio_nacimiento',
            'nivel_instruccion','titulo','institucion_educativa',
            'pais_residencia','estado_residencia','municipio','parroquia','direccion_completa',
            'anos_experiencia_publica','meses_experiencia_publica','anos_experiencia_privada','meses_experiencia_privada','anos_experiencia_independiente','meses_experiencia_independiente',
            // Salud
            'tipo_sangre','tipo_discapacidad','condicion_medica',
            // Contacto emergencia
            'contacto_emergencia_nombre','contacto_emergencia_parentesco','contacto_emergencia_telefono',
        ]);

        $data['tiene_discapacidad'] = $request->boolean('tiene_discapacidad');
        $data['inhabilitado'] = $request->boolean('inhabilitado');
        $data['estado']     = 'activo';
        $data['creado_por'] = auth()->id();

        if ($request->hasFile('curriculum')) {
            $data['curriculum_path'] = $request->file('curriculum')
                ->store('empleados_cv', 'local');
        }

        if ($request->hasFile('carnet_militar_foto')) {
            $data['carnet_militar_foto_path'] = $request->file('carnet_militar_foto')
                ->store('empleados_militar', 'local');
        }

        Empleado::create($data);
        return redirect()->route('nomina.empleados.index')
            ->with('success', 'Empleado registrado correctamente.');
    }

    // ── DETALLE ───────────────────────────────────────────────────
    public function show(Empleado $empleado)
    {
        $empleado->load([
            'cargo', 'unidadEjecutora', 'nominasDetalle.nomina',
            'familiares',
            'historialCargos.cargo',
            'historialCargos.unidadEjecutora',
            'formaciones',
            'bonificaciones.concepto',
        ]);
        $cargos    = Cargo::activos()->orderBy('nombre')->get();
        $unidades  = UnidadEjecutora::where('activo', true)->orderBy('nombre')->get();
        $conceptos = ConceptoNomina::activos()->orderBy('nombre')->get();

        return view('nomina.empleados.show', compact('empleado', 'cargos', 'unidades', 'conceptos'));
    }

    // ── EDITAR ────────────────────────────────────────────────────
    public function edit(Empleado $empleado)
    {
        $cargos   = Cargo::activos()->orderBy('nombre')->get();
        $unidades = UnidadEjecutora::where('activo', true)->orderBy('nombre')->get();
        return view('nomina.empleados.edit', compact('empleado', 'cargos', 'unidades'));
    }

    // ── ACTUALIZAR ────────────────────────────────────────────────
    public function update(Request $request, Empleado $empleado)
    {
        $request->validate([
            'cedula'              => 'required|max:15|unique:empleados,cedula,' . $empleado->id,
            'pasaporte'           => 'nullable|max:30',
            'nombres'             => 'required|string|max:100',
            'primer_apellido'     => 'required|string|max:100',
            'segundo_apellido'    => 'nullable|string|max:100',
            'sexo'                => 'required|in:F,M',
            'cargo_id'            => 'required|exists:cargos,id',
            'unidad_ejecutora_id' => 'required|exists:unidades_ejecutoras,id',
            'fecha_ingreso'       => 'required|date',
            'tipo'                => 'required|in:fijo,contratado,obrero',
            'estado'              => 'required|in:activo,inactivo,jubilado,retirado',
            'fecha_nacimiento'    => 'nullable|date|before:today',
            'nivel_instruccion'   => 'nullable|in:sin_instruccion,primaria,secundaria,tsu,universitario,postgrado,doctorado',
            'estado_civil'        => 'nullable|in:soltero,casado,divorciado,viudo,concubinato',
            'curriculum'          => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'carnet_militar_foto' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $data = $request->only([
            'cedula','pasaporte','nombres','primer_apellido','segundo_apellido','sexo','cargo_id','unidad_ejecutora_id',
            'fecha_ingreso','tipo','banco','numero_cuenta','telefono','email',
            'estado','fecha_egreso','observaciones',
            'estado_civil','nacionalidad','pais_origen','numero_carnet_militar','fecha_expedicion_militar',
            'fecha_nacimiento','lugar_nacimiento','pais_nacimiento','estado_nacimiento','municipio_nacimiento',
            'nivel_instruccion','titulo','institucion_educativa',
            'pais_residencia','estado_residencia','municipio','parroquia','direccion_completa',
            'anos_experiencia_publica','meses_experiencia_publica','anos_experiencia_privada','meses_experiencia_privada','anos_experiencia_independiente','meses_experiencia_independiente',
            // Salud
            'tipo_sangre','tipo_discapacidad','condicion_medica',
            // Contacto emergencia
            'contacto_emergencia_nombre','contacto_emergencia_parentesco','contacto_emergencia_telefono',
        ]);
        
        $data['tiene_discapacidad'] = $request->boolean('tiene_discapacidad');
        $data['inhabilitado'] = $request->boolean('inhabilitado');

        if ($request->hasFile('curriculum')) {
            // Eliminar CV anterior si existe
            if ($empleado->curriculum_path) {
                Storage::disk('local')->delete($empleado->curriculum_path);
            }
            $data['curriculum_path'] = $request->file('curriculum')
                ->store('empleados_cv', 'local');
        }

        if ($request->hasFile('carnet_militar_foto')) {
            // Eliminar foto anterior si existe
            if ($empleado->carnet_militar_foto_path) {
                Storage::disk('local')->delete($empleado->carnet_militar_foto_path);
            }
            $data['carnet_militar_foto_path'] = $request->file('carnet_militar_foto')
                ->store('empleados_militar', 'local');
        }

        $empleado->update($data);
        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Datos del empleado actualizados correctamente.');
    }

    // ── DESCARGAR CURRICULUM ──────────────────────────────────────
    public function downloadCV(Empleado $empleado)
    {
        if (!$empleado->curriculum_path || !Storage::disk('local')->exists($empleado->curriculum_path)) {
            return back()->with('error', 'No hay curriculum registrado para este empleado.');
        }

        $nombre = 'CV_' . str_replace(' ', '_', $empleado->nombre_completo) . '_' . $empleado->cedula;
        $ext    = pathinfo($empleado->curriculum_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($empleado->curriculum_path, $nombre . '.' . $ext);
    }

    // ── DESCARGAR CARNET MILITAR ───────────────────────────────────
    public function downloadCarnetMilitar(Empleado $empleado)
    {
        if (!$empleado->carnet_militar_foto_path || !Storage::disk('local')->exists($empleado->carnet_militar_foto_path)) {
            return back()->with('error', 'No hay foto de carnet militar registrada para este empleado.');
        }

        $nombre = 'CarnetMilitar_' . str_replace(' ', '_', $empleado->nombre_completo) . '_' . $empleado->cedula;
        $ext    = pathinfo($empleado->carnet_militar_foto_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->download($empleado->carnet_militar_foto_path, $nombre . '.' . $ext);
    }

    // ── FAMILIARES ────────────────────────────────────────────────
    public function addFamiliar(Request $request, Empleado $empleado)
    {
        $request->validate([
            'nombre_completo'  => 'required|string|max:150',
            'parentesco'       => 'required|in:hijo,conyuge,padre,madre,hermano,otro',
            'cedula'           => 'nullable|string|max:20',
            'fecha_nacimiento' => 'nullable|date|before:today',
            'telefono'         => 'nullable|string|max:30',
            'es_carga_familiar'=> 'boolean',
            'observaciones'    => 'nullable|string|max:300',
        ]);

        $empleado->familiares()->create([
            'nombre_completo'  => $request->nombre_completo,
            'parentesco'       => $request->parentesco,
            'cedula'           => $request->cedula,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'telefono'         => $request->telefono,
            'es_carga_familiar'=> $request->boolean('es_carga_familiar'),
            'observaciones'    => $request->observaciones,
        ]);

        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Familiar registrado correctamente.');
    }

    public function deleteFamiliar(Empleado $empleado, EmpleadoFamiliar $familiar)
    {
        abort_unless($familiar->empleado_id === $empleado->id, 403);
        $familiar->delete();
        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Familiar eliminado.');
    }

    // ── HISTORIAL DE CARGOS ───────────────────────────────────────
    public function addHistorial(Request $request, Empleado $empleado)
    {
        $request->validate([
            'cargo_id'           => 'required|exists:cargos,id',
            'unidad_ejecutora_id'=> 'nullable|exists:unidades_ejecutoras,id',
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'nullable|date|after_or_equal:fecha_inicio',
            'motivo_cambio'      => 'nullable|string|max:300',
        ]);

        $empleado->historialCargos()->create([
            'cargo_id'           => $request->cargo_id,
            'unidad_ejecutora_id'=> $request->unidad_ejecutora_id,
            'fecha_inicio'       => $request->fecha_inicio,
            'fecha_fin'          => $request->fecha_fin,
            'motivo_cambio'      => $request->motivo_cambio,
            'registrado_por'     => auth()->id(),
        ]);

        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Registro de cargo agregado al historial.');
    }

    public function deleteHistorial(Empleado $empleado, EmpleadoHistorialCargo $historial)
    {
        abort_unless($historial->empleado_id === $empleado->id, 403);
        $historial->delete();
        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Registro eliminado del historial.');
    }

    // ── FORMACIÓN ADICIONAL ───────────────────────────────────────
    public function addFormacion(Request $request, Empleado $empleado)
    {
        $request->validate([
            'tipo'            => 'required|in:curso,certificacion,diplomado,postgrado,maestria,doctorado,idioma,otro',
            'nombre'          => 'required|string|max:200',
            'institucion'     => 'nullable|string|max:200',
            'pais'            => 'nullable|string|max:80',
            'fecha_inicio'    => 'nullable|date',
            'fecha_fin'       => 'nullable|date|after_or_equal:fecha_inicio',
            'duracion_horas'  => 'nullable|integer|min:1|max:9999',
            'nivel_idioma'    => 'nullable|in:basico,intermedio,avanzado,nativo',
            'numero_registro' => 'nullable|string|max:100',
            'descripcion'     => 'nullable|string|max:1000',
            'documento'       => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $data = $request->only([
            'tipo','nombre','institucion','pais',
            'fecha_inicio','fecha_fin','duracion_horas',
            'nivel_idioma','numero_registro','descripcion',
        ]);
        $data['en_curso']       = $request->boolean('en_curso');
        $data['registrado_por'] = auth()->id();

        if ($request->hasFile('documento')) {
            $data['documento_path'] = $request->file('documento')
                ->store('empleados_formaciones', 'local');
        }

        $empleado->formaciones()->create($data);

        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Formación registrada correctamente.');
    }

    public function deleteFormacion(Empleado $empleado, EmpleadoFormacion $formacion)
    {
        abort_unless($formacion->empleado_id === $empleado->id, 403);
        if ($formacion->documento_path) {
            Storage::disk('local')->delete($formacion->documento_path);
        }
        $formacion->delete();
        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Formación eliminada.');
    }

    public function downloadDocFormacion(Empleado $empleado, EmpleadoFormacion $formacion)
    {
        abort_unless($formacion->empleado_id === $empleado->id, 403);
        if (!$formacion->documento_path || !Storage::disk('local')->exists($formacion->documento_path)) {
            return back()->with('error', 'No hay documento registrado para esta formación.');
        }
        $nombre = 'DOC_' . str_replace(' ', '_', $formacion->nombre);
        $ext    = pathinfo($formacion->documento_path, PATHINFO_EXTENSION);
        return Storage::disk('local')->download($formacion->documento_path, $nombre . '.' . $ext);
    }

    // ── BONIFICACIONES / CONCEPTOS INDIVIDUALES ───────────────────
    public function addBonificacion(Request $request, Empleado $empleado)
    {
        $request->validate([
            'concepto_nomina_id' => 'required|exists:conceptos_nomina,id',
            'monto'              => 'nullable|numeric|min:0',
            'observaciones'      => 'nullable|string|max:300',
        ]);

        $empleado->bonificaciones()->create([
            'concepto_nomina_id' => $request->concepto_nomina_id,
            'monto'              => $request->monto,
            'observaciones'      => $request->observaciones,
            'activo'             => true,
            'registrado_por'     => auth()->id(),
        ]);

        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Concepto individual asignado correctamente al trabajador.');
    }

    public function deleteBonificacion(Empleado $empleado, EmpleadoBonificacion $bonificacion)
    {
        abort_unless($bonificacion->empleado_id === $empleado->id, 403);
        $bonificacion->delete();
        return redirect()->route('nomina.empleados.show', $empleado)
            ->with('success', 'Concepto individual retirado.');
    }
}
