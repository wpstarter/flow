<?php

use WpStarter\Flow\State\ArrayStore;

test('array store can store and retrieve values', function () {
    $store = new ArrayStore('test-namespace');
    
    $store->put('key1', 'value1');
    expect($store->get('key1'))->toBe('value1');
});

test('array store returns default value when key not found', function () {
    $store = new ArrayStore('test-namespace');
    
    expect($store->get('non-existent', 'default'))->toBe('default');
});

test('array store can store values forever', function () {
    $store = new ArrayStore('test-namespace');
    
    $store->forever('key2', 'value2');
    expect($store->get('key2'))->toBe('value2');
});

test('array store can forget values', function () {
    $store = new ArrayStore('test-namespace');
    
    $store->put('key3', 'value3');
    expect($store->get('key3'))->toBe('value3');
    
    $store->forget('key3');
    expect($store->get('key3'))->toBeNull();
});

test('array store uses namespaced keys', function () {
    $store1 = new ArrayStore('namespace1');
    $store2 = new ArrayStore('namespace2');
    
    $store1->put('key', 'value1');
    $store2->put('key', 'value2');
    
    expect($store1->get('key'))->toBe('value1');
    expect($store2->get('key'))->toBe('value2');
}); 