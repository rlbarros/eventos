<?php

namespace App\DTOs;

use App\Models\City;
use App\Models\State;

class OpenCEPResponse
{
    protected $cep;
    protected $logradouro;
    protected $complemento;
    protected $bairro;
    protected $localidade;
    protected $uf;
    protected $ibge;

    public function __construct(array $data = [])
    {
        $this->cep = $data['cep'] ?? null;
        $this->logradouro = $data['logradouro'] ?? null;
        $this->complemento = $data['complemento'] ?? null;
        $this->bairro = $data['bairro'] ?? null;
        $this->localidade = $data['localidade'] ?? null;
        $this->uf = $data['uf'] ?? null;
        $this->ibge = $data['ibge'] ?? null;
    }

    public function getCep()
    {
        return $this->cep;
    }
    public function getLogradouro()
    {
        return $this->logradouro;
    }
    public function getComplemento()
    {
        return $this->complemento;
    }
    public function getBairro()
    {
        return $this->bairro;
    }
    public function getLocalidade()
    {
        return $this->localidade;
    }
    public function getUf()
    {
        return $this->uf;
    }
    public function getIbge()
    {
        return $this->ibge;
    }

    public function getState(): State
    {
        $ibgeState = substr($this->ibge, 0, 2) + 0;
        $state = State::where('id', '=', $ibgeState)->first();
        return $state;
    }

    public function getCity(): City
    {
        $ibgeState = substr($this->ibge, 0, 2) + 0;
        $ibgeCity = substr($this->ibge, 2) + 0;
        $whereArray = [
            ['state_id', '=', $ibgeState],
            ['ibge_id', '=', $ibgeCity]
        ];
        $city =  City::where($whereArray)->first();
        return $city;
    }
}
