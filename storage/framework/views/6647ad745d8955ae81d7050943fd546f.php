<form action="<?php echo e(route('mascotas.store')); ?>" method="POST">
    <?php echo csrf_field(); ?>

    <label>Nombre de la mascota</label>
    <input type="text" name="Nombre_mascota" required>

    <label>Especie</label>
    <input type="text" name="Especie">

    <label>Raza</label>
    <input type="text" name="Raza" required>

    <label>Edad aproximada</label>
    <input type="number" name="Edad_aprox" required>

    <label>Género</label>
    <input type="text" name="Genero" required>

    <label>Estado</label>
    <select name="estado" required>
        <option value="Adoptado">Adoptado</option>
        <option value="En adopcion">En adopción</option>
        <option value="Rescatada">Rescatada</option>
    </select>

    <label>Lugar de rescate</label>
    <input type="text" name="Lugar_rescate" required>

    <label>Descripción</label>
    <textarea name="Descripcion" required></textarea>

    <label>Foto (ruta o URL)</label>
    <input type="text" name="Foto" required>

    <label>Vacunas</label>
    <input type="text" name="vacunas" required>

    <label>Fecha de ingreso</label>
    <input type="date" name="Fecha_ingreso" required>

    <label>Fecha de salida</label>
    <input type="date" name="Fecha_salida">

    <button type="submit">Registrar mascota</button>
</form>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\fundaciones\create.blade.php ENDPATH**/ ?>