<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <h5 class="fw-bold mb-3">
                    <i class="fas fa-paw me-2"></i>Rescatando Mascotas
                </h5>
                <p class="text-light">Dedicados a rescatar, rehabilitar y encontrar hogares amorosos para animales en situación de vulnerabilidad.</p>
                <div class="social-links">
                    <a href="#" class="text-light me-3"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="fw-bold">Enlaces Rápidos</h6>
                <ul class="list-unstyled">
                    <li><a href="<?php echo e(url('/')); ?>" class="text-light text-decoration-none">Inicio</a></li>
                    <li><a href="<?php echo e(route('public.mascotas.index')); ?>" class="text-light text-decoration-none">Adoptar</a></li>
                    <li><a href="<?php echo e(route('public.rescates.create')); ?>" class="text-light text-decoration-none">Rescatar</a></li>
                    <li><a href="<?php echo e(route('public.donaciones.create')); ?>" class="text-light text-decoration-none">Donar</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="fw-bold">Servicios</h6>
                <ul class="list-unstyled">
                    <li><a href="#" class="text-light text-decoration-none">Adopción</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Rescate</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Veterinaria</a></li>
                    <li><a href="#" class="text-light text-decoration-none">Voluntariado</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h6 class="fw-bold">Contacto</h6>
                <ul class="list-unstyled">
                    <li class="mb-2">
                        <i class="fas fa-map-marker-alt me-2"></i>
                        <span class="text-light">Calle Principal, 123</span>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-phone me-2"></i>
                        <a href="tel:+34123456789" class="text-light text-decoration-none">+34 123 456 789</a>
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:info@rescatandomascotas.local" class="text-light text-decoration-none">info@rescatandomascotas.local</a>
                    </li>
                </ul>
            </div>
        </div>

        <hr class="bg-light">

        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0 text-light">&copy; 2024 Rescatando Mascotas. Todos los derechos reservados.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="#" class="text-light text-decoration-none me-3">Política de Privacidad</a>
                <a href="#" class="text-light text-decoration-none">Términos de Servicio</a>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\home\partials\footer.blade.php ENDPATH**/ ?>