@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Orders</h1>

        <div class="mb-4 d-flex justify-content-between">
            <div>
                <form action="{{ route('orders.generate') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary ml-2">Сгенерировать 1000 заказов</button>
                </form>
                <form action="{{ route('orders.destroy-table') }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-secondary ml-2">Очистить таблицу</button>
                </form>
                    <form action="{{ route('orders.set-sheet') }}" method="POST" class="form-inline">
                        @csrf
                        <div class="form-group mr-2">
                            <input type="url" name="sheet_url" class="form-control" 
                                   placeholder="Google Sheet URL" required
                                   value="{{$url}}">
                        </div>
                        <button type="submit" class="btn btn-primary">Сохранить</button>
                    </form>
            </div>

            <form action="{{ route('orders.index') }}" method="GET" class="form-inline">
                <div class="form-group ">
                    <label for="count" class="sr-only">Записей на странице</label>
                    <input type="number" class="form-control" id="count" name="count"
                        value="{{ request('count') }}">
                </div>
                <button type="submit" class="btn btn-primary mb-2">Выполнить</button>
            </form>
        </div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>{{ $order->id }}</td>
                        <td>
                            {{ $order->status }}
                        </td>
                        <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $order->updated_at->format('Y-m-d H:i') }}</td>
                        <td>
                            <form action="{{ route('orders.destroy', $order) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Вы уверены?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="d-flex justify-content-center">
            {{ $orders->appends(request()->input())->links() }}
        </div>
    </div>
@endsection
