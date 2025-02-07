<?php
namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Usuario;
use App\Entity\Comentario;
use App\Entity\Amistad;
use App\Entity\Publicacion;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\UsuarioRepository;
class Controladores extends AbstractController
{
    private $usuarioRepository;
    private $entityManager;
    public function __construct(UsuarioRepository $usuarioRepository, EntityManagerInterface $entityManager)
    {
        $this->usuarioRepository = $usuarioRepository;
        $this->entityManager = $entityManager;
    }
 

	#[Route('/login', name:'login')]
    public function index(AuthenticationUtils $authenticationUtils){  
       
        if ($this->getUser()) {
            dump($this->getUser());
            return $this->redirectToRoute('inicio');
        }
       // Comprueba si hubo algún error
         $error = $authenticationUtils->getLastAuthenticationError();

        // Recupera el último nombre de usuario que se probó
         $lastUsername = $authenticationUtils->getLastUsername();

        // Renderizar el formulario de login
        return $this->render('login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);

    } 
   
       
   
   

    // Verificar si el usuario o email ya existen
    #[Route('/check-usuario', name: 'check-usuario', methods: ['POST'])]
    public function checkUsuario(Request $request,EntityManagerInterface $entityManager): JsonResponse
    {
        $usuario = $request->request->get('usuario');
        $email = $request->request->get('email');

        // Consultar si existe un usuario con el mismo nombre de usuario
        $existingUserByUsername = $this->entityManager->getRepository(Usuario::class)->findOneBy(['usuario' => $usuario]);

        // Consultar si existe un usuario con el mismo email
        $existingUserByEmail = $this->entityManager->getRepository(Usuario::class)->findOneBy(['email' => $email]);

        if ($existingUserByUsername) {
            return new JsonResponse(['error' => 'Este nombre de usuario ya está en uso.'], 400);
        }

        if ($existingUserByEmail) {
            return new JsonResponse(['error' => 'Este email ya está registrado.'], 400);
        }

        return new JsonResponse(['success' => 'El nombre de usuario y el email están disponibles.'], 200);
    }

    // Registro de usuario
    #[Route('/registrarse', name: 'registrarse')]
    public function registrarse(Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        if($request->isMethod('GET')){
            return $this->render('registrarse.html.twig');
        }
        $nombre = $request->request->get('nombre');
        $apellido = $request->request->get('apellido');
        $usuario = $request->request->get('usuario');
        $email = $request->request->get('email');
        $clave = $request->request->get('clave');
        $edad = $request->request->get('edad');

       
        // Crear una nueva entidad de usuario
        $nuevoUsuario = new Usuario();
        $nuevoUsuario->setNombre($nombre);
        $nuevoUsuario->setApellido($apellido);
        $nuevoUsuario->setUsuario($usuario);
        $nuevoUsuario->setEmail($email);
        $nuevoUsuario->setEdad($edad);
        $nuevoUsuario->setRol(0);

        // Hash de la contraseña
        $hashedPassword = $passwordHasher->hashPassword($nuevoUsuario, $clave);
        $nuevoUsuario->setPassword($hashedPassword);

        // Guardar usuario en la base de datos
        $this->entityManager->persist($nuevoUsuario);
        $this->entityManager->flush();

        return new JsonResponse(['success' => 'Usuario registrado correctamente.'], 200);
    }
    

	
	#[Route('/logout', name:'logout')]
    public function logout(){    
        return $this->redirectToRoute('login');
    }   
  

 

    //configurar el correo para que envie el mensaje
    #[Route('/correoRecuperacion', name:'correoRecuperacion')]
    public function correoRecuperacion(Request $request, EntityManagerInterface $entityManager, MailerInterface $mailer)
    {
        $correo = $request->request->get('email');

        if (empty($correo)) {
            return new Response("Por favor, introduce tu correo.");
        }

        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $correo]);

        if (!$usuario) {
            return new Response("Correo no encontrado.");
        }

       
        $codigo = random_int(100000, 999999);

        // Guardar el código en la sesión
        $session = $request->getSession();
        $session->set('codigo_recuperacion', $codigo);
        $session->set('email_recuperacion', $correo);

        $email = (new Email())
            ->from('noreply@Slyce.com')
            ->to($correo)
            ->subject('Código de Recuperación de Contraseña')
            ->text("Tu código de recuperación es: $codigo");

        $mailer->send($email);

        return $this->redirectToRoute('recuperarContraseña', ['mostrar' => true]);
    }

    
    //controlador para cambiar la contraseña
    #[Route('/cambioContraseña', name:'cambioContraseña')]
    public function cambioContraseña(EntityManagerInterface $entityManager, Request $request, UserPasswordHasherInterface $passwordHasher)
    {
        $correo = $request->request->get('email');
        $newPass = $request->request->get('nuevaContra');
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(['email' => $correo]);

        if (!$usuario) {
            return new Response("Error: usuario no encontrado.");
        }

        // Hashear y actualizar la contraseña
        $hashedPassword = $passwordHasher->hashPassword($usuario, $newPass);
        $usuario->setPassword($hashedPassword);

        $entityManager->persist($usuario);
        $entityManager->flush();

        // Limpiar la sesión después de cambiar la contraseña
        $session = $request->getSession();
        $session->remove('codigo_recuperacion');
        $session->remove('email_recuperacion');

        return new Response("Contraseña actualizada con éxito.");
    }


    //controlador para verificar el codigo
    #[Route('/verificarCodigo', name:'verificarCodigo')]
    public function verificarCodigo(Request $request)
    {
        $codigoIngresado = $request->request->get('codigo');
        $session = $request->getSession();
        $codigoGuardado = $session->get('codigo_recuperacion');
        $correoGuardado = $session->get('email_recuperacion');

        if (!$codigoGuardado || $codigoIngresado != $codigoGuardado) {
            return new Response("Código incorrecto. Inténtalo de nuevo.");
        }

        // Redirigir a la vista de cambio de contraseña
        return $this->redirectToRoute('cambiarContraseña', ['email' => $correoGuardado]);
    }


    //controlador para mostrar las publicaciones en la pagina de inicio
    #[Route('/inicio', name: 'inicio')]
    public function inicio(EntityManagerInterface $entityManager){
       
        // Comprobamos si el usuario al menos se ha logueado
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
       
        $publicaciones = $entityManager->getRepository(Publicacion::class)->findAll();
        return $this->render('inicio.html.twig', [publicaciones => $publicaciones]);
    }
    
    #[Route('/busqueda', name: 'busqueda', methods: ['POST'])]
    public function busqueda(Request $request, EntityManagerInterface $entityManager){
        $busqueda = $request->request->get('busqueda');


        if (!$busqueda) {
            return $this->render('busqueda.html.twig', ['error'=> 'Introduce una busqueda']);

        $usuario = $entityManager->getRepository(Usuario::class)
        ->createQueryBuilder('b')
        ->where('b.usuario LIKE :busqueda')
        ->orWhere('b.nombre LIKE :busqueda')
        ->setParameter('busqueda', '%$busqueda%')
        ->getQuery()
        ->getResult();

        if (!$usarios) {
            return $this->render('busqueda.html.twig', ['error'=> 'No se encontraron resultados']);
        }

        return $this->render('busqueda.html.twig', ['usuarios' => $usuarios]);

        }
        
    }

    //controlador para ver Mi perfil
    #[Route('/miperfil', name: 'miperfil')]
    public function miperfil(EntityManagerInterface $entityManager){
       
        // Comprobamos si el usuario al menos se ha logueado
		$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
       
        return $this->render('miPerfil.html.twig');
    }

    //controlador para subir fotos
    #[Route('/cambiarFotoPerfil', name: 'cambiarFotoPerfil')]
    public function cambiarFotoPerfil(Requeste $request, EntityManagerInterface $entityManager){
       
        $usuario = $this->getUser(); 

        $fotoPerfil = $request->files->get('foto_perfil');

       if ($fotoPerfil) {
        // Validar el archivo (puedes validar el tipo de archivo o el tamaño)
        $filename = uniqid() . '.' . $fotoPerfil->guessExtension();

        try {
            // Mover el archivo al directorio donde guardarás las fotos
                $fotoPerfil->move(
                $this->getParameter('fotos_perfil_directory'), // Esto debes definirlo en config/services.yaml
                $filename
            );
        } catch (FileException $e) {

            return $this->render('error.html.twig', ['error' => 'Error al subir la foto']);
        }

        // guardo la ruta de la imagen en el perfil del usuario
        $usuario->setFotoPerfil($filename);

        $entityManager->persist($usuario);
        $entityManager->flush();
    }
        return $this->redirectToRoute('miPerfil');
    }
}


    


