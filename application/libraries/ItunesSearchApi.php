<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ItunesSearchApi
{
    protected $apiUrl = 'https://itunes.apple.com';
    protected $timeout = 15;

    public function __construct()
    {
        // kosong
    }

    protected function request($path, array $params = [])
    {
        // Hapus parameter kosong/null agar URL bersih
        $params = array_filter($params, function ($value) {
            return $value !== null && $value !== '';
        });

        $url = rtrim($this->apiUrl, '/') . '/' . ltrim($path, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'User-Agent: Mozilla/5.0'
            ]
        ]);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new Exception('iTunes API cURL error: ' . $error);
        }

        curl_close($ch);

        $json = json_decode($body, true);

        if ($httpCode < 200 || $httpCode >= 300) {
            $message = isset($json['errorMessage']) ? $json['errorMessage'] : $body;
            throw new Exception('iTunes API error ' . $httpCode . ': ' . $message);
        }

        if (!is_array($json)) {
            throw new Exception('iTunes API response bukan JSON valid');
        }

        return $json;
    }

    public function search($term, array $options = [])
    {
        $explicit = isset($options['explicit']) ? $options['explicit'] : null;

        if (is_bool($explicit)) {
            $explicit = $explicit ? 'Yes' : 'No';
        }

        return $this->request('/search', [
            'term'      => $term,
            'country'   => isset($options['country']) ? $options['country'] : null,
            'media'     => isset($options['media']) ? $options['media'] : null,
            'entity'    => isset($options['entity']) ? $options['entity'] : null,
            'attribute' => isset($options['attribute']) ? $options['attribute'] : null,
            'limit'     => isset($options['limit']) ? $options['limit'] : null,
            'lang'      => isset($options['lang']) ? $options['lang'] : null,
            'version'   => isset($options['version']) ? $options['version'] : null,
            'explicit'  => $explicit,
        ]);
    }

    public function lookup(array $options = [])
    {
        return $this->request('/lookup', [
            'id'          => $this->toCommaValue(isset($options['id']) ? $options['id'] : null),
            'amgArtistId' => $this->toCommaValue(isset($options['amg_artist_id']) ? $options['amg_artist_id'] : null),
            'amgAlbumId'  => $this->toCommaValue(isset($options['amg_album_id']) ? $options['amg_album_id'] : null),
            'amgVideoId'  => $this->toCommaValue(isset($options['amg_video_id']) ? $options['amg_video_id'] : null),
            'bundleId'    => $this->toCommaValue(isset($options['bundle_id']) ? $options['bundle_id'] : null),
            'upc'         => $this->toCommaValue(isset($options['upc']) ? $options['upc'] : null),
            'isbn'        => $this->toCommaValue(isset($options['isbn']) ? $options['isbn'] : null),
            'entity'      => isset($options['entity']) ? $options['entity'] : null,
            'limit'       => isset($options['limit']) ? $options['limit'] : null,
            'sort'        => isset($options['sort']) ? $options['sort'] : null,
        ]);
    }

    protected function toCommaValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return implode(',', $value);
        }

        return $value;
    }
}