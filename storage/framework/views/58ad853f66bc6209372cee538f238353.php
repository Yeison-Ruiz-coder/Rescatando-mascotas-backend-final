<form method="POST" action="<?php echo e(route('posts.store')); ?>">
    <?php echo csrf_field(); ?>
    <input type="text" name="title" placeholder="Título en español" required>
    <textarea name="content" placeholder="Contenido en español" required></textarea>
    <textarea name="excerpt" placeholder="Extracto en español"></textarea>
    <button type="submit">Guardar Post</button>
</form>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\posts\create.blade.php ENDPATH**/ ?>