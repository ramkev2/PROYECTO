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
   
    #[ORM\Column(type:'datetime', name:'fecha_publicacion')] 
    private $fecha_publicacion;

	#[ORM\Column(type:'integer', name:'likes')]
	private $likes;

    #[ORM\Column(type:'string', name:'contenido')]
	private $contenido;

    #[ORM\Column(type: 'string', nullable: true)]
    private string $imagen;

    #[ORM\Column(type:'integer', name:'usuario_id')]
	private $usuario_id;
    #[ORM\ManyToOne(targetEntity: Usuario::class, inversedBy: 'publicaciones')]
    #[ORM\JoinColumn(nullable: false)]
    private Usuario $usuario;

	
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

	public function getContenido()
    {
        return $this->contenido;
    }
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario)
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getImagen(): string
    {
        return $this->imagen;
    }

    public function setImagen(string $imagen): void
    {
        $this->imagen = $imagen;
    }
}



