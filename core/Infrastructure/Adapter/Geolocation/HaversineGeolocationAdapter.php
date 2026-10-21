<?php
namespace App\Infrastructure\Adapter\Geolocation;

class HaversineGeolocationAdapter implements GeolocationAdapterInterface {
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Coordenadas GPS de referencia para los barrios de Neiva, Huila
     */
    private array $neighborhoodMap = [
        'cándido leguízamo' => [2.9460, -75.2970],
        'cándido'           => [2.9460, -75.2970],
        'las granjas'       => [2.9420, -75.2890],
        'centro'            => [2.9273, -75.2819],
        'quirinal'          => [2.9360, -75.2850],
        'prado alto'        => [2.9310, -75.2750],
        'ipanema'           => [2.9340, -75.2680],
        'santa inés'        => [2.9490, -75.2910],
        'canaima'           => [2.9120, -75.2790],
        'los andes'         => [2.9200, -75.2730],
        'las palmas'        => [2.9380, -75.2620],
        'el altico'         => [2.9250, -75.2880],
        'chicalá'           => [2.9400, -75.2780],
        'gualanday'         => [2.9320, -75.2640],
    ];

    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float {
        // Conversión a radianes
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = self::EARTH_RADIUS_KM * $c;
        return round($distance, 1);
    }

    public function getNeighborhoodCoordinates(string $neighborhood): ?array {
        $key = mb_strtolower(trim($neighborhood), 'UTF-8');
        return $this->neighborhoodMap[$key] ?? null;
    }

    public function calculateDistanceToNeighborhood(string $neighborhood, float $originLat, float $originLon): float {
        $coords = $this->getNeighborhoodCoordinates($neighborhood);
        if (!$coords) {
            // Distancia estimada promedio en Neiva si el barrio no está mapeado
            return 3.5;
        }

        return $this->calculateDistance($originLat, $originLon, $coords[0], $coords[1]);
    }
}
