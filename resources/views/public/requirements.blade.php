@extends('layouts.app')
@section('title', 'Requisitos')
@section('content')
    <section class="section">
        <p class="kicker">La carpeta</p>
        <h1>Requisitos</h1>
        <div class="steps">
            <article class="step"><b>1</b><h3>Cédula</h3><p>Copia actualizada.</p></article>
            <article class="step"><b>2</b><h3>RIF</h3><p>RIF actualizado.</p></article>
            <article class="step"><b>3</b><h3>Licencia y 2DA</h3><p>Licencia más el certificado.</p></article>
            <article class="step"><b>4</b><h3>Constancia</h3><p>Constancia de trabajo.</p></article>
            <article class="step"><b>5</b><h3>Ubicación</h3><p>Se verifica después de los papeles y deja la preaprobación.</p></article>
        </div>
        <div class="panel">
            <h2>Dos formas de empezar</h2>
            <p><strong>Subir y esperar la llamada.</strong> Cargas los cuatro archivos desde la moto que elegiste. La sede preevalúa y te contacta.</p>
            <p><strong>Llevar la carpeta.</strong> Llevas esos mismos papeles a la sede. El vendedor recibe la carpeta, y después se verifica la ubicación.</p>
            <a class="btn" href="{{ route('catalog.index') }}">Elegir moto</a>
        </div>
    </section>
@endsection
