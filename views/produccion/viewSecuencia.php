<?php
require_once '../../contenido.php';
require_once '../../models/produccion/ActionModel.php';

$conexion = new Conexion();
$conn = $conexion->getConexion(); 

$produccionModel = new ActionModel();

$personas = $produccionModel->getPersonasActivas();
$operaciones = $produccionModel->getOperacionesByDetalleOp($id);



$id = $secuencia['iddetop'];
$stmt = $conn->prepare("
    SELECT op.idcliente 
    FROM detalleop det
    JOIN ordenesproduccion op ON det.idop = op.idop
    WHERE det.iddetop = :iddetop
");
$stmt->bindParam(':iddetop', $id, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    $idcliente = $result['idcliente'];
} else {
    $idcliente = null;
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-0">Control de Producción</h1>
            <p class="text-muted">Gestión de registros de producción</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $host ?>/views/produccion/indexP.php?cliente_id=<?= htmlspecialchars($idcliente) ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Regresar
            </a>
            <button class="btn btn-primary" 
                    data-bs-toggle="modal" 
                    data-bs-target="#nuevoRegistroModal"
                    data-iddetop="<?= htmlspecialchars($secuencia['iddetop']) ?>">
                <i class="bi bi-plus-circle"></i> Nuevo Registro
            </button>
        </div>
    </div>

    <div class="card mb-4">
    <div class="card-body">
        <form>
            <h2>Filtro</h2>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="fecha" 
                        name="fecha"
                        min="<?= htmlspecialchars($secuencia['sinicio']) ?>"
                        max="<?= htmlspecialchars($secuencia['sfin']) ?>"
                        required>
                </div>
                <div class="col-md-6">
                    <label for="estadoPago" class="form-label">Estado de Pago</label>
                    <select class="form-select" id="estadoPago" name="estadoPago">
                        <option value="Todos" selected>Todos</option>
                        <option value="Pagado">Pagado</option>
                        <option value="Pendiente">Pendiente</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 text-end">
                <button type="button" class="btn btn-secondary" id="limpiarFiltros">Limpiar Filtros</button>
            </div>
        </form>
    </div>
</div>


    <div class="table-responsive">
    <table class="table table-striped table-hover table-bordered align-middle">
        <thead class="table-light">
            <tr>
                <th scope="col">Persona</th>
                <th scope="col">Operación</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Fecha</th>
                <th scope="col">Paga</th>
                <th scope="col">Fecha Pagado</th>
            </tr>
        </thead>
        <tbody>
        <?php
            $produccion = $produccionModel->getProduccionByDOP($id);
            foreach ($produccion as $produccion): ?>
                <tr>
                    <td><?= htmlspecialchars($produccion['nombrePersona']) ?></td>
                    <td><?= htmlspecialchars($produccion['tipoOperacion']) ?></td>
                    <td><?= htmlspecialchars($produccion['cantidadproducida']) ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($produccion['fecha']))) ?></td>
                    <td>
                        <?php if ($produccion['pagado'] == 0): ?>
                            <span class="badge bg-warning text-dark">Pendiente</span>
                        <?php else: ?>
                            <span class="badge bg-success">Pagado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $produccion['fechapagopersona'] 
                            ? htmlspecialchars(date('d/m/Y', strtotime($produccion['fechapagopersona']))) 
                            : '<span class="text-muted">No pagado</span>' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($produccion)): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted">No hay producción registrada.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</div>


<div class="modal fade" id="nuevoRegistroModal" tabindex="-1" aria-labelledby="nuevoRegistroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="nuevoRegistroModalLabel">Registrar Producción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoRegistro" action="<?= $host ?>/views/produccion/indexP.php?action=createProduccion" method="POST">
                    <div class="mb-3">
                        <input type="hidden" name="iddetop" id="opIdInput" value="<?= htmlspecialchars($secuencia['iddetop']) ?>">

                        <label for="idpersona" class="form-label">Persona</label>
                            <select class="form-select" id="idpersona" name="idpersona" required>
                                <option value="" selected>Seleccione una persona</option>
                                <?php foreach ($personas as $persona): ?>
                                    <option value="<?= htmlspecialchars($persona['idpersona']) ?>">
                                        <?= htmlspecialchars($persona['nombres'] . ' ' . $persona['apellidos']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                    </div>
                    <div class="mb-3">
                        <label for="iddetop_operacion" class="form-label">
                            Tipo de Operación
                        </label>
                        <span id="cantidadOperacion" style="font-weight: bold; margin-left: 10px;">Cantidad a realizar</span> 
                        <select class="form-select" id="iddetop_operacion" name="iddetop_operacion" required>
                            <option value="" selected>Seleccione una operación</option>
                            <?php foreach ($operaciones as $operacion): ?>
                            <option value="<?= htmlspecialchars($operacion['iddetop_operacion']) ?>"
                            data-cantidad="<?= htmlspecialchars($operacion['cantidaO'] ?? 'No disponible') ?>"> 
                        <?= htmlspecialchars($operacion['operacion']) ?>
                    </option>

                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidadproducida" class="form-label">Cantidad Producida</label>
                        <input type="number" class="form-control" id="cantidadproducida" name="cantidadproducida" min="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="submitBtn">Registrar</button>
            </div>
        </div>
    </div>
</div>


<script>
    document.querySelector("#submitBtn").addEventListener("click", async (event) => {
        event.preventDefault(); 

        const confirmacion = await Swal.fire({
            title: '¿Está seguro de registrar esta producción?',
            text: 'Verifique que los datos sean correctos antes de proceder.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar',
        });

        if (confirmacion.isConfirmed) {
            Swal.fire({
                title: 'Producción Registrada',
                text: 'La producción se ha registrado exitosamente.',
                icon: 'success',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                document.getElementById('formNuevoRegistro').submit();
            });
        } else {
            Swal.fire({
                title: 'Registro Cancelado',
                text: 'El registro ha sido cancelado.',
                icon: 'info',
                confirmButtonText: 'Aceptar'
            });
        }
    });
</script>


<script>

document.getElementById('formNuevoRegistro').addEventListener('submit', function(event) {
    var cantidadProducida = parseInt(document.getElementById('cantidadproducida').value, 10);
    var cantidadDisponible = parseInt(
        document.getElementById('iddetop_operacion').selectedOptions[0].getAttribute('data-cantidad'),
        10
    );

    if (cantidadProducida > cantidadDisponible) {
        alert('La cantidad producida no puede exceder la cantidad disponible.');
        event.preventDefault(); // Detener el envío del formulario
    }
});


    document.getElementById('nuevoRegistroModal').addEventListener('show.bs.modal', function (event) {
    
        const button = event.relatedTarget;

        const iddetop = button.getAttribute('data-iddetop');

        const hiddenInput = document.getElementById('opIdInput');
        hiddenInput.value = iddetop;
    });

</script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const selectOperacion = document.getElementById("iddetop_operacion");
        const cantidadOperacion = document.getElementById("cantidadOperacion");

        selectOperacion.addEventListener("change", function () {
            const selectedOption = selectOperacion.options[selectOperacion.selectedIndex];
            const cantidad = selectedOption.getAttribute("data-cantidad");

            if (cantidad) {
                cantidadOperacion.textContent = `Cantidad: ${cantidad}`;
            } else {
                cantidadOperacion.textContent = "Selecciona una operación";
            }
        });
    });
</script>

<script>

    


/* Filtro */ 
document.addEventListener('DOMContentLoaded', function() {
    const fechaInput = document.getElementById('fecha');
    const estadoPagoSelect = document.getElementById('estadoPago');
    const tabla = document.querySelector('table tbody');
    const filasOriginales = Array.from(tabla.querySelectorAll('tr'));

    function filtrarTabla() {
        const fechaSeleccionada = fechaInput.value; 
        const estadoSeleccionado = estadoPagoSelect.value;

        let filasFiltradas = [...filasOriginales];

        if (fechaSeleccionada) {
            const [anio, mes, dia] = fechaSeleccionada.split('-');
            const fechaFiltro = `${dia}/${mes}/${anio}`;

            filasFiltradas = filasFiltradas.filter(fila => {
                const fechaCelda = fila.cells[3].textContent.trim(); 
                if (fechaCelda === 'No hay producción registrada.') return false;

                return fechaCelda === fechaFiltro;
            });
        }

        if (estadoSeleccionado !== 'Todos') {
            filasFiltradas = filasFiltradas.filter(fila => {
                const estadoCelda = fila.cells[4].textContent.trim();
                return estadoCelda === estadoSeleccionado;
            });
        }

        tabla.innerHTML = '';

        if (filasFiltradas.length === 0) {
            const filaSinDatos = document.createElement('tr');
            filaSinDatos.innerHTML = '<td colspan="6" class="text-center text-muted">No se encontraron registros con los filtros seleccionados.</td>';
            tabla.appendChild(filaSinDatos);
        } else {
            filasFiltradas.forEach(fila => tabla.appendChild(fila.cloneNode(true)));
        }
    }


    fechaInput.addEventListener('change', filtrarTabla);
    estadoPagoSelect.addEventListener('change', filtrarTabla);

    const limpiarBtn = document.getElementById('limpiarFiltros');
    limpiarBtn.onclick = function() {
        fechaInput.value = '';
        estadoPagoSelect.value = 'Todos';
        filtrarTabla();
    };

    function formatearFechaParaInput(fecha) {
        const [dia, mes, anio] = fecha.split('/');
        return `${anio}-${mes.padStart(2, '0')}-${dia.padStart(2, '0')}`;
    }
});
</script>

<?php require_once '../../footer.php'; ?>
