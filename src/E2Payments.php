<?php

class E2Payments
{
    private $SERVER_URL = 'https://e2payments.explicador.co.mz';
    private $client_id;
    private $client_secret;
    private $temp_token;

    public function __construct($credentials)
    {
        if (is_array($credentials)) {
            $this->client_id = $credentials['client_id'];
            $this->client_secret = $credentials['client_secret'];
        }
    }

    public function getToken () {

        $client = new GuzzleHttp\Client(['base_uri' => $this->SERVER_URL]);

        $requestTokenBody = [
            "grant_type"    => "client_credentials",
            "client_id"     => $this->client_id,
            "client_secret" => $this->client_secret,
        ];

        $response = $client->post('POST', '/oauth/token', [
            "headers" => [],
            'json' => $requestTokenBody,
        ]);

        //TODO: Por tratar os erros.

        $data = json_decode($response->getBody());

        $this->temp_token = $data->token_type . ' ' . $data->access_token;

        return $this->temp_token;

    }

    public function handleMpesaC2bPayment ($amount, $phone, $reference, $wallet_id, $skip_domain_from_validation = false) {

        $requestBody = [
            "client_id" => $this->client_id,
            "amount"    => $amount,
            "phone"     => $phone,
            "reference" => $reference, //sem espaços
            "skip_domain_from_validation" => $skip_domain_from_validation //Por colocar nas configurações do token
        ];

        return $this->makeRequest($wallet_id, $requestBody);

    }

    private function makeRequest($wallet_id, $requestBody) {

        $client = new GuzzleHttp\Client(['base_uri' => 'https://e2payments.explicador.co.mz']);

        $response = $client->post('/v1/c2b/mpesa-payment/' . $wallet_id, [
            "headers" => $this->getHeaders(),
            'json' => $requestBody,
            'http_errors' => false
        ]);

        //TODO: Por validar erros

        return json_decode($response->getBody());
    }

    private function getHeaders()
    {
        $token = $this->temp_token;
        if (!$token) {
            $token = $this->getToken();
        }
        $headers = [
            "Authorization" => $token,
            "Accept"=> "application/json",
            "Content-Type" => "application/json",
        ];
        return $headers;
    }

}