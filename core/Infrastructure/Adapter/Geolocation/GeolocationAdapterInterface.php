<?php
namespace App\Infrastructure\Adapter\Geolocation;

interface GeolocationAdapterInterface {
    /**
     * Calcula la distancia geodésica en kilómetros entre dos coordenadas GPS (Lat, Lon)
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float;

    /**
     * Obtiene las coordenadas GPS aproximadas de un barrio de Neiva
     * @return array [lat, lon]
     */
    public function getNeighborhoodCoordinates(string $neighborhood): ?array;

    /**
     * Calcula la distancia desde un punto de referencia en Neiva hacia un barrio
     */
    public function calculateDistanceToNeighborhood(string $neighborhood, float $originLat, float $originLon): float;
}
