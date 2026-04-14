<?php $__env->startSection('title', 'Solicitar Adopción - ' . $mascota->nombre_mascota); ?>

<?php $__env->startPush('styles'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=<?php echo e(time()); ?>">
<link rel="stylesheet" href="<?php echo e(asset('css/pages/adopciones/solicitudes.css')); ?>?v=<?php echo e(time()); ?>">
<style>
    /* Estilos de respaldo por si el CSS no carga */
    :root {
        --color-turquesa: #1B8E95;
        --color-fucsia: #D94A8B;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- BREADCRUMB -->
            <?php echo $__env->make('public.adopciones.partials.breadcrumb', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

            <!-- CARD PRINCIPAL -->
            <div class="card card-solicitud animacion-entrada">
                <!-- HEADER -->
                <?php echo $__env->make('public.adopciones.partials.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                <div class="card-body p-4">
                    <!-- INFORMACIÓN DE LA MASCOTA -->
                    <?php echo $__env->make('public.adopciones.partials.mascota_info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                    <!-- SEPARADOR -->
                    <hr class="separador-seccion">

                    <!-- FORMULARIO DE SOLICITUD -->
                    <form action="<?php echo e(route('public.adopciones.solicitar.store')); ?>" method="POST" id="formSolicitud">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="mascota_id" value="<?php echo e($mascota->id); ?>">

                        <!-- SECCIÓN DATOS PERSONALES -->
                        <?php echo $__env->make('public.adopciones.partials.form_datos_personales', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- SECCIÓN INFORMACIÓN ADICIONAL -->
                        <?php echo $__env->make('public.adopciones.partials.form_informacion_adicional', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- COMPROMISOS DE ADOPCIÓN -->
                        <?php echo $__env->make('public.adopciones.partials.compromisos', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        <!-- BOTONES DE ACCIÓN -->
                        <?php echo $__env->make('public.adopciones.partials.acciones', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    </form>
                </div>
            </div>

            <!-- INFORMACIÓN DEL PROCESO -->
            <?php echo $__env->make('public.adopciones.partials.proceso_info', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formSolicitud');
    const btnEnviar = document.getElementById('btnEnviarSolicitud');
    const checkboxes = [
        'compromiso_cuidado',
        'compromiso_esterilizacion',
        'compromiso_seguimiento'
    ];

    if (form) {
        form.addEventListener('submit', function(e) {
            let todosMarcados = true;
            let errores = [];

            // Validar checkboxes
            for (let checkboxId of checkboxes) {
                const checkbox = document.getElementById(checkboxId);
                if (!checkbox || !checkbox.checked) {
                    todosMarcados = false;
                    break;
                }
            }

            if (!todosMarcados) {
                e.preventDefault();
                errores.push('Debes aceptar todos los compromisos de adopción');
            }

            // Validar motivo
            const motivo = document.getElementById('motivo_adopcion');
            if (motivo && motivo.value.trim().length < 20) {
                e.preventDefault();
                errores.push('El motivo debe tener al menos 20 caracteres');
                motivo.focus();
            }

            // Validar campos requeridos
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    e.preventDefault();
                    const label = field.previousElementSibling?.innerText || 'Campo';
                    errores.push(`El campo ${label} es requerido`);
                }
            });

            // Mostrar errores con SweetAlert
            if (errores.length > 0) {
                e.preventDefault();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Verifica los siguientes puntos:',
                        html: '<ul style="text-align: left;">' +
                              errores.map(err => `<li>${err}</li>`).join('') +
                              '</ul>',
                        confirmButtonColor: '#1B8E95',
                        confirmButtonText: 'Entendido'
                    });
                } else {
                    alert('Por favor, completa todos los campos requeridos:\n\n' + errores.join('\n'));
                }
                return;
            }

            // Cambiar estado del botón al enviar
            if (btnEnviar && errores.length === 0) {
                btnEnviar.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando solicitud...';
                btnEnviar.classList.add('cargando');
                btnEnviar.disabled = true;
            }
        });
    }

    // Debug: Verificar si el CSS está cargando
    console.log('CSS loaded:', document.styleSheets.length);
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\adopciones\solicitar.blade.php ENDPATH**/ ?>