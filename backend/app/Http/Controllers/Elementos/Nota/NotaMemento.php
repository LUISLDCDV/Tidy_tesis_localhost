<?php

class NotaMemento
{
    private $estado;

    public function __construct($estado)
    {
        $this->estado = $estado;
    }

    public function getEstado()
    {
        return $this->estado;
    }
}

class Nota
{
    private $contenido;

    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    }

    public function guardar()
    {
        return new NotaMemento($this->contenido);
    }

    public function restaurar(NotaMemento $memento)
    {
        $this->contenido = $memento->getEstado();
    }
}
