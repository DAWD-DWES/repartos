@extends('app')
@section('encabezado', "Formulario de Reparto")
@section('contenido')
<div class="container mt-3">
    <div class="d-flex justify-content-center">
        <div class="card" style='width:28rem;'>
            <div class="card-header">
                <h3><i class="bi bi-cart-fill me-2"></i>Crear Envio</h3>
            </div>
            <div class="card-body">
                <form id="form-reparto" name="form-reparto" method="POST" action="{{ $_SERVER['PHP_SELF'] }}">
                    <div class="input-group my-2">
                        <span class="input-group-text"><i class="bi bi-building"></i></span>
                        <input type="text" class="form-control" placeholder="Dirección" id="direccion" name="direccion" required>
                    </div>
                    <div class="input-group mt-1">
                        <input value="Ver Coordenadas" name="ver-coordenadas" class="btn btn-info mr-2" id="ver-coordenadas">
                    </div>
                    <div class="input-group my-2">
                        <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                        <input type="text" class="form-control" placeholder="Latitud" id="lat" required name="lat" readonly>
                    </div>
                    <div class="input-group my-2">
                        <span class="input-group-text"><i class="bi bi-geo-alt-fill"></i></span>
                        <input type="text" class="form-control" placeholder="longitud" id="lon" name="lon" required readonly>
                    </div>
                    <div class="input-group my-2">
                        <span class="input-group-text"><i class="bi bi-box2"></i></span>
                        <input type="text" class="form-control" placeholder="Producto" id="producto" name="producto" required>
                    </div>
                    <div class="input-group">
                        <input type="hidden" name="lista-reparto-id" value="{{ $listaRepartoId }}">
                        <input type="submit" class="btn btn-info mr-2" id="nuevo_reparto" value="Nuevo Envio" name="nuevo-reparto" disabled>
                        <a href="{{ $_SERVER['PHP_SELF'] }}" class="btn btn-success">Volver</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script src="js/coordenadas.js"></script>
@endsection
