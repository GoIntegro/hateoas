<?php

namespace GoIntegro\Hateoas\JsonApi;

interface Factory
{
    /**
     * Crea y configura la instancia.
     * @return mixed
     */
    public function create();
}
