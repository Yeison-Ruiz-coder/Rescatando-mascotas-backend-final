<h1><?php echo e($post->title); ?></h1> 

<div>
    <a href="<?php echo e(route('locale.switch', 'es')); ?>">Español</a> |
    <a href="<?php echo e(route('locale.switch', 'en')); ?>">English</a>
</div>

<article>
    <?php echo e($post->content); ?>

</article>

<?php if($post->excerpt): ?>
    <p><?php echo e($post->excerpt); ?></p>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\posts\show.blade.php ENDPATH**/ ?>