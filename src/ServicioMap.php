<?php

namespace App;

use InvalidArgumentException;
use RuntimeException;

class ServicioMap
{
    /**
     * Obtiene las coordenadas (latitud y longitud) a partir de una dirección.
     *
     * @param string $dir Dirección para buscar las coordenadas
     *
     * @return array{lat: float, lon: float}
     */
    public function getCoordenadas(string $dir): array
    {
        $endpoint = 'https://maps.googleapis.com/maps/api/geocode/json';

        $direccionCompleta = implode(', ', array_filter([
            trim($dir),
            $_ENV['LOCALIDAD'] ?? '',
            $_ENV['CIUDAD'] ?? '',
            $_ENV['PAIS'] ?? '',
        ]));

        $params = [
            'address'  => $direccionCompleta,
            'language' => 'es',
            'region'   => 'es',
            'key'      => $_ENV['MAP_API_KEY'],
        ];

        $url = $endpoint . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $datos = $this->curlGetJson($url);

        if (($datos['status'] ?? '') !== 'OK' || empty($datos['results'][0]['geometry']['location'])) {
            throw new RuntimeException('No se han encontrado coordenadas para la dirección indicada.');
        }

        $location = $datos['results'][0]['geometry']['location'];

        return [
            'lat' => (float) $location['lat'],
            'lon' => (float) $location['lng'],
        ];
    }

    /**
     * Obtiene la ruta óptima para realizar los repartos.
     *
     * @param string $dato Parejas de coordenadas de los destinos del reparto separados por |
     *
     * @return string[] Orden de entrega a seguir en el reparto
     */
    public function ordenarRuta(string $dato): array
    {
        $endpoint = 'https://routes.googleapis.com/directions/v2:computeRoutes';

        $puntos = array_values(array_filter(
            array_map('trim', explode('|', $dato)),
            static fn(string $punto): bool => $punto !== ''
        ));

        if ($puntos === []) {
            return [];
        }

        if (count($puntos) === 1) {
            return ['1'];
        }

        $baseLat = (float) ($_ENV['LAT_BASE'] ?? 0);
        $baseLon = (float) ($_ENV['LON_BASE'] ?? 0);

        $intermediates = [];
        foreach ($puntos as $punto) {
            $intermediates[] = [
                'location' => [
                    'latLng' => $this->parseLatLng($punto),
                ],
            ];
        }

        $payload = [
            'origin' => [
                'location' => [
                    'latLng' => [
                        'latitude'  => $baseLat,
                        'longitude' => $baseLon,
                    ],
                ],
            ],
            'destination' => [
                'location' => [
                    'latLng' => [
                        'latitude'  => $baseLat,
                        'longitude' => $baseLon,
                    ],
                ],
            ],
            'intermediates' => $intermediates,
            'travelMode' => 'DRIVE',
            'languageCode' => 'es',
            'units' => 'METRIC',
            'optimizeWaypointOrder' => true,
        ];

        $headers = [
            'Content-Type: application/json',
            'X-Goog-Api-Key: ' . $_ENV['MAP_API_KEY'],
            'X-Goog-FieldMask: routes.optimizedIntermediateWaypointIndex',
        ];

        $datos = $this->curlPostJson($endpoint, $payload, $headers);

        if (empty($datos['routes'][0]['optimizedIntermediateWaypointIndex'])) {
            return [];
        }

        $orden = $datos['routes'][0]['optimizedIntermediateWaypointIndex'];

        // Google devuelve índices 0..n-1.
        // Para mantener compatibilidad con Bing, devolvemos 1..n como strings.
        return array_map(
            static fn(int $indice): string => (string) ($indice + 1),
            $orden
        );
    }

    /**
     * Ejecuta una petición GET y devuelve el JSON decodificado.
     */
    private function curlGetJson(string $url): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_VERBOSE => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $respuesta = curl_exec($ch);

        if ($respuesta === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Error cURL: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $datos = json_decode($respuesta, true);

        if (!is_array($datos)) {
            throw new RuntimeException('La respuesta no contiene un JSON válido.');
        }

        if ($httpCode >= 400) {
            throw new RuntimeException('Error HTTP ' . $httpCode . ': ' . $respuesta);
        }

        return $datos;
    }

    /**
     * Ejecuta una petición POST con JSON y devuelve el JSON decodificado.
     */
    private function curlPostJson(string $url, array $payload, array $headers = []): array
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_VERBOSE => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);

        $respuesta = curl_exec($ch);

        if ($respuesta === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Error cURL: ' . $error);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $datos = json_decode($respuesta, true);

        if (!is_array($datos)) {
            throw new RuntimeException('La respuesta no contiene un JSON válido.');
        }

        if ($httpCode >= 400) {
            throw new RuntimeException('Error HTTP ' . $httpCode . ': ' . $respuesta);
        }

        return $datos;
    }

    /**
     * Convierte un string "lat,lon" en el formato que espera Google Routes API.
     *
     * @return array{latitude: float, longitude: float}
     */
    private function parseLatLng(string $punto): array
    {
        $coords = array_map('trim', explode(',', $punto, 2));

        if (count($coords) !== 2 || !is_numeric($coords[0]) || !is_numeric($coords[1])) {
            throw new InvalidArgumentException('Coordenadas inválidas: ' . $punto);
        }

        return [
            'latitude'  => (float) $coords[0],
            'longitude' => (float) $coords[1],
        ];
    }
}
