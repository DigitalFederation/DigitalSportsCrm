<?php

use App\Livewire\Widgets\LocationPicker;
use Livewire\Livewire;

test('location picker component can be rendered', function () {
    Livewire::test(LocationPicker::class)
        ->assertStatus(200)
        ->assertSee('Set on Map')
        ->assertSee('Latitude')
        ->assertSee('Longitude');
});

test('location picker component accepts initial coordinates', function () {
    Livewire::test(LocationPicker::class, [
        'initialLat' => 40.7128,
        'initialLng' => -74.0060,
    ])
        ->assertSet('latitude', 40.7128)
        ->assertSet('longitude', -74.0060)
        ->assertSee('(Selected)');
});

test('location picker component can handle coordinate selection', function () {
    Livewire::test(LocationPicker::class)
        ->call('handleLocationSelected', 40.7128, -74.0060)
        ->assertSet('latitude', 40.7128)
        ->assertSet('longitude', -74.0060)
        ->assertDispatched('coordinates-updated', [
            'latitude' => 40.7128,
            'longitude' => -74.0060,
        ]);
});

test('location picker component validates coordinate ranges', function () {
    Livewire::test(LocationPicker::class)
        ->call('handleLocationSelected', 91, 181) // Invalid coordinates (out of range)
        ->assertHasErrors(['location'])
        ->assertSet('latitude', null)
        ->assertSet('longitude', null);
});

test('location picker component can open and close modal', function () {
    Livewire::test(LocationPicker::class)
        ->assertSet('isModalOpen', false)
        ->call('openModal')
        ->assertSet('isModalOpen', true)
        ->assertDispatched('openMapModal')
        ->call('closeModal')
        ->assertSet('isModalOpen', false)
        ->assertDispatched('modalClosed');
});

test('location picker component can clear location', function () {
    Livewire::test(LocationPicker::class)
        ->set('latitude', 40.7128)
        ->set('longitude', -74.0060)
        ->call('clearLocation')
        ->assertSet('latitude', null)
        ->assertSet('longitude', null)
        ->assertDispatched('coordinates-updated', [
            'latitude' => null,
            'longitude' => null,
        ]);
});

test('location picker component uses custom field names', function () {
    Livewire::test(LocationPicker::class, [
        'latField' => 'custom_lat',
        'lngField' => 'custom_lng',
    ])
        ->assertSet('latFieldName', 'custom_lat')
        ->assertSet('lngFieldName', 'custom_lng')
        ->assertSee('custom_lat')
        ->assertSee('custom_lng');
});

test('location picker component shows clear button when coordinates exist', function () {
    Livewire::test(LocationPicker::class)
        ->assertDontSee('Clear Location')
        ->set('latitude', 40.7128)
        ->set('longitude', -74.0060)
        ->assertSee('Clear Location');
});

test('location picker component accepts coordinate values', function () {
    Livewire::test(LocationPicker::class)
        ->call('handleLocationSelected', 40.712812, -74.006099)
        ->assertSet('latitude', 40.712812)
        ->assertSet('longitude', -74.006099);
});
