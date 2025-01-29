<?php

// ===== MUCHO CUIDADO, tienes que incluir el namespace =====
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity] 
#[ORM\Table(name: 'publicacion')]
class Publicacion{

    #[ORM\Id]
    #[ORM\Column(type:'integer', name:'id')]
    #[ORM\GeneratedValue]
    private $id;
   
    #[ORM\Column(type:'integer', name:'fecha_publicacion')] //cambiar a date 
    private $fecha_publicacion;

	#[ORM\Column(type:'integer', name:'likes')]
	private $likes;

	
	public function getId()
    {
        return $this->id;
    }

    public function getFecha_publicacion()
    {
        return $this->fecha_publicacion;
    }

    public function setFecha_publicacion($fecha_publicacion)
    {
        $this->fecha_publicacion = $fecha_publicacion;
    }
	public function getLikes()
    {
        return $this->likes;
    }

    public function setLikes($likes)
    {
        $this->likes = $likes;
    }
	
}



