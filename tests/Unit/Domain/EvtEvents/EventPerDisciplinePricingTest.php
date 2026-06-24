<?php

use App\Enums\EvtEventFeeTypeEnum;
use Domain\EvtEvents\Models\Event;
use Domain\EvtEvents\Models\Pricing;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

it('detects when event has per-discipline pricing', function () {
    $event = Mockery::mock(Event::class)->makePartial();

    $hasManyMock = Mockery::mock(HasMany::class);
    $hasManyMock->shouldReceive('where')
        ->with('is_active', true)
        ->andReturnSelf();
    $hasManyMock->shouldReceive('where')
        ->with('price_type', EvtEventFeeTypeEnum::PER_DISCIPLINE->value)
        ->andReturnSelf();
    $hasManyMock->shouldReceive('exists')
        ->andReturn(true);

    $event->shouldReceive('pricing')
        ->andReturn($hasManyMock);

    expect($event->hasPerDisciplinePricing())->toBeTrue();
});

it('detects when event does not have per-discipline pricing', function () {
    $event = Mockery::mock(Event::class)->makePartial();

    $hasManyMock = Mockery::mock(HasMany::class);
    $hasManyMock->shouldReceive('where')
        ->with('is_active', true)
        ->andReturnSelf();
    $hasManyMock->shouldReceive('where')
        ->with('price_type', EvtEventFeeTypeEnum::PER_DISCIPLINE->value)
        ->andReturnSelf();
    $hasManyMock->shouldReceive('exists')
        ->andReturn(false);

    $event->shouldReceive('pricing')
        ->andReturn($hasManyMock);

    expect($event->hasPerDisciplinePricing())->toBeFalse();
});

it('returns per-discipline pricing collection', function () {
    $event = Mockery::mock(Event::class)->makePartial();

    $pricing1 = new Pricing([
        'id' => 1,
        'price_type' => EvtEventFeeTypeEnum::PER_DISCIPLINE->value,
        'price' => 25.00,
        'discipline_id' => 1,
    ]);
    $pricing2 = new Pricing([
        'id' => 2,
        'price_type' => EvtEventFeeTypeEnum::PER_DISCIPLINE->value,
        'price' => 30.00,
        'discipline_id' => 2,
    ]);

    $pricingCollection = new Collection([$pricing1, $pricing2]);

    $hasManyMock = Mockery::mock(HasMany::class);
    $hasManyMock->shouldReceive('where')
        ->with('is_active', true)
        ->andReturnSelf();
    $hasManyMock->shouldReceive('where')
        ->with('price_type', EvtEventFeeTypeEnum::PER_DISCIPLINE->value)
        ->andReturnSelf();
    $hasManyMock->shouldReceive('get')
        ->andReturn($pricingCollection);

    $event->shouldReceive('pricing')
        ->andReturn($hasManyMock);

    $result = $event->getPerDisciplinePricing();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toHaveCount(2)
        ->and($result->first()->price)->toBe(25.00);
});

it('returns empty collection when no per-discipline pricing exists', function () {
    $event = Mockery::mock(Event::class)->makePartial();

    $hasManyMock = Mockery::mock(HasMany::class);
    $hasManyMock->shouldReceive('where')
        ->with('is_active', true)
        ->andReturnSelf();
    $hasManyMock->shouldReceive('where')
        ->with('price_type', EvtEventFeeTypeEnum::PER_DISCIPLINE->value)
        ->andReturnSelf();
    $hasManyMock->shouldReceive('get')
        ->andReturn(new Collection);

    $event->shouldReceive('pricing')
        ->andReturn($hasManyMock);

    $result = $event->getPerDisciplinePricing();

    expect($result)->toBeInstanceOf(Collection::class)
        ->and($result)->toBeEmpty();
});
