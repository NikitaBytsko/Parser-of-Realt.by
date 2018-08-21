<!DOCTYPE html>
<html>
<head>
    <title>Office</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">

    <h1><a href="{{ URL::to('office') }}">Все объявления</a></h1>

    <h2>Контактная информация:: <?php dump(json_decode($office->contact))?></h2>
    <h2>Местоположение:: <?php dump(json_decode($office->location))?></h2>
    <h2>Параметры объекта: <?php dump(json_decode($office->options))?></h2>
    <h2>Условия продажи / аренды: <?php dump(json_decode($office->conditions))?></h2>
</div>
</body>
</html>
