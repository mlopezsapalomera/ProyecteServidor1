<?php
function pokemonApiRequest($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_USERAGENT => 'PokeNet Social/1.0',
        ]);
        $body = curl_exec($ch);
        curl_close($ch);

        if ($body === false || $body === null) {
            return null;
        }

        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : null;
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'timeout' => 20,
            'header' => "User-Agent: PokeNet Social/1.0\r\n",
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);

    $body = @file_get_contents($url, false, $context);
    if ($body === false) {
        return null;
    }

    $decoded = json_decode($body, true);
    return is_array($decoded) ? $decoded : null;
}

function pokemonApiCachePath($key) {
    return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pokenet_pokeapi_' . md5($key) . '.json';
}

function pokemonApiReadCache($key, $ttlSeconds = 86400) {
    $path = pokemonApiCachePath($key);
    if (!is_file($path)) {
        return null;
    }

    if (filemtime($path) !== false && (time() - filemtime($path)) > $ttlSeconds) {
        return null;
    }

    $cached = file_get_contents($path);
    if ($cached === false) {
        return null;
    }

    $decoded = json_decode($cached, true);
    return is_array($decoded) ? $decoded : null;
}

function pokemonApiWriteCache($key, array $data) {
    $path = pokemonApiCachePath($key);
    @file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE));
}

function pokemonApiFormatName($name) {
    $name = trim((string)$name);
    if ($name === '') {
        return '';
    }

    $name = str_replace(['-', '_'], ' ', $name);
    return ucwords(strtolower($name));
}

function pokemonApiSlugify($name) {
    $name = trim((string)$name);
    $name = strtolower($name);
    $name = preg_replace('/[^a-z0-9]+/', '-', $name);
    return trim((string)$name, '-');
}

function pokemonApiExtractIdFromUrl($url) {
    if (preg_match('#/pokemon/(\d+)/?$#', (string)$url, $matches)) {
        return (int)$matches[1];
    }

    return null;
}

function pokemonApiSpriteUrlFromId($id) {
    $id = (int)$id;
    if ($id <= 0) {
        return null;
    }

    return 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/' . $id . '.png';
}

function pokemonApiNormalizeDetails(array $pokemon) {
    $types = [];
    if (!empty($pokemon['types']) && is_array($pokemon['types'])) {
        foreach ($pokemon['types'] as $typeInfo) {
            if (!empty($typeInfo['type']['name'])) {
                $types[] = $typeInfo['type']['name'];
            }
        }
    }

    $stats = [
        'hp' => null,
        'attack' => null,
        'defense' => null,
        'special-attack' => null,
        'special-defense' => null,
        'speed' => null,
    ];

    if (!empty($pokemon['stats']) && is_array($pokemon['stats'])) {
        foreach ($pokemon['stats'] as $statInfo) {
            $statName = $statInfo['stat']['name'] ?? null;
            if ($statName && array_key_exists($statName, $stats)) {
                $stats[$statName] = isset($statInfo['base_stat']) ? (int)$statInfo['base_stat'] : null;
            }
        }
    }

    $apiId = isset($pokemon['id']) ? (int)$pokemon['id'] : null;
    $apiName = isset($pokemon['name']) ? (string)$pokemon['name'] : '';
    $spriteUrl = null;
    if (!empty($pokemon['sprites']['front_default'])) {
        $spriteUrl = $pokemon['sprites']['front_default'];
    } elseif (!empty($pokemon['sprites']['other']['official-artwork']['front_default'])) {
        $spriteUrl = $pokemon['sprites']['other']['official-artwork']['front_default'];
    }

    return [
        'api_id' => $apiId,
        'api_name' => $apiName,
        'display_name' => pokemonApiFormatName($apiName),
        'types' => $types,
        'primary_type' => $types[0] ?? null,
        'secondary_type' => $types[1] ?? null,
        'vida' => $stats['hp'],
        'ataque' => $stats['attack'],
        'defensa' => $stats['defense'],
        'ataque_especial' => $stats['special-attack'],
        'defensa_especial' => $stats['special-defense'],
        'velocidad' => $stats['speed'],
        'sprite_url' => $spriteUrl,
    ];
}

function pokemonApiFetchList($offset = 0, $limit = 2000) {
    $cacheKey = 'pokemon-list-' . (int)$offset . '-' . (int)$limit;
    $cached = pokemonApiReadCache($cacheKey, 86400);
    if ($cached !== null) {
        return $cached;
    }

    $url = 'https://pokeapi.co/api/v2/pokemon?offset=' . (int)$offset . '&limit=' . (int)$limit;
    $data = pokemonApiRequest($url);
    if (!is_array($data)) {
        return null;
    }

    pokemonApiWriteCache($cacheKey, $data);
    return $data;
}

function buscarPokemonApi($query, $limit = 12) {
    $query = trim((string)$query);
    if (strlen($query) < 2) {
        return [];
    }

    $queryLower = strtolower($query);
    $list = pokemonApiFetchList();
    if (empty($list['results']) || !is_array($list['results'])) {
        return [];
    }

    $results = [];
    foreach ($list['results'] as $pokemon) {
        $name = $pokemon['name'] ?? '';
        if ($name === '') {
            continue;
        }

        if (strpos(strtolower($name), $queryLower) === false) {
            continue;
        }

        $apiId = pokemonApiExtractIdFromUrl($pokemon['url'] ?? '');
        $results[] = [
            'api_id' => $apiId,
            'name' => $name,
            'display_name' => pokemonApiFormatName($name),
            'sprite_url' => pokemonApiSpriteUrlFromId($apiId),
            'url' => $pokemon['url'] ?? null,
        ];

        if (count($results) >= (int)$limit) {
            break;
        }
    }

    return $results;
}

function obtenerPokemonApiPorNombre($name) {
    $slug = pokemonApiSlugify($name);
    if ($slug === '') {
        return null;
    }

    $cacheKey = 'pokemon-detail-' . $slug;
    $cached = pokemonApiReadCache($cacheKey, 86400);
    if ($cached !== null) {
        return $cached;
    }

    $pokemon = pokemonApiRequest('https://pokeapi.co/api/v2/pokemon/' . rawurlencode($slug));
    if (!is_array($pokemon)) {
        return null;
    }

    $normalized = pokemonApiNormalizeDetails($pokemon);
    pokemonApiWriteCache($cacheKey, $normalized);
    return $normalized;
}
