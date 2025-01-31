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
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\Usuario;

class Controladores extends AbstractController
{
	#[Route('/login', name:'login')]
    public function login(){    
        return $this->render('login.html.twig');
    } 
    #[Route('/ctr_login', name:'ctr_login')]
    public function ctr_login(  Request $request, 
    EntityManagerInterface $entityManager, 
    UserPasswordHasherInterface $passwordHasher,)
    {
        $correo = $request->request->get('username');
        $clave = $request->request->get('password');
    
        // Buscar usuario por email
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $correo]);
    
        if (!$usuario) {
            return $this->render('login.html.twig', ['error' => 'Usuario no encontrado']);
        }
    
        // Verificar contraseña
        if (!$passwordHasher->isPasswordValid($usuario, $clave)) {
            return $this->render('login.html.twig', ['error' => 'Contraseña incorrecta']);
        }
    
       return $this->render('inicio.html.twig');
    
    
    }       
	
	#[Route('/logout', name:'logout')]
    public function logout(){    
        return new Response();
    }   
    

   
    #[Route('/recuperarContraseña', name:'recuperarContraseña')]
    public function recuperarContraseña(){    
        return new Response();
    }
    
}

