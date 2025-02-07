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
        return new Response();
    }   
    

    #[Route('/verificarCorreo', name: 'verificarCorreo', methods: ['POST'])]
    public function verificarCorreo(Request $request, EntityManagerInterface $entityManager)
    {
        $data = json_decode($request->getContent(), true);

        $email = trim($data["email"]);

        // Buscar el usuario en la base de datos
        $usuario = $entityManager->getRepository(Usuario::class)->findOneBy(["email" => $email]);

        if ($usuario) {
            return new JsonResponse(["success" => "Correo verificado."],200);
        } else {
            return new JsonResponse(["error" => "El correo no está registrado."], 400);
        }
    }
    #[Route('/enviarCodigo', name: 'enviarCodigo')]
public function enviarCodigo(Request $request, MailerInterface $mailer)
{
    $jsonContent = $request->getContent();
    error_log("JSON recibido: " . $jsonContent);

    // Intentar decodificar el JSON
    $data = json_decode($jsonContent, true);
        $email = trim($data['email']);
        $codigo = $data['codigo'];
        error_log("Datos recibidos: " . print_r($data, true));

        // Crear el correo a enviar
        $emailMessage = (new Email())
            ->from('noreply@Slyce.com')  
            ->to($email)                     
            ->subject('Código de verificación')
            ->text('Tu código de verificación es: ' . $codigo);

        try {
            // Intentar enviar el correo
            $mailer->send($emailMessage);
            return new JsonResponse(['success' => 'Código enviado con éxito.']);
        } catch (\Exception $e) {
            // Si ocurre un error en el envío del correo
            return new JsonResponse(['error' => 'Error al enviar el código: ' . $e->getMessage()], 500);
        }
    }
    #[Route('/cambioContraseña', name: 'cambioContraseña')]
    public function cambioContraseña()
    {
        return $this->render('recuperarContraseña.html.twig');
    }
    #[Route('/cambiarContraseña', name: 'cambiarContraseña', methods: ['POST'])]
        public function cambiarContraseña(Request $request, UserPasswordHasherInterface $passwordHasher)
        {
            $jsonContent = $request->getContent();
            error_log("JSON recibido: " . $jsonContent);
        
            // Intentar decodificar el JSON
            $data = json_decode($jsonContent, true);
            $email = trim($data['email']);
            $password = $data['newPassword'];
            error_log("Datos recibidos: " . print_r($data, true));
        
            // Buscar el usuario en la base de datos
            $usuario = $this->entityManager->getRepository(Usuario::class)->findOneBy(["email" => $email]);
        
            if ($usuario) {
                // Hash de la contraseña
                $hashedPassword = $passwordHasher->hashPassword($usuario, $password);
                $usuario->setPassword($hashedPassword);
        
                // Guardar la nueva contraseña en la base de datos
                $this->entityManager->persist($usuario);
                $this->entityManager->flush();
        
                return new JsonResponse(['success' => 'Contraseña cambiada con éxito.']);
            } else {
                return new JsonResponse(['error' => 'El correo no está registrado.'], 400);
            }
        }
    

    

    // //controlador para mostrar las publicaciones en la pagina de inicio
    // #[Route('inicio', name: 'inicio')]
    // public function inicio(EntityMangerInteface $entityManager){
       
    //     // Comprobamos si el usuario al menos se ha logueado
	// 	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
       
    //     $publicaciones = $entityManager->getRepository(Publicacion::class)->findAll();
    //     return $this->render('home.html.twig', [publicaciones => $publicaciones]);
    // }
    
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

}

    


