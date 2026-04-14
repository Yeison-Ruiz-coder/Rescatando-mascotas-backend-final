<?php $__env->startSection('content'); ?>

<style>
    body {
        background: url('/images/fondo-azul.jpg') no-repeat center center fixed;
        background-size: cover;
        font-family: 'Poppins', sans-serif;
    }

    .contenedor-formulario {
        background: rgba(255,255,255,0.85);
        border-radius: 15px;
        padding: 30px;
        max-width: 1100px;
        margin: auto;
        backdrop-filter: blur(10px);
        box-shadow: 0 0 15px rgba(0,0,0,0.15);
    }

    .titulo {
        font-size: 1.7rem;
        margin-bottom: 20px;
        font-weight: 700;
        text-align: center;
        color: #006b8f;
    }

    .form-control, .form-select {
        border-radius: 10px;
    }

    .btn-registrar {
        background-color: #ff4f8b;
        color: white;
        font-weight: bold;
        border-radius: 25px;
        padding: 12px 20px;
        border: none;
        width: 100%;
        transition: 0.3s;
    }

    .btn-registrar:hover {
        background-color: #e93c79;
    }

    /* Galería */
    .galeria img {
        width: 100%;
        height: 130px;
        border-radius: 10px;
        object-fit: cover;
    }

    #mensajeCarga {
        display: none;
        margin-top: 15px;
        font-weight: bold;
        color: #1ca58f;
    }
</style>

<div class="container py-5">
    <div class="contenedor-formulario">

        <h2 class="titulo">REGISTRA TU MASCOTA</h2>

        <div class="row">
            <!-- Formulario -->
            <div class="col-md-6">

                <label class="form-label mt-2">Nombre</label>
                <input type="text" class="form-control">

                <label class="form-label mt-3">Especie</label>
                <select class="form-select">
                    <option>Canino</option>
                    <option>Felino</option>
                </select>

                <label class="form-label mt-3">Raza</label>
                <select class="form-select">
                    <option>Doberman</option>
                    <option>Labrador</option>
                    <option>Golden</option>
                </select>

                <label class="form-label mt-3">Edad</label>
                <input type="number" class="form-control">

                <label class="form-label mt-3">Fundación de mascotas</label>
                <input type="text" class="form-control">

                <button class="btn-registrar mt-4" onclick="registrarMascota()">
                    Registrar
                </button>

                <div id="mensajeCarga">
                    ✔ Registrando mascota... Por favor espera un momento.
                </div>
            </div>

            <!-- Galería -->
            <div class="col-md-6 galeria">
                <img src="https://i.pinimg.com/736x/0d/8f/47/0d8f476ec54c3d67413784fa1ff08894.jpg">
                <img src="https://i.pinimg.com/1200x/c6/3b/c2/c63bc2eb656f85421a7dddb98c065b08.jpg" class="mt-3">
                <img src="https://i.pinimg.com/1200x/37/51/54/3751548eb0139e8cb47b998adf13bb1b.jpg" class="mt-3">
            </div>
        </div>
    </div>
</div>

<script>
function registrarMascota() {
    document.getElementById('mensajeCarga').style.display = 'block';

    setTimeout(() => {
        alert("Mascota registrada con éxito (simulación).");
    }, 2500);
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('public.layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\Rescatando-mascotas-backend-final\resources\views\public\fundaciones\registrarMascota.blade.php ENDPATH**/ ?>