<?php

/**
 * Criado por: Rafael Dourado
 * Data: 28/10/2020
 * Hora: 09 : 09
 */

declare(strict_types=1);

namespace Mercatus\PaymentApi\Infrastructure\Core\Transport;

class HttpTransport implements TransportInterface
{

    public function get(string $url, array $headers, $options)
    {
        // inicia o curl
        $ch = curl_init();

        $opts = $options;

        if (is_array($options)) {
            $opts = http_build_query($options);
        }

        $url .= $opts ? '?' . $opts: '';
        curl_setopt_array(
            $ch,
            [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_ENCODING => '',
            ]
        );

        $response =  curl_exec($ch);

        curl_close($ch);

        return $response;
    }

    public function post(string $url, array $headers, $options)
    {
        // inicia o curl
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $url,
            CURLOPT_POST => 1,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_ENCODING => '',
            CURLOPT_POSTFIELDS => $options,
        ]);

        $response =  curl_exec($ch);

        curl_close($ch);

        return $response;
    }
}
