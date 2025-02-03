<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
#[ORM\Entity]
#[ORM\Table(name: 'usuario')]
class Usuario implements UserInterface , PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer', name: 'id')]
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

    #[ORM\Column(type: 'string', name: 'password')]
    private string $password;

    #[ORM\Column(type: 'integer', name: 'edad')]
    private int $edad;
    #[ORM\Column(type:'integer', name:'rol')]
    private $rol;

    public function getRol(){
        return $this->rol;
    }

    public function setRol($rol){
        $this->rol = $rol;
    }
    public function getRoles(): array
    {
		return ['ROLE_USER'];            
	}

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

    public function getPassword(): ?string
   
    {
        return $this->password;
    }

    public function setpassword(string $password)
    {
        $this->password = $password;
    }

    public function getEdad()
    {
        return $this->edad;
    }

    public function setEdad(int $edad)
    {
        $this->edad = $edad;
    }
    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getSalt(): ?string
    {
        return null;
    }
	
    public function eraseCredentials(): void
    {

    }
}
