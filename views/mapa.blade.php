@extends('app')
@section('encabezado', "Destino de Reparto")
@section('contenido')
<div class="container mt-3 ">
    <div class="d-flex justify-content-center">
        <div id="miMapa" data-lat="{{ $lat }}" data-lon="{{ $lon }}" style="width: 650px; height: 420px;"></div>
        <div class="mt-r">
        </div>
    </div>
    <div class="d-flex justify-content-center mt-3">
        <a href="{{ $_SERVER['PHP_SELF'] }}" class="btn btn-warning">Volver</a>
    </div>
</div>
@endsection
@section('scripts')
<script src="js/mapa.js"></script>
<script async
    src="https://maps.googleapis.com/maps/api/js?key={{ $_ENV['MAP_API_KEY'] }}&loading=async&callback=loadMapScenario">
</script>
@endsection


