@extends('admin.layout')

@section('admin-content')
    <h1 class="text-2xl font-bold mb-4">Редактировать партнёра</h1>
    <form action="{{ route('admin.partners.update', $partner) }}" method="POST" class="bg-white p-6 rounded-2xl shadow max-w-lg">
        @csrf @method('PUT')
        <div class="mb-4">
            <label class="block mb-2">ФИО</label>
            <input type="text" name="full_name" value="{{ old('full_name', $partner->full_name) }}" class="w-full border rounded p-2" required>
        </div>
        <div class="mb-4">
            <label class="block mb-2">Телефон</label>
            <input type="text" name="phone" value="{{ old('phone', $partner->phone) }}" class="w-full border rounded p-2">
        </div>
        <div class="mb-4">
            <label class="block mb-2">Email</label>
            <input type="email" name="email" value="{{ old('email', $partner->email) }}" class="w-full border rounded p-2">
        </div>
        <button type="submit" class="bg-amber-500 text-white px-6 py-2 rounded-full">Обновить</button>
    </form>
@endsection