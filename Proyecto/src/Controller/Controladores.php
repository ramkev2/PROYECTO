<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Categoria;
use App\Entity\Producto;
use App\Entity\Pedido;
use App\Entity\PedidoProducto;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class Controladores extends AbstractController
{
	#[Route('/login', name:'login')]
    public function login(){    
        return $this->render('login.html.twig');
    }    
	
	#[Route('/logout', name:'logout')]
    public function logout(){    
        return new Response();
    }   
    
    // #[Route('/logout', name:'logout')]
    // public function logout(){    
    //     return new Response();
    // }
}