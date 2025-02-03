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
    

   
    #[Route('/registrarse', name:'registrarse')]
    public function registrarse(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response{    
        
        if ($request->isMethod('POST')) {
            $nombre = $request->request->get('nombre');
            $apellido = $request->request->get('apellido');
            $email = $request->request->get('email');
            $usuario = $request->request->get('usuario');
            $clave = $request->request->get('clave');
            $edad = (int)$request->request->get('edad');

            if (!$nombre || !$apellido || !$email || !$usuario || !$clave || !$edad) {
              $error= 'Todos los campos son obligatorios.';
              return $this->render('registrarse.html.twig', [
                'error' => $error  ]);
            }

            $usuarioExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);
            if ($usuarioExistente) {
                $error= 'El email ya está registrado.';
                return  $this->render('registrarse.html.twig', [
                    'error' => $error  ]);
            }
            $usuarioNombreExistente = $entityManager->getRepository(Usuario::class)->findOneBy(['usuario' => $usuario]);
            if ($usuarioNombreExistente) {
                $error = 'El nombre de usuario ya está registrado.';
                $this->render('registrarse.html.twig', [
                    'error' => $error  ]);
            }
            if (!is_numeric($edad) || $edad <= 14) {
                $error = 'Debes ser mayor de 14 años para registrarte.';
                return  $this->render('registrarse.html.twig', [
                    'error' => $error  ]);
            }

            $nuevoUsuario = new Usuario();
            $nuevoUsuario->setNombre($nombre);
            $nuevoUsuario->setApellido($apellido);
            $nuevoUsuario->setEmail($email);
            $nuevoUsuario->setUsuario($usuario);
            $nuevoUsuario->setEdad($edad);
            $nuevoUsuario->setRol(0);

            $hashedPassword = $passwordHasher->hashPassword($nuevoUsuario, $clave);
            $nuevoUsuario->setClave($hashedPassword);

            $entityManager->persist($nuevoUsuario);
            $entityManager->flush();
            return $this->redirectToRoute('login');
        }
        return $this->render('registrarse.html.twig');
    }

    #[Route('/recuperarContraseña', name:'recuperarContraseña')]
    public function recuperarContraseña(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response{    
        return new Response();
    }
    
}

