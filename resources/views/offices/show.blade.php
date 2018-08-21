<!DOCTYPE html>
<html>
<head>
    <title>Office</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">

    <h1><a href="{{ URL::to('office') }}">Все объявления</a></h1>

    @foreach ($office->images as $image)
        <img style="max-width: 300px" src="{{$image->image}}">
    @endforeach

    <h2>Контактная информация: @dump(json_decode($office->contact)) </h2>
    <h2>Местоположение::@dump(json_decode($office->location))</h2>
    <h2>Параметры объекта: @dump(json_decode($office->options))</h2>
    <h2>Условия продажи / аренды: @dump(json_decode($office->conditions))</h2>

</div>
</body>
</html>
