<?php

// ===== MUCHO CUIDADO, tienes que incluir el namespace =====
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] 
#[ORM\Table(name: 'amistad')]
class Amistad{
    #[ORM\Id]
    #[ORM\Column(type:'integer', name:'id')]
    #[ORM\GeneratedValue]
    private $id;
   
    #[ORM\Column(type:'integer', name:'usuario1_id')]
    private $usuario1_id;

	#[ORM\Column(type:'integer', name:'usuario2_id')]
	private $usuario2_id;

    #[ORM\Column(type:'datetime', name:'fecha_amistad')]
	private $fecha_amistad;
    
	
	public function getId()
    {
        return $this->id;
    }

    public function getUsuario1_id()
    {
        return $this->usuario1_id;
    }
    public function setUsuario1_id($usuario1_id)
    {
        $this->usuario1_id = $usuario1_id;
    }

	public function getUsuario2_id()
    {
        return $this->fundacion;
    }
    public function setUsuario2_id($usuario2_id)
    {
        $this->usuario2_id = $usuario2_id;
    }

	public function getFecha_amistad()
    {
        return $this->socios;
    }
	public function setFecha_amistad($fecha_amistad)
    {
        $this->fecha_amistad = $fecha_amistad;
    }
}



