@extends('admin.layout')

@section('admin-content')
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Категории</h1>
        <a href="{{ route('admin.categories.create') }}" class="bg-amber-500 text-white px-4 py-2 rounded-full">Добавить</a>
    </div>
    <div class="bg-white rounded-xl shadow overflow-hidden">
        @foreach($categories as $category)
            <div class="p-4 border-b flex justify-between items-center">
                <div>
                    <span class="font-semibold">{{ $category->name }}</span>
                    <span class="text-xs text-gray-500 ml-2">
                        ({{ $category->show_in_catalog ? 'Еда' : '' }}{{ $category->show_in_catalog && $category->show_in_ready_eat ? ', ' : '' }}{{ $category->show_in_ready_eat ? 'Продукты и товары' : '' }})
                    </span>
                    @if($category->children->count())
                        <ul class="ml-4 mt-1 text-sm text-gray-600">
                            @foreach($category->children as $child)
                                <li>- {{ $child->name }} <span class="text-xs text-gray-400">({{ $child->show_in_catalog ? 'Еда' : '' }}{{ $child->show_in_catalog && $child->show_in_ready_eat ? ', ' : '' }}{{ $child->show_in_ready_eat ? 'Продукты и товары' : '' }})</span></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="space-x-2">
                    <a href="{{ route('admin.categories.edit', $category) }}" class="text-blue-600">Ред.</a>
                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button class="text-red-600" onclick="return confirm('Удалить?')">Уд.</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
@endsection