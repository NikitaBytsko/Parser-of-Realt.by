<!DOCTYPE html>
<html>
<head>
    <title>Look! I'm CRUDding</title>
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">

    <h1><a href="{{ URL::to('office') }}">Все объявления</a></h1>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <td>ID</td>
            <td>Код объявления</td>
            <td>Заголовок</td>
            <td>Цена в BYN<br>
                <a href="{{ URL::to('office?sort_value=min') }}">По возрастанию</a>
                <a href="{{ URL::to('office?sort_value=max') }}">По убыванию</a>
            </td>
            <td>
                {{ Form::open(array('url' => 'office', 'method' => 'post')) }}
                {{csrf_field()}}

                <div class="form-group">
                    {{ Form::label('address', 'Адрес/Область') }}
                    {{ Form::text('address', \Illuminate\Support\Facades\Input::old('address'), array('class' => 'form-control')) }}
                </div>

                {{ Form::submit('Поиск', array('class' => 'btn btn-primary')) }}

                {{ Form::close() }}

            </td>
            <td>Действия</td>
        </tr>
        </thead>
        <tbody>
        @foreach($offices as $key => $value)
            <tr>
                <td>{{ $value->id }}</td>
                <td>{{ $value->code }}</td>
                <td>{{ $value->title }}</td>
                <td>{{ $value->price }}</td>
                <td>{{ $value->address }}</td>
                <td>
                    <a class="btn btn-small btn-success" href="{{ URL::to('office/' . $value->id) }}">Просмотреть
                        объявление</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
</body>
</html>
