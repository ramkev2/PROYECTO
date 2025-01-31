<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'usuario')]
class Usuario
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'usuId')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: 'string', name: 'usuario')]
    private string $usuario;

    #[ORM\Column(type: 'string', name: 'apellido')]
    private string $apellido;

    #[ORM\Column(type: 'string', name: 'nombre')]
    private string $nombre;

    #[ORM\Column(type: 'string', name: 'email')]
    private string $email;

    #[ORM\Column(type: 'string', name: 'clave')]
    private string $clave;

    #[ORM\Column(type: 'integer', name: 'edad')]
    private int $edad;
    public function getId(): int
    {
        return $this->id;
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario(string $usuario)
    {
        $this->usuario = $usuario;
    }

    public function getApellido()
    {
        return $this->apellido;
    }

    public function setApellido(string $apellido)
    {
        $this->apellido = $apellido;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email)
    {
        $this->email = $email;
    }

    public function getClave()
    {
        return $this->clave;
    }

    public function setClave(string $clave)
    {
        $this->clave = $clave;
    }

    public function getEdad()
    {
        return $this->edad;
    }

    public function setEdad(int $edad)
    {
        $this->edad = $edad;
    }
}
